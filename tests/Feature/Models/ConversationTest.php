<?php

use App\Models\Conversation;
use App\Models\Message;

it('has many messages', function () {
    $conversation = Conversation::factory()
        ->has(Message::factory()->count(3))
        ->create();

    expect($conversation->messages)->toHaveCount(3);
});

it('cascades deletes to messages', function () {
    $conversation = Conversation::factory()
        ->has(Message::factory()->count(2))
        ->create();

    $conversation->delete();

    expect(Message::count())->toBe(0);
});
