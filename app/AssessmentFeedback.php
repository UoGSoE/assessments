<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssessmentFeedback extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'assessment_id', 'feedback_given'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class);
    }
}
