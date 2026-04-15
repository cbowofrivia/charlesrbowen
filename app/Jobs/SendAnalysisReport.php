<?php

namespace App\Jobs;

use App\Ai\Agents\ReportSynthesisAgent;
use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAnalysisReport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $batchKey,
        public int $batchCount,
        public string $recipient,
        public CarbonImmutable $windowStart,
        public CarbonImmutable $windowEnd,
    ) {}

    public function handle(): void
    {
        $cacheKeys = [];
        $batchResults = [];

        for ($i = 0; $i < $this->batchCount; $i++) {
            $key = "{$this->batchKey}:{$i}";
            $cacheKeys[] = $key;
            $result = Cache::get($key);

            if ($result !== null) {
                $batchResults[] = $result;
            }
        }

        if (empty($batchResults)) {
            Log::warning('SendAnalysisReport: no cached batch results found, skipping report.', [
                'batch_key' => $this->batchKey,
                'batch_count' => $this->batchCount,
            ]);

            return;
        }

        if (count($batchResults) < $this->batchCount) {
            Log::warning('SendAnalysisReport: some batch results missing, proceeding with available data.', [
                'expected' => $this->batchCount,
                'found' => count($batchResults),
            ]);
        }

        if (count($batchResults) === 1) {
            $report = $batchResults[0];
        } else {
            $agent = new ReportSynthesisAgent($batchResults);
            $report = $agent->prompt('Synthesize the batch results into a single consolidated report.')->toArray();
        }

        Mail::to($this->recipient)
            ->send(new ConversationAnalysisReport($report, $this->windowStart, $this->windowEnd));

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
