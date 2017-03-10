<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function feedbacks()
    {
        return $this->belongsToMany(AssessmentFeedback::class, 'assessment_feedbacks');
    }

    public function assessmentsAsJson()
    {
        $data = [];
        foreach ($this->courses()->with('assessments.feedbacks')->get() as $course) {
            foreach ($course->assessments as $assessment) {
                $negativeFeedback = $assessment->feedbacks()->where('user_id', $this->id)->first();
                if ($negativeFeedback) {
                    $negativeFeedback = true;
                }
                $data[] = [
                    'assessment_id' => $assessment->id,
                    'course_code' => $course->code,
                    'course_title' => $course->title,
                    'deadline' => $assessment->deadline->format('Y-m-d H:i'),
                    'feedback_due' => $assessment->feedback_due->format('Y-m-d H:i'),
                    'type' => $assessment->type,
                    'feedback_missed' => $negativeFeedback,
                ];
            }
        }
        return json_encode($data);
    }

    public function recordFeedback($assessment)
    {
        if (is_numeric($assessment)) {
            $assessment = findOrFail($assessment);
        }
        $assessment->addFeedback($this);
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
}
