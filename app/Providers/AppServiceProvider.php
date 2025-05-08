<?php

namespace App\Providers;

use App\Contracts\Reporter;
use App\Reporting\ConsoleReporter;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Reporter::class, function ($app) {
            return new ConsoleReporter($app->make(Command::class));
        });
    }
}
