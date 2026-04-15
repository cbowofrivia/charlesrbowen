<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
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

    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->hasTo(config('analysis.recipient'));
    });
});

it('sends a heartbeat report when no conversations exist', function () {
    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    Mail::assertSent(ConversationAnalysisReport::class);
});

it('only includes conversations within the configured window', function () {
    config(['analysis.window_days' => 7]);

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

    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'Recent message')
            && ! str_contains($instructions, 'Old message');
    });
});

it('fails when document files are missing', function () {
    $cvPath = base_path('documents/cv.md');
    $promptPath = base_path('documents/prompt.md');

    rename($cvPath, $cvPath.'.bak');

    try {
        $this->artisan('conversations:analyze')
            ->assertFailed();

        Mail::assertNothingSent();
    } finally {
        rename($cvPath.'.bak', $cvPath);
    }
});

it('prompts the agent with cv and prompt content', function () {
    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'Product Engineer')
            && str_contains($instructions, 'Agent Instructions');
    });
});
