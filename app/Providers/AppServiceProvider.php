<?php

namespace App\Providers;

use App\TODB\TODBClient;
use App\TODB\TODBClientInterface;
use DB;
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
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->app->bind(TODBClientInterface::class, TODBClient::class);

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
