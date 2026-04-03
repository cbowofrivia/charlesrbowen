<?php

use App\Ai\Agents\CvAgent;
use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;

beforeEach(function () {
    CvAgent::fake(['Hello! Charles is a product engineer.']);
});

it('creates a conversation and persists the user message', function () {
    $sessionId = Str::uuid()->toString();

    $this->post(route('chat'), [
        'message' => 'Tell me about Charles',
        'session_id' => $sessionId,
    ])->assertSuccessful();

    expect(Conversation::where('session_id', $sessionId)->exists())->toBeTrue();
    expect(Message::where('role', MessageRole::User)->count())->toBe(1);
    expect(Message::where('role', MessageRole::User)->first()->content)->toBe('Tell me about Charles');
});

it('reuses an existing conversation for the same session_id', function () {
    $sessionId = Str::uuid()->toString();
    Conversation::factory()->create(['session_id' => $sessionId]);

    $this->post(route('chat'), [
        'message' => 'What are his skills?',
        'session_id' => $sessionId,
    ])->assertSuccessful();

    expect(Conversation::where('session_id', $sessionId)->count())->toBe(1);
});

it('returns a streaming response', function () {
    $this->post(route('chat'), [
        'message' => 'Hello',
        'session_id' => Str::uuid()->toString(),
    ])->assertSuccessful()
        ->assertHeader('content-type', 'text/event-stream; charset=UTF-8');
});

it('validates that message is required', function () {
    $this->post(route('chat'), [
        'session_id' => Str::uuid()->toString(),
    ])->assertInvalid(['message']);
});

it('rate limits excessive requests per session', function () {
    config(['app.chat_rate_limit' => 3]);
    $sessionId = Str::uuid()->toString();

    for ($i = 0; $i < 3; $i++) {
        $this->post(route('chat'), [
            'message' => "Message {$i}",
            'session_id' => $sessionId,
        ])->assertSuccessful();
    }

    $this->post(route('chat'), [
        'message' => 'One too many',
        'session_id' => $sessionId,
    ])->assertTooManyRequests()
        ->assertHeader('Retry-After');
});

it('validates that session_id is required and must be a uuid', function () {
    $this->post(route('chat'), [
        'message' => 'Hello',
    ])->assertInvalid(['session_id']);

    $this->post(route('chat'), [
        'message' => 'Hello',
        'session_id' => 'not-a-uuid',
    ])->assertInvalid(['session_id']);
});
