<?php

use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;

it('renders the report with all sections when data is present', function () {
    $report = [
        'gap_analysis' => [
            [
                'topic' => 'DevOps experience',
                'description' => 'Visitors asked about Docker/Kubernetes experience',
                'evidence' => 'User asked: "What DevOps experience does Charles have?"',
                'severity' => 'high',
            ],
        ],
        'prompt_effectiveness' => [
            [
                'observation' => 'Agent was too verbose in responses',
                'example' => 'Response was 500 words for a simple question',
                'suggestion' => 'Add instruction to keep responses under 200 words',
            ],
        ],
        'cv_suggestions' => [
            [
                'section' => 'Technical Skills',
                'recommendation' => 'Add Docker and Kubernetes prominently',
                'rationale' => '3 visitors asked about containerization',
            ],
        ],
        'summary' => [
            'conversation_count' => 12,
            'message_count' => 48,
            'common_topics' => ['Technical skills', 'Work experience', 'Availability'],
            'notable_interactions' => 'One visitor had a 20-message conversation about billing platform architecture.',
            'is_heartbeat' => false,
        ],
    ];

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    $mailable = new ConversationAnalysisReport($report, $windowStart, $windowEnd);

    $mailable->assertHasSubject('CV Agent Analysis Report — 16 Mar to 15 Apr 2026');
    $mailable->assertSeeInHtml('DevOps experience');
    $mailable->assertSeeInHtml('Agent was too verbose');
    $mailable->assertSeeInHtml('Add Docker and Kubernetes');
    $mailable->assertSeeInHtml('12 conversations');
    $mailable->assertSeeInHtml('48 messages');
});

it('renders a heartbeat report when there are no conversations', function () {
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

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    $mailable = new ConversationAnalysisReport($report, $windowStart, $windowEnd);

    $mailable->assertSeeInHtml('No conversations in this period');
});
