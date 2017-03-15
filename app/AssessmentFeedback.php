<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssessmentFeedback extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'assessment_id', 'feedback_given'
    ];

    protected $casts = [
        'staff_notified' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function isUnread()
    {
        return ! $this->staff_notified;
    }

    public function markAsRead()
    {
        $this->staff_notified = true;
        $this->save();
    }
}