<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student');
    }

    public function staff()
    {
        return $this->belongsToMany(User::class, 'course_staff');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
