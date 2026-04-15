<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
#[Temperature(0.3)]
#[MaxTokens(4096)]
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

        Analyze the conversations above. Be ruthlessly selective — only flag things that genuinely need attention. A conversation where the agent answered well is not a finding. An observation that something "could be better" without a clear problem is not a finding.

        1. **Gap Analysis** — Only flag genuine failures: questions the agent couldn't answer, topics where it explicitly said "not in the CV," or areas where the response was clearly wrong or misleading. Do not flag things the agent handled adequately. Maximum 3 items — if nothing significant, return an empty array.

        2. **Prompt Effectiveness** — Only flag clear violations of the prompt instructions or responses that would actively harm the user's impression. Do not flag things that are "working fine" or "could be slightly better." Maximum 2 items — if the agent behaved well, return an empty array.

        3. **CV Content Suggestions** — Only suggest changes that would materially improve the CV. Each suggestion must identify a specific, concrete problem (not a hypothetical improvement). Do not suggest rewording things that already work. Maximum 3 items.

        4. **Conversation Summary** — Brief overview: conversation and message counts, top 3 topics, and only genuinely notable interactions (not routine questions). Keep the notable_interactions field to 1-2 sentences maximum.

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
