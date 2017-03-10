<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssessmentFeedback extends Model
{
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class);
    }
}
