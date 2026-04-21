<?php

use Illuminate\Console\Scheduling\Schedule;

it('registers the conversations:analyze command on the configured schedule', function () {
    $schedule = app(Schedule::class);

    $events = collect($schedule->events())->filter(function ($event) {
        return str_contains($event->command, 'conversations:analyze');
    });

    expect($events)->not->toBeEmpty();
});

it('uses the cron expression from config', function () {
    $schedule = app(Schedule::class);

    $event = collect($schedule->events())->first(function ($event) {
        return str_contains($event->command, 'conversations:analyze');
    });

    expect($event->expression)->toBe(config('analysis.schedule'));
});
