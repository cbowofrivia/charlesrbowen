<?php

namespace App\Ai\Agents;

use App\Ai\Concerns\HasAnalysisReportSchema;
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
    use HasAnalysisReportSchema, Promptable;

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

        Your job is to consolidate these batch results into a single, short, actionable report. Be aggressive about cutting — the reader wants a quick scan, not a document.

        Rules:

        1. **Deduplicate ruthlessly** — If multiple batches flag the same thing, merge into one item. Combine evidence. Never repeat a finding.

        2. **Cut low-value items** — Drop anything that's a minor observation, hypothetical improvement, or "nice to have." Only keep findings that represent genuine problems or high-impact opportunities.

        3. **Cap each section** — Maximum 3 gap analysis items, 2 prompt effectiveness items, 3 CV suggestions. If a section has nothing worth keeping after filtering, return an empty array.

        4. **Keep it brief** — Each item should be 1-2 sentences for description/observation, one short quote for evidence/example. No essays. The notable_interactions summary should be 1-2 sentences maximum.

        5. **Accurate stats** — Sum conversation and message counts across batches. Deduplicate common topics, keep the top 5 maximum.

        ## Batch Results to Synthesize

        {$serialized}
        PROMPT;
    }
}
