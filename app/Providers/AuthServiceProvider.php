<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('can_leave_feedback', function ($student, $assessment) {
            if ($student->notOnCourse($assessment->course)) {
                return false;
            }
            if ($assessment->deadline->lt(Carbon::now()->subMonths(3))) {
                return false;
            }
            if ($assessment->notOverdue()) {
                return false;
            }
            return true;
        });
    }
}
