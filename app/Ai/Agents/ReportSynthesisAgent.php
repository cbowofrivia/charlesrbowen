<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
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
class ReportSynthesisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, array{gap_analysis: array<int, mixed>, prompt_effectiveness: array<int, mixed>, cv_suggestions: array<int, mixed>, summary: array<string, mixed>}>  $batchResults
     */
    public function __construct(
        protected array $batchResults,
    ) {}

    public function instructions(): Stringable|string
    {
        $serialized = json_encode($this->batchResults, JSON_PRETTY_PRINT);

        return <<<PROMPT
        You are an expert report synthesizer. You have received analysis results from multiple batches of conversation reviews. Each batch independently analyzed a subset of visitor conversations with a CV chatbot agent.

        Your job is to consolidate these batch results into a single, coherent report by:

        1. **Deduplicating** — Multiple batches may flag the same gap, suggestion, or observation. Merge similar items into one, combining their evidence and examples. Do not repeat the same finding twice.

        2. **Spotting patterns** — Look across batches for recurring themes. If 3 out of 4 batches mention visitors asking about DevOps, that's a strong signal worth highlighting. Prioritise findings that appear across multiple batches.

        3. **Ranking by severity** — Order gap analysis items by severity (high → medium → low). For items with the same severity, prioritise those with evidence from multiple batches.

        4. **Producing accurate summary stats** — Sum conversation and message counts across all batches. Deduplicate common topics. Combine notable interactions into a concise summary.

        5. **Writing concisely** — Each finding should be clear and actionable. Don't pad the report. If there's nothing meaningful to say in a section, return an empty array for that section.

        ## Batch Results to Synthesize

        {$serialized}
        PROMPT;
    }

    /**
     * Same schema as ConversationAnalysisAgent — the final report shape.
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
}
