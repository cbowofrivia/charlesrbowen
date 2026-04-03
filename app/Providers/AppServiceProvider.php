<?php

namespace App\Providers;

use App\Services\SystemPromptService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemPromptService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute((int) config('app.chat_rate_limit', 20))
                ->by($request->input('session_id', $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'You are sending messages too quickly. Please wait a moment before trying again.',
                        'retry_after' => $headers['Retry-After'],
                    ], 429, $headers);
                });
        });
    }
}
