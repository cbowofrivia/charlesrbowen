<?php

namespace App\Ai\Agents;

use App\Models\Conversation;
use App\Services\SystemPromptService;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class CvAgent implements Agent, Conversational
{
    use Promptable;

    public function __construct(
        protected Conversation $conversation,
        protected SystemPromptService $promptService,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->promptService->getPrompt();
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return $this->conversation->messages()
            ->oldest()
            ->get()
            ->map(fn ($message) => new Message($message->role->value, $message->content))
            ->all();
    }
}
