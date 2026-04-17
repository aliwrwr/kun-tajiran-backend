<?php

namespace App\Providers;

use App\Services\FcmService;
use App\Services\FirestoreService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FcmService::class);
        $this->app->singleton(FirestoreService::class);
    }

    public function boot(): void
    {
        //
    }
}
