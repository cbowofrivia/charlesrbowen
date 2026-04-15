<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Temperature(0.3)]
#[MaxTokens(4096)]
#[Timeout(300)]
class ConversationAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected Collection $conversations,
        protected string $cvContent,
        protected string $promptContent,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $conversationData = $this->formatConversations();

        return <<<PROMPT
        You are an expert conversation analyst. Your job is to review interactions that visitors have had with a CV chatbot agent and produce actionable feedback.

        ## Context

        The chatbot agent represents Charles Bowen on his portfolio website. It answers questions about his professional experience, skills, and background using the CV and prompt instructions provided below.

        ### Current Agent Prompt Instructions

        {$this->promptContent}

        ### Current CV Content

        {$this->cvContent}

        ### Conversations to Analyze

        {$conversationData}

        ## Your Task

        Analyze the conversations above and produce a structured report covering:

        1. **Gap Analysis** — Identify questions the agent couldn't answer well, topics where it said "not in the CV" or gave vague responses, and areas visitors expected to be covered but weren't. Include specific quotes from conversations as evidence.

        2. **Prompt Effectiveness** — Evaluate whether the agent's tone, format, and behavior matched the prompt instructions. Flag responses that were too verbose, too terse, off-brand, broke guardrails, or could have been better structured. Include specific examples.

        3. **CV Content Suggestions** — Based on visitor interest patterns, suggest specific additions, updates, or reorganizations to the CV content. Focus on gaps that multiple visitors hit or topics that generated the most engagement.

        4. **Conversation Summary** — Provide an overview: total conversations analyzed, total messages, most common topics, and any notable or unusual interactions worth highlighting.

        If there are no conversations to analyze, provide a brief heartbeat report noting that the system is running but there was no activity.
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'gap_analysis' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'topic' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'evidence' => $schema->string()->required(),
                        'severity' => $schema->string()->enum(['low', 'medium', 'high'])->required(),
                    ])
                )
                ->required(),
            'prompt_effectiveness' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'observation' => $schema->string()->required(),
                        'example' => $schema->string()->required(),
                        'suggestion' => $schema->string()->required(),
                    ])
                )
                ->required(),
            'cv_suggestions' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'section' => $schema->string()->required(),
                        'recommendation' => $schema->string()->required(),
                        'rationale' => $schema->string()->required(),
                    ])
                )
                ->required(),
            'summary' => $schema->object(fn (JsonSchema $schema) => [
                'conversation_count' => $schema->integer()->required(),
                'message_count' => $schema->integer()->required(),
                'common_topics' => $schema->array()->items($schema->string())->required(),
                'notable_interactions' => $schema->string()->required(),
                'is_heartbeat' => $schema->boolean()->required(),
            ])->required(),
        ];
    }

    /**
     * Format conversations into a readable string for the agent.
     */
    protected function formatConversations(): string
    {
        if ($this->conversations->isEmpty()) {
            return 'No conversations in this analysis window.';
        }

        return $this->conversations->map(function ($conversation, $index) {
            $messages = $conversation->messages->map(function ($message) {
                $role = strtoupper($message->role->value);

                return "[{$role}]: {$message->content}";
            })->implode("\n");

            $date = $conversation->created_at->format('Y-m-d H:i');

            return '--- Conversation #'.($index + 1)." ({$date}) ---\n{$messages}";
        })->implode("\n\n");
    }
}
