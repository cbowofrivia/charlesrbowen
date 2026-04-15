<?php

namespace App\Jobs;

use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $cvContent = (string) file_get_contents(base_path('documents/cv.md'));
        $promptContent = (string) file_get_contents(base_path('documents/prompt.md'));

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
            ->then(function (Batch $batch) use ($batchKey, $batchCount, $recipient, $windowStart, $windowEnd) {
                SendAnalysisReport::dispatch(
                    batchKey: $batchKey,
                    batchCount: $batchCount,
                    recipient: $recipient,
                    windowStart: $windowStart,
                    windowEnd: $windowEnd,
                );
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
