<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // File upload: 5 requests per minute per user
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(5)->by($request->user('siswa')?->id ?: $request->ip());
        });

        // Heavy computation endpoints: 10 requests per minute per user
        RateLimiter::for('heavy', function (Request $request) {
            return Limit::perMinute(10)->by($request->user('dosen')?->id ?: $request->ip());
        });

        // Login: 5 attempts per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
