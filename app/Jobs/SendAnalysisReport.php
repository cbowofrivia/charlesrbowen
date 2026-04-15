<?php

namespace App\Jobs;

use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendAnalysisReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $batchKey,
        public int $batchCount,
        public string $recipient,
        public CarbonImmutable $windowStart,
        public CarbonImmutable $windowEnd,
    ) {}

    public function handle(): void
    {
        $batchResults = [];

        for ($i = 0; $i < $this->batchCount; $i++) {
            $cacheKey = "{$this->batchKey}:{$i}";
            $batchResults[] = Cache::pull($cacheKey);
        }

        $report = count($batchResults) === 1
            ? $batchResults[0]
            : $this->mergeResults($batchResults);

        Mail::to($this->recipient)
            ->send(new ConversationAnalysisReport($report, $this->windowStart, $this->windowEnd));
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
