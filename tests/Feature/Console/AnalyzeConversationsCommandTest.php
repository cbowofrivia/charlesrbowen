<?php

use App\Jobs\AnalyzeConversationsJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('dispatches the analysis job', function () {
    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    Queue::assertPushed(AnalyzeConversationsJob::class, function ($job) {
        return $job->windowDays === (int) config('analysis.window_days')
            && $job->recipient === config('analysis.recipient');
    });
});

it('fails when document files are missing', function () {
    $cvPath = base_path('documents/cv.md');

    rename($cvPath, $cvPath.'.bak');

    try {
        $this->artisan('conversations:analyze')
            ->assertFailed();

        Queue::assertNothingPushed();
    } finally {
        rename($cvPath.'.bak', $cvPath);
    }
});
