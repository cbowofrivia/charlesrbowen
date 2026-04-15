<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Jobs\AnalyzeConversationBatch;
use App\Jobs\AnalyzeConversationsJob;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
    Mail::fake();
});

it('sends a heartbeat report when no conversations exist', function () {
    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->report['summary']['is_heartbeat'] === true
            && $mail->hasTo('test@example.com');
    });
});

it('dispatches a batch of analysis jobs for conversations', function () {
    Bus::fake();

    $conversation = Conversation::factory()->create([
        'created_at' => now()->subDays(5),
    ]);
    $conversation->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Tell me about Charles',
    ]);

    (new AnalyzeConversationsJob(
        windowDays: 30,
        recipient: 'test@example.com',
    ))->handle();

    Bus::assertBatched(function ($batch) {
        return $batch->name === 'Conversation Analysis'
            && $batch->jobs->count() === 1
            && $batch->jobs->first() instanceof AnalyzeConversationBatch;
    });
});

it('creates one batch job per conversation', function () {
    Bus::fake();

    // Create 2 conversations — should result in 2 batch jobs
    for ($i = 0; $i < 2; $i++) {
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

    Bus::assertBatched(function ($batch) {
        return $batch->jobs->count() === 2;
    });
});

it('only includes conversations within the configured window', function () {
    Bus::fake();

    // Old conversation — outside window
    Conversation::factory()->create([
        'created_at' => now()->subDays(14),
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

    Bus::assertBatched(function ($batch) {
        return $batch->jobs->count() === 1;
    });
});
