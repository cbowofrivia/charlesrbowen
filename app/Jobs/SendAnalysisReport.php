<?php

namespace App\Jobs;

use App\Ai\Agents\ReportSynthesisAgent;
use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendAnalysisReport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

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
            $batchResults[] = Cache::get($key);
        }

        if ($this->batchCount === 1) {
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
