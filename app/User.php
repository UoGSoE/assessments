<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Course;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;
    use CanConvertAssessmentsToJson;
    use CanBeCreatedFromOutsideSources;
    use InteractsWithFeedbacks;

    protected $fillable = [
        'username', 'email', 'password', 'surname', 'forenames', 'is_student'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if ($user->isStaff()) {
                $user->assessments->each->delete();
            }
            if ($user->isStudent()) {
                $user->feedbacks->each->delete();
            }
            $user->courses()->detach();
        });
    }

    public function courses()
    {
        if ($this->is_student) {
            return $this->belongsToMany(Course::class, 'course_student', 'student_id');
        }
        return $this->belongsToMany(Course::class, 'course_staff', 'staff_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'staff_id');
    }

    public function assessmentsWhereFeedbacksDue()
    {
        $cutoff = Carbon::now()->subDays(config('assessments.feedback_grace_days'));
        return $this->assessments()->where('deadline', '<=', $cutoff);
    }

    public function orderedAssessments()
    {
        return $this->hasMany(Assessment::class, 'staff_id')->orderBy('deadline')->get();
    }

    public function numberOfAssessments()
    {
        return (int) $this->assessments()->count();
    }

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class, 'student_id');
    }

    public function getAssessmentsWithStudentFeedback()
    {
        return $this->assessments()->has('feedbacks')->get();
    }

    public function numberOfStaffFeedbacks()
    {
        return $this->getAssessmentsWithStudentFeedback()
                    ->reduce(function ($carry, $assessment) {
                        return $carry + $assessment->totalNegativeFeedbacks();
                    }, 0);
    }

    public function numberOfMissedDeadlines()
    {
        return count($this->assessmentsWhereFeedbacksDue()->get()
                    ->filter
                    ->feedbackWasGivenLate());
    }

    public function newFeedbacks()
    {
        return $this->assessments()->with('course', 'feedbacks')
                    ->get()
                    ->flatMap(function ($assessment) {
                        return $assessment->negativeFeedbacks->filter->staffNotNotified();
                    });
    }

    public function getMatricAttribute()
    {
        return preg_replace('/[^0-9]/', '', $this->username);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_student', '=', false);
    }

    public function scopeStudent($query)
    {
        return $query->where('is_student', '=', true);
    }

    public function fullName()
    {
        return $this->surname . ', ' . $this->forenames;
    }

    public function isStudent()
    {
        return $this->is_student;
    }

    public function isStaff()
    {
        return ! $this->isStudent();
    }

    protected function usernameIsMatric($username)
    {
        if (preg_match('/^[0-9]{7}[a-z]$/i', $username)) {
            return true;
        }
        return false;
    }

    public function notOnCourse($courseId)
    {
        if (!is_numeric($courseId)) {
            $courseId = $courseId->id;
        }
        if ($this->courses()->where('course_id', $courseId)->first()) {
            return false;
        }
        return true;
    }

    public static function findByUsername($username)
    {
        return static::where('username', '=', $username)->first();
    }
}
