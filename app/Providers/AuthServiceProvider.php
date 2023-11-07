<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
    public function boot(): void
    {
        Gate::define('edit_assessments', function ($user) {
            if ($user->is_admin) {
                return true;
            }

            return false;
        });

        Gate::define('leave_feedback', function ($student, $assessment) {
            if (! $student->is_student) {
                return false;
            }
            if ($student->notOnCourse($assessment->course)) {
                return false;
            }
            if ($assessment->isReallyOld()) {
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

        Gate::define('see_assessment', function ($user, $assessment) {
            if ($user->is_admin) {
                return true;
            }
            if ($user->id == $assessment->staff_id) {
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
            if ($user->id != $assessment->staff_id) {
                return false;
            }

            return true;
        });

        Gate::define('complete_feedback', function ($user, $assessment) {
            if ($user->is_admin) {
                return true;
            }
            if ($assessment->deadline->gte(Carbon::now())) {
                return false;
            }
            if ($user->id == $assessment->staff_id) {
                return true;
            }

            return false;
        });

        Gate::define('see_course', function ($user, $course) {
            if ($user->is_admin) {
                return true;
            }
            if ($user->notOnCourse($course)) {
                return false;
            }

            return true;
        });

        Gate::define('view_students', function ($user) {
            if ($user->is_student) {
                return false;
            }

            return true;
        });
    }
}
