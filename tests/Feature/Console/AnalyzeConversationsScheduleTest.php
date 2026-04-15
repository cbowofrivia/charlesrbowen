<?php

use Illuminate\Console\Scheduling\Schedule;

it('registers the conversations:analyze command on the configured schedule', function () {
    $schedule = app(Schedule::class);

    $events = collect($schedule->events())->filter(function ($event) {
        return str_contains($event->command, 'conversations:analyze');
    });

    expect($events)->not->toBeEmpty();
});
