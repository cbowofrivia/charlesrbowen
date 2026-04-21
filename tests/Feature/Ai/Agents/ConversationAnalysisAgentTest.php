<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Models\Conversation;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
});

it('produces structured output with the expected schema keys', function () {
    $response = (new ConversationAnalysisAgent(
        conversations: collect(),
        cvContent: 'Test CV content',
        promptContent: 'Test prompt content',
    ))->prompt('Analyze these conversations.');

    expect($response['gap_analysis'])->toBeArray();
    expect($response['prompt_effectiveness'])->toBeArray();
    expect($response['cv_suggestions'])->toBeArray();
    expect($response['summary'])->toBeArray();
});

it('includes conversation data in its instructions', function () {
    $conversation = Conversation::factory()->create();
    $conversation->messages()->createMany([
        ['role' => MessageRole::User, 'content' => 'What DevOps experience does Charles have?'],
        ['role' => MessageRole::Assistant, 'content' => 'That information is not in the CV.'],
    ]);

    $agent = new ConversationAnalysisAgent(
        conversations: Conversation::with('messages')->get(),
        cvContent: 'Test CV content',
        promptContent: 'Test prompt content',
    );

    $instructions = $agent->instructions();

    expect((string) $instructions)
        ->toContain('What DevOps experience does Charles have?')
        ->toContain('That information is not in the CV.')
        ->toContain('Test CV content')
        ->toContain('Test prompt content');
});
