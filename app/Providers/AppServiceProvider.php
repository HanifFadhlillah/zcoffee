<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS saat diakses via ngrok atau production
        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}