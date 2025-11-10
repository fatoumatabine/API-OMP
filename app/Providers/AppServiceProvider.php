<?php

namespace App\Providers;

use App\Interfaces\CompteServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Services\CompteService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(CompteServiceInterface::class, CompteService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
