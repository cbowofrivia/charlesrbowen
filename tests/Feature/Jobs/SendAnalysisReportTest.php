<?php

use App\Ai\Agents\ReportSynthesisAgent;
use App\Jobs\SendAnalysisReport;
use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    ReportSynthesisAgent::fake();
    Mail::fake();
});

it('sends the report directly when there is only one batch', function () {
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

    // Should not invoke synthesis agent for a single batch
    ReportSynthesisAgent::assertNeverPrompted();

    // Cache should be cleaned up
    expect(Cache::get('test-batch:0'))->toBeNull();
});

it('uses the synthesis agent to consolidate multiple batches', function () {
    Cache::put('test-batch:0', [
        'gap_analysis' => [['topic' => 'DevOps', 'description' => 'D1', 'evidence' => 'E1', 'severity' => 'high']],
        'prompt_effectiveness' => [],
        'cv_suggestions' => [],
        'summary' => ['conversation_count' => 3, 'message_count' => 6, 'common_topics' => ['DevOps'], 'notable_interactions' => 'Batch 1.', 'is_heartbeat' => false],
    ], now()->addHour());

    Cache::put('test-batch:1', [
        'gap_analysis' => [['topic' => 'DevOps', 'description' => 'D2', 'evidence' => 'E2', 'severity' => 'high']],
        'prompt_effectiveness' => [],
        'cv_suggestions' => [],
        'summary' => ['conversation_count' => 2, 'message_count' => 4, 'common_topics' => ['DevOps'], 'notable_interactions' => 'Batch 2.', 'is_heartbeat' => false],
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

    // Synthesis agent should be invoked with both batch results
    ReportSynthesisAgent::assertPrompted(fn ($prompt) => str_contains($prompt->agent->instructions(), 'DevOps'));
    Mail::assertSent(ConversationAnalysisReport::class);
});
