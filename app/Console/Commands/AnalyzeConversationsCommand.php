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
        $cvPath = base_path('documents/cv.md');
        $promptPath = base_path('documents/prompt.md');

        if (! file_exists($cvPath) || ! file_exists($promptPath)) {
            $this->error('Required document files (documents/cv.md, documents/prompt.md) are missing.');

            return self::FAILURE;
        }

        AnalyzeConversationsJob::dispatch(
            windowDays: (int) config('analysis.window_days', 30),
            recipient: (string) config('analysis.recipient'),
        );

        $this->info('Conversation analysis job dispatched.');

        return self::SUCCESS;
    }
}
