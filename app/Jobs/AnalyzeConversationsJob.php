<?php

namespace App\Jobs;

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class AnalyzeConversationsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

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

        $batchResults = [];

        foreach ($conversations->chunk(5) as $batch) {
            $agent = new ConversationAnalysisAgent(
                conversations: $batch,
                cvContent: $cvContent,
                promptContent: $promptContent,
            );

            $batchResults[] = $agent->prompt('Analyze the conversations provided in your instructions.')->toArray();
        }

        $report = count($batchResults) === 1
            ? $batchResults[0]
            : $this->mergeResults($batchResults);

        Mail::to($this->recipient)
            ->send(new ConversationAnalysisReport($report, $windowStart, $windowEnd));
    }

    /**
     * Send a heartbeat report when there are no conversations.
     */
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

    /**
     * Merge multiple batch results into a single report.
     *
     * @param  array<int, array{gap_analysis: array<int, mixed>, prompt_effectiveness: array<int, mixed>, cv_suggestions: array<int, mixed>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}}>  $batchResults
     * @return array{gap_analysis: array<int, mixed>, prompt_effectiveness: array<int, mixed>, cv_suggestions: array<int, mixed>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}}
     */
    private function mergeResults(array $batchResults): array
    {
        $merged = [
            'gap_analysis' => [],
            'prompt_effectiveness' => [],
            'cv_suggestions' => [],
            'summary' => [
                'conversation_count' => 0,
                'message_count' => 0,
                'common_topics' => [],
                'notable_interactions' => '',
                'is_heartbeat' => false,
            ],
        ];

        foreach ($batchResults as $result) {
            $merged['gap_analysis'] = array_merge($merged['gap_analysis'], $result['gap_analysis']);
            $merged['prompt_effectiveness'] = array_merge($merged['prompt_effectiveness'], $result['prompt_effectiveness']);
            $merged['cv_suggestions'] = array_merge($merged['cv_suggestions'], $result['cv_suggestions']);
            $merged['summary']['conversation_count'] += $result['summary']['conversation_count'];
            $merged['summary']['message_count'] += $result['summary']['message_count'];
            $merged['summary']['common_topics'] = array_unique(array_merge(
                $merged['summary']['common_topics'],
                $result['summary']['common_topics'],
            ));

            if ($result['summary']['notable_interactions']) {
                $merged['summary']['notable_interactions'] .= ($merged['summary']['notable_interactions'] ? ' ' : '').$result['summary']['notable_interactions'];
            }
        }

        $merged['summary']['common_topics'] = array_values($merged['summary']['common_topics']);

        return $merged;
    }
}
