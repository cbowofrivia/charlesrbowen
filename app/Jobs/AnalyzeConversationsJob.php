<?php

namespace App\Jobs;

use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AnalyzeConversationsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $windowDays,
        public string $recipient,
    ) {}

    public function handle(): void
    {
        $windowStart = CarbonImmutable::now()->subDays($this->windowDays)->startOfDay();
        $windowEnd = CarbonImmutable::now();

        $conversations = Conversation::with('messages')
            ->where('created_at', '>=', $windowStart)
            ->oldest()
            ->get();

        if ($conversations->isEmpty()) {
            $this->sendHeartbeat($windowStart, $windowEnd);

            return;
        }

        $cvPath = base_path('documents/cv.md');
        $promptPath = base_path('documents/prompt.md');

        if (! file_exists($cvPath) || ! file_exists($promptPath)) {
            Log::error('AnalyzeConversationsJob: required document files are missing.', [
                'cv_exists' => file_exists($cvPath),
                'prompt_exists' => file_exists($promptPath),
            ]);

            return;
        }

        $cvContent = (string) file_get_contents($cvPath);
        $promptContent = (string) file_get_contents($promptPath);

        $batchKey = 'analysis:'.Str::uuid();
        $chunks = $conversations->chunk(1);

        $jobs = $chunks->map(fn ($chunk, $index) => new AnalyzeConversationBatch(
            batchKey: $batchKey,
            batchIndex: $index,
            conversations: $chunk,
            cvContent: $cvContent,
            promptContent: $promptContent,
        ))->all();

        $recipient = $this->recipient;
        $batchCount = $chunks->count();

        Bus::batch($jobs)
            ->name('Conversation Analysis')
            ->allowFailures()
            ->then(function (Batch $batch) use ($batchKey, $batchCount, $recipient, $windowStart, $windowEnd) {
                SendAnalysisReport::dispatch(
                    batchKey: $batchKey,
                    batchCount: $batchCount,
                    recipient: $recipient,
                    windowStart: $windowStart,
                    windowEnd: $windowEnd,
                );
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::warning('Conversation analysis batch had failures.', [
                    'batch_id' => $batch->id,
                    'failed_jobs' => $batch->failedJobs,
                    'total_jobs' => $batch->totalJobs,
                    'error' => $e->getMessage(),
                ]);
            })
            ->dispatch();
    }

    private function sendHeartbeat(CarbonImmutable $windowStart, CarbonImmutable $windowEnd): void
    {
        $report = [
            'gap_analysis' => [],
            'prompt_effectiveness' => [],
            'cv_suggestions' => [],
            'summary' => [
                'conversation_count' => 0,
                'message_count' => 0,
                'common_topics' => [],
                'notable_interactions' => '',
                'is_heartbeat' => true,
            ],
        ];

        Mail::to($this->recipient)
            ->send(new ConversationAnalysisReport($report, $windowStart, $windowEnd));
    }
}
