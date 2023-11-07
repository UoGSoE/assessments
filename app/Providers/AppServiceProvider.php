<?php

namespace App\Providers;

use App\Wlm\WlmClient;
use App\Wlm\WlmClientInterface;
use Eluceo\iCal\Component\Calendar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->app->bind(WlmClientInterface::class, WlmClient::class);

        $this->app->bind(Calendar::class, function ($app) {
            return new Calendar(url('/'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->environment('local', 'testing')) {
            //$this->app->register(DuskServiceProvider::class);
        }
    }
}
