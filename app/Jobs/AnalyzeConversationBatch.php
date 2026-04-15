<?php

namespace App\Jobs;

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Models\Conversation;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AnalyzeConversationBatch implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * @param  Collection<int, Conversation>  $conversations
     */
    public function __construct(
        public string $batchKey,
        public int $batchIndex,
        public Collection $conversations,
        public string $cvContent,
        public string $promptContent,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $agent = new ConversationAnalysisAgent(
            conversations: $this->conversations,
            cvContent: $this->cvContent,
            promptContent: $this->promptContent,
        );

        $result = $agent->prompt('Analyze the conversations provided in your instructions.')->toArray();

        Cache::put("{$this->batchKey}:{$this->batchIndex}", $result, now()->addHour());
    }
}
