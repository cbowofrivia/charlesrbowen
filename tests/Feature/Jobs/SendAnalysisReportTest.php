<?php

use App\Jobs\SendAnalysisReport;
use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

it('sends the analysis report email from cached batch results', function () {
    Cache::put('test-batch:0', [
        'gap_analysis' => [['topic' => 'DevOps', 'description' => 'Asked about Docker', 'evidence' => 'Quote', 'severity' => 'high']],
        'prompt_effectiveness' => [],
        'cv_suggestions' => [],
        'summary' => ['conversation_count' => 3, 'message_count' => 6, 'common_topics' => ['DevOps'], 'notable_interactions' => '', 'is_heartbeat' => false],
    ], now()->addHour());

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    (new SendAnalysisReport(
        batchKey: 'test-batch',
        batchCount: 1,
        recipient: 'test@example.com',
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ))->handle();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->hasTo('test@example.com')
            && $mail->report['summary']['conversation_count'] === 3;
    });

    // Cache should be cleaned up
    expect(Cache::get('test-batch:0'))->toBeNull();
});

it('merges multiple batch results', function () {
    Cache::put('test-batch:0', [
        'gap_analysis' => [['topic' => 'DevOps', 'description' => 'D1', 'evidence' => 'E1', 'severity' => 'high']],
        'prompt_effectiveness' => [],
        'cv_suggestions' => [],
        'summary' => ['conversation_count' => 3, 'message_count' => 6, 'common_topics' => ['DevOps'], 'notable_interactions' => 'Batch 1 notable.', 'is_heartbeat' => false],
    ], now()->addHour());

    Cache::put('test-batch:1', [
        'gap_analysis' => [['topic' => 'CI/CD', 'description' => 'D2', 'evidence' => 'E2', 'severity' => 'medium']],
        'prompt_effectiveness' => [['observation' => 'Too verbose', 'example' => 'Ex', 'suggestion' => 'Fix']],
        'cv_suggestions' => [],
        'summary' => ['conversation_count' => 2, 'message_count' => 4, 'common_topics' => ['CI/CD', 'DevOps'], 'notable_interactions' => 'Batch 2 notable.', 'is_heartbeat' => false],
    ], now()->addHour());

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    (new SendAnalysisReport(
        batchKey: 'test-batch',
        batchCount: 2,
        recipient: 'test@example.com',
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ))->handle();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        $report = $mail->report;

        return count($report['gap_analysis']) === 2
            && count($report['prompt_effectiveness']) === 1
            && $report['summary']['conversation_count'] === 5
            && $report['summary']['message_count'] === 10
            && count($report['summary']['common_topics']) === 2
            && str_contains($report['summary']['notable_interactions'], 'Batch 1')
            && str_contains($report['summary']['notable_interactions'], 'Batch 2');
    });
});
