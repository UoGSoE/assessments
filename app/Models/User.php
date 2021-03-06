<?php

namespace App\Models;

use App\Models\Course;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Storage;

class User extends Authenticatable
{
    use Notifiable;
    use CanConvertAssessmentsToJson;
    use CanBeCreatedFromOutsideSources;
    use InteractsWithFeedbacks;
    use HasFactory;

    protected $fillable = [
        'username', 'email', 'password', 'surname', 'forenames', 'is_student',
    ];

    protected $hidden = [
        'password', 'remember_token', 'is_admin', 'is_student', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function courses()
    {
        if ($this->is_student) {
            return $this->belongsToMany(Course::class, 'course_student', 'student_id')->active();
        }

        return $this->belongsToMany(Course::class, 'course_staff', 'staff_id')->active();
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
        return (int) $this->assessments->count();
    }

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class, 'student_id');
    }

    public function assessmentsWithFeedbacks()
    {
        return $this->assessments()->has('feedbacks');
    }

    public function getAssessmentsWithStudentFeedback()
    {
        return $this->assessmentsWithFeedbacks;
    }

    public function totalStudentFeedbacks()
    {
        return $this->getAssessmentsWithStudentFeedback()
                    ->reduce(function ($carry, $assessment) {
                        return $carry + $assessment->totalNegativeFeedbacks();
                    }, 0);
    }

    public function numberOfMissedDeadlines()
    {
        return count($this->assessmentsWhereFeedbacksDue
                    ->filter
                    ->feedbackWasGivenLate());
    }

    /**
     * Used to build a list of student feedbacks where the member of staff has not been
     * notified/emailed.
     */
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
        return $this->surname.', '.$this->forenames;
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
        if (! is_numeric($courseId)) {
            $courseId = $courseId->id;
        }
        if ($this->courses->where('id', $courseId)->first()) {
            return false;
        }

        return true;
    }

    public static function findByUsername($username)
    {
        return static::where('username', '=', $username)->first();
    }

    public static function findByEmail($email)
    {
        return static::where('email', '=', $email)->first();
    }

    /**
     * Used to generate a unique filename for the users ical file.
     */
    public function getUuid()
    {
        $hasher = new Hashids(config('assessments.hash_seed'), 10);

        return $this->username.'_'.$hasher->encode($this->id);
    }

    public function icsPath()
    {
        return "eng/{$this->getUuid()}.ics";
    }

    public function icsUrl()
    {
        return url('calendars/'.$this->icsPath());
    }
}
