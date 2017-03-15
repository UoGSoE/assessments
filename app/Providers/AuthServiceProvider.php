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
            if (!$student->is_student) {
                return false;
            }
            if ($student->notOnCourse($assessment->course)) {
                return false;
            }
            if ($assessment->deadline->lt(Carbon::now()->subMonths(3))) {
                return false;
            }
            if ($assessment->notOverdue()) {
                return false;
            }
            if ($student->hasLeftFeedbackFor($assessment)) {
                return false;
            }
            return true;
        });
        Gate::define('can_see_assessment', function ($user, $assessment) {
            if ($user->is_admin) {
                return true;
            }
            if ($user->notOnCourse($assessment->course)) {
                return false;
            }
            return true;
        });
        Gate::define('see_feedbacks', function ($user, $assessment) {
            if ($user->is_admin) {
                return true;
            }
            if ($user->id != $assessment->user_id) {
                return false;
            }
            return true;
        });
    }
}
