<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Jobs\AnalyzeConversationBatch;
use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
});

it('analyzes conversations and stores the result in cache', function () {
    $conversation = Conversation::factory()->create();
    $conversation->messages()->createMany([
        ['role' => MessageRole::User, 'content' => 'Tell me about Charles'],
        ['role' => MessageRole::Assistant, 'content' => 'Charles is a product engineer.'],
    ]);

    $job = new AnalyzeConversationBatch(
        batchKey: 'test-batch',
        batchIndex: 0,
        conversations: Conversation::with('messages')->get(),
        cvContent: 'Test CV',
        promptContent: 'Test prompt',
    );

    $job->handle();

    $result = Cache::get('test-batch:0');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['gap_analysis', 'prompt_effectiveness', 'cv_suggestions', 'summary']);
});

it('passes conversation content to the agent', function () {
    $conversation = Conversation::factory()->create();
    $conversation->messages()->create([
        'role' => MessageRole::User,
        'content' => 'What about DevOps?',
    ]);

    $job = new AnalyzeConversationBatch(
        batchKey: 'test-batch',
        batchIndex: 0,
        conversations: Conversation::with('messages')->get(),
        cvContent: 'Test CV content',
        promptContent: 'Test prompt content',
    );

    $job->handle();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'What about DevOps?')
            && str_contains($instructions, 'Test CV content');
    });
});
