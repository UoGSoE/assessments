<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student');
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'course_assessment');
    }
}
