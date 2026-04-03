<?php

use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;

it('belongs to a conversation', function () {
    $message = Message::factory()->create();

    expect($message->conversation)->toBeInstanceOf(Conversation::class);
});

it('casts role to MessageRole enum', function () {
    $message = Message::factory()->fromUser()->create();

    expect($message->role)->toBe(MessageRole::User);
});

it('has user and assistant factory states', function () {
    $userMessage = Message::factory()->fromUser()->create();
    $assistantMessage = Message::factory()->fromAssistant()->create();

    expect($userMessage->role)->toBe(MessageRole::User)
        ->and($assistantMessage->role)->toBe(MessageRole::Assistant);
});
