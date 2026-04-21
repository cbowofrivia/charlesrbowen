<?php

namespace App\Ai\Concerns;

use Illuminate\Contracts\JsonSchema\JsonSchema;

trait HasAnalysisReportSchema
{
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
