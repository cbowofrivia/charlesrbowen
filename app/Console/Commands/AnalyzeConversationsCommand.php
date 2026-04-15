<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeConversationsJob;
use Illuminate\Console\Command;

class AnalyzeConversationsCommand extends Command
{
    protected $signature = 'conversations:analyze';

    protected $description = 'Analyze recent CvAgent conversations and email an improvement report';

    public function handle(): int
    {
        AnalyzeConversationsJob::dispatch(
            windowDays: (int) config('analysis.window_days', 30),
            recipient: (string) config('analysis.recipient'),
        );

        $this->info('Conversation analysis job dispatched.');

        return self::SUCCESS;
    }
}
