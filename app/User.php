<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\OverdueFeedback;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'surname', 'forenames', 'is_student'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function courses()
    {
        if ($this->is_student) {
            return $this->belongsToMany(Course::class, 'course_student');
        }
        return $this->belongsToMany(Course::class, 'course_staff');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class);
    }

    public function unreadFeedbacks()
    {
        $feedbacks = [];
        foreach ($this->assessments()->with('course', 'feedbacks')->get() as $assessment) {
            foreach ($assessment->negativeFeedbacks as $feedback) {
                if ($feedback->isUnread()) {
                    $feedbacks[] = $feedback;
                }
            }
        }
        return collect($feedbacks);
    }

    public function getMatricAttribute()
    {
        return preg_replace('/[^0-9]/', '', $this->username);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_student', '=', false);
    }

    public function fullName()
    {
        return $this->surname . ', ' . $this->forenames;
    }

    public function isStaff()
    {
        return ! $this->is_student;
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
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'course_code' => $course->code,
                    'course_title' => $course->title,
                    'start' => $assessment->deadline->toIso8601String(),
                    'end' => $assessment->deadline->addHours(1)->toIso8601String(),
                    'feedback_due' => $assessment->feedback_due->toIso8601String(),
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

    public function markAllFeedbacksAsRead($feedbacks = [])
    {
        if (count($feedbacks) == 0) {
            $feedbacks = $this->unreadFeedbacks();
        }
        foreach ($feedbacks as $feedback) {
            $feedback->markAsRead();
        }
    }

    public function notifyAboutUnreadFeedback()
    {
        $unread = $this->unreadFeedbacks();
        if ($unread->count() == 0) {
            return;
        }
        $this->notify(new OverdueFeedback($unread));
        $this->markAllFeedbacksAsRead($unread);
    }

    public function hasLeftFeedbacks()
    {
        if ($this->feedbacks()->count() == 0) {
            return false;
        }
        return true;
    }
}