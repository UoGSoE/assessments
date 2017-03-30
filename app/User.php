<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\OverdueFeedback;
use App\Course;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'surname', 'forenames', 'is_student'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean'
    ];

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
        $cutoff = Carbon::now()->subDays(config('assessments.feedback_grace_days'));
        ;
        return count($this->assessments()->where('deadline', '<=', $cutoff)
                    ->get()
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

    public function assessmentsAsJson()
    {
        if ($this->isStaff()) {
            return $this->staffAssessmentsAsJson();
        }
        return $this->studentAssessmentsAsJson();
    }

    /**
     * Returns a json encoded list of assessments from a student account for use
     * in the jquery fullcalendar view
     */
    protected function studentAssessmentsAsJson()
    {
        return $this->courses()->with('assessments.feedbacks')->get()->flatMap(function ($course) {
            return $course->assessments->map(function ($assessment) use ($course) {
                return $this->getEvent($assessment, $course, false);
            });
        })->toJson();
    }

    /**
     * Rreturns a json encoded list of assessments from a staff account for use
     * in the jquery fullcalendar view.
     * Staff get to see a duplicate event for when feedback is due for a given
     * assessment (the 'feedbackEvent').
     */
    protected function staffAssessmentsAsJson()
    {
        $data = [];
        foreach (Course::with('assessments.feedbacks')->get() as $course) {
            $year = $course->getYear();
            foreach ($course->assessments as $assessment) {
                $event = $this->getEvent($assessment, $course, $year);
                $feedbackEvent = $this->getFeedbackEvent($event, $assessment);
                if ($feedbackEvent) {
                    $data[] = $feedbackEvent;
                }
                $data[] = $event;
            }
        }
        return json_encode($data);
    }

    /**
     * Generic transform of an assessment to an array for json encoding.
     */
    public function getEvent($assessment, $course, $year)
    {
        $event = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'course_code' => $course->code,
            'course_title' => $course->title,
            'start' => $assessment->deadline->toIso8601String(),
            'end' => $assessment->deadline->addHours(1)->toIso8601String(),
            'feedback_due' => $assessment->feedback_due->toIso8601String(),
            'type' => $assessment->type,
            'mine' => $this->can('see_assessment', $assessment),
            'color' => 'steelblue',
        ];
        if ($year) {
            $event['year'] = $year;
        }
        if ($this->cannot('see_assessment', $assessment)) {
            $event['color'] = 'whitesmoke';
            $event['textColor'] = 'black';
        }
        return $event;
    }

    /**
     * Create a modified Event array for staff for the feedback due deadline
     */
    public function getFeedbackEvent($event, $assessment)
    {
        if ($this->is_admin) {
            return false;
        }
        if ($assessment->feedback_left) {
            return false;
        }
        if ($this->cannot('see_assessment', $assessment)) {
            return false;
        }
        $feedbackEvent = $event;
        $feedbackEvent['title'] = 'Feedback Due ' . $feedbackEvent['title'];
        $feedbackEvent['color'] = 'crimson';
        $feedbackEvent['textColor'] = 'white';
        $feedbackEvent['start'] = $assessment->feedback_due->toIso8601String();
        $feedbackEvent['end'] = $assessment->feedback_due->addHours(1)->toIso8601String();
        return $feedbackEvent;
    }

    public function recordFeedback($assessment)
    {
        if (is_numeric($assessment)) {
            $assessment = findOrFail($assessment);
        }
        $assessment->addFeedback($this);
    }

    public function hasLeftFeedbackFor($assessment)
    {
        $feedback = $this->feedbacks()->where('assessment_id', '=', $assessment->id)->first();
        if ($feedback) {
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

    public function markAllFeedbacksAsNotified($feedbacks = [])
    {
        if (count($feedbacks) == 0) {
            $feedbacks = $this->newFeedbacks();
        }
        foreach ($feedbacks as $feedback) {
            $feedback->markAsNotified();
        }
    }

    public function notifyAboutNewFeedback()
    {
        $newFeedbacks = $this->newFeedbacks();
        if ($newFeedbacks->count() == 0) {
            return;
        }
        $this->notify(new OverdueFeedback($newFeedbacks));
        $this->markAllFeedbacksAsNotified($newFeedbacks);
    }

    public function hasLeftFeedbacks()
    {
        if ($this->feedbacks()->count() == 0) {
            return false;
        }
        return true;
    }

    protected function usernameIsMatric($username)
    {
        if (preg_match('/^[0-9]{7}[a-z]$/i', $username)) {
            return true;
        }
        return false;
    }

    public static function findByUsername($username)
    {
        return static::where('username', '=', $username)->first();
    }

    public static function createFromLdap($ldapData)
    {
        $user = new static([
            'username' => $ldapData['username'],
            'surname' => $ldapData['surname'],
            'forenames' => $ldapData['forenames'],
            'email' => $ldapData['email'],
            'password' => bcrypt(str_random(64))
        ]);
        $user->is_student = $user->usernameIsMatric($ldapData['username']);
        $user->save();
        return $user;
    }

    public static function staffFromWlmData($wlmStaff)
    {
        $wlmStaff['Username'] = $wlmStaff['GUID'];
        return static::userFromWlmData($wlmStaff, false);
    }

    public static function studentFromWlmData($wlmStudent)
    {
        $wlmStudent['Username'] = strtolower($wlmStudent['Matric'] . substr($wlmStudent['Surname'], 0, 1));
        $wlmStudent['Email'] = $wlmStudent['Username'] . "@student.gla.ac.uk";
        return static::userFromWlmData($wlmStudent, true);
    }

    protected static function userFromWlmData($wlmData, $isStudent = false)
    {
        $user = User::findByUsername($wlmData['Username']);
        if (!$user) {
            $user = new static([
                'username' => $wlmData['Username'],
                'email' => $wlmData['Email'],
            ]);
        }
        $user->surname = $wlmData['Surname'] ?? 'Unknown';
        $user->forenames = $wlmData['Forenames'] ?? 'Unknown';
        $user->password = bcrypt(str_random(32));
        $user->is_student = $isStudent;
        $user->save();
        return $user;
    }
}
