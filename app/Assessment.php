<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $casts = [
        'deadline' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class);
    }
}
