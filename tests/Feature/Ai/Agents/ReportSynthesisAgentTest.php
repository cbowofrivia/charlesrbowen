<?php

use App\Ai\Agents\ReportSynthesisAgent;

beforeEach(function () {
    ReportSynthesisAgent::fake();
});

it('produces structured output with the expected schema keys', function () {
    $batchResults = [
        [
            'gap_analysis' => [['topic' => 'DevOps', 'description' => 'D', 'evidence' => 'E', 'severity' => 'high']],
            'prompt_effectiveness' => [],
            'cv_suggestions' => [],
            'summary' => ['conversation_count' => 3, 'message_count' => 6, 'common_topics' => ['DevOps'], 'notable_interactions' => '', 'is_heartbeat' => false],
        ],
    ];

    $response = (new ReportSynthesisAgent($batchResults))
        ->prompt('Synthesize these results.');

    expect($response['gap_analysis'])->toBeArray();
    expect($response['prompt_effectiveness'])->toBeArray();
    expect($response['cv_suggestions'])->toBeArray();
    expect($response['summary'])->toBeArray();
});

it('includes batch results in its instructions', function () {
    $batchResults = [
        [
            'gap_analysis' => [['topic' => 'DevOps experience', 'description' => 'D', 'evidence' => 'E', 'severity' => 'high']],
            'prompt_effectiveness' => [],
            'cv_suggestions' => [],
            'summary' => ['conversation_count' => 3, 'message_count' => 6, 'common_topics' => ['DevOps'], 'notable_interactions' => '', 'is_heartbeat' => false],
        ],
    ];

    $agent = new ReportSynthesisAgent($batchResults);
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('DevOps experience')
        ->toContain('Deduplicate')
        ->toContain('Cap each section');
});
