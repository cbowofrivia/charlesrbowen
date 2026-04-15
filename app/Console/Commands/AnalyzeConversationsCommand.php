<?php

namespace App\Console\Commands;

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AnalyzeConversationsCommand extends Command
{
    protected $signature = 'conversations:analyze';

    protected $description = 'Analyze recent CvAgent conversations and email an improvement report';

    public function handle(): int
    {
        $windowDays = config('analysis.window_days', 30);
        $recipient = config('analysis.recipient');
        $windowStart = CarbonImmutable::now()->subDays($windowDays)->startOfDay();
        $windowEnd = CarbonImmutable::now();

        $this->info("Analyzing conversations from the last {$windowDays} days...");

        $conversations = Conversation::with('messages')
            ->where('created_at', '>=', $windowStart)
            ->oldest()
            ->get();

        $cvPath = base_path('documents/cv.md');
        $promptPath = base_path('documents/prompt.md');

        if (! file_exists($cvPath) || ! file_exists($promptPath)) {
            $this->error('Required document files (documents/cv.md, documents/prompt.md) are missing.');

            return self::FAILURE;
        }

        $cvContent = (string) file_get_contents($cvPath);
        $promptContent = (string) file_get_contents($promptPath);

        $agent = new ConversationAnalysisAgent(
            conversations: $conversations,
            cvContent: $cvContent,
            promptContent: $promptContent,
        );

        $this->info("Found {$conversations->count()} conversations. Running analysis...");

        $response = $agent->prompt('Analyze the conversations provided in your instructions.');

        /** @var array{gap_analysis: array<int, array{topic: string, description: string, evidence: string, severity: string}>, prompt_effectiveness: array<int, array{observation: string, example: string, suggestion: string}>, cv_suggestions: array<int, array{section: string, recommendation: string, rationale: string}>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}} $report */
        $report = $response->toArray();

        Mail::to($recipient)
            ->send(new ConversationAnalysisReport($report, $windowStart, $windowEnd));

        $this->info("Report sent to {$recipient}.");

        return self::SUCCESS;
    }
}
