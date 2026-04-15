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
