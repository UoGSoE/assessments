<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\DuskServiceProvider;
use Eluceo\iCal\Component\Calendar;
use App\Wlm\WlmClientInterface;
use App\Wlm\WlmClient;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
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
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            //$this->app->register(DuskServiceProvider::class);
        }
    }
}
