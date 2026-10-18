<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure rate limiter for AI note generation
        // Limit: 10 generations per hour per regular student (unlimited for admin)
        RateLimiter::for('note-generation', function (Request $request) {
            if ($request->user()?->isAdmin()) {
                return Limit::none();
            }
            return Limit::perHour(10)->by($request->user()?->id);
        });

        // Configure rate limiter for AI quiz generation
        // Limit: 10 generations per hour per regular student (unlimited for admin)
        RateLimiter::for('quiz-generation', function (Request $request) {
            if ($request->user()?->isAdmin()) {
                return Limit::none();
            }
            return Limit::perHour(10)->by($request->user()?->id);
        });
    }
}
