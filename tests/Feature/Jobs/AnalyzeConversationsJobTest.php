<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Jobs\AnalyzeConversationsJob;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
    Mail::fake();
});

it('sends an analysis report email', function () {
    $conversation = Conversation::factory()->create([
        'created_at' => now()->subDays(5),
    ]);
    $conversation->messages()->createMany([
        ['role' => MessageRole::User, 'content' => 'Tell me about Charles'],
        ['role' => MessageRole::Assistant, 'content' => 'Charles is a product engineer.'],
    ]);

    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});

it('sends a heartbeat report when no conversations exist', function () {
    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->report['summary']['is_heartbeat'] === true;
    });

    ConversationAnalysisAgent::assertNeverPrompted();
});

it('only includes conversations within the configured window', function () {
    // Old conversation — outside window
    $old = Conversation::factory()->create([
        'created_at' => now()->subDays(14),
    ]);
    $old->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Old message',
    ]);

    // Recent conversation — inside window
    $recent = Conversation::factory()->create([
        'created_at' => now()->subDays(3),
    ]);
    $recent->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Recent message',
    ]);

    (new AnalyzeConversationsJob(
        windowDays: 7,
        recipient: 'test@example.com',
    ))->handle();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'Recent message')
            && ! str_contains($instructions, 'Old message');
    });
});

it('batches conversations into groups of 5', function () {
    $promptCount = 0;

    ConversationAnalysisAgent::fake(function () use (&$promptCount) {
        $promptCount++;

        return null;
    });

    // Create 8 conversations — should result in 2 batches (5 + 3)
    for ($i = 0; $i < 8; $i++) {
        $conversation = Conversation::factory()->create([
            'created_at' => now()->subDays(5),
        ]);
        $conversation->messages()->create([
            'role' => MessageRole::User,
            'content' => "Message {$i}",
        ]);
    }

    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    expect($promptCount)->toBe(2);
    Mail::assertSent(ConversationAnalysisReport::class);
});

it('prompts the agent with cv and prompt content', function () {
    $conversation = Conversation::factory()->create([
        'created_at' => now()->subDays(5),
    ]);
    $conversation->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Hello',
    ]);

    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'Product Engineer')
            && str_contains($instructions, 'Agent Instructions');
    });
});
