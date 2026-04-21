<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analysis Window
    |--------------------------------------------------------------------------
    |
    | The number of days to look back when analyzing conversations. The
    | analysis agent will receive all conversations from this rolling
    | window, regardless of whether they were included in a previous
    | report.
    |
    */

    'window_days' => (int) env('ANALYSIS_WINDOW_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | How often the analysis command should run. Accepts any valid cron
    | expression. Defaults to weekly (Mondays at 9:00 AM).
    |
    */

    'schedule' => env('ANALYSIS_SCHEDULE', '0 9 * * 1'),

    /*
    |--------------------------------------------------------------------------
    | Recipient
    |--------------------------------------------------------------------------
    |
    | The email address that analysis reports should be sent to.
    |
    */

    'recipient' => env('ANALYSIS_RECIPIENT', 'charlesrbowen@gmail.com'),

];
