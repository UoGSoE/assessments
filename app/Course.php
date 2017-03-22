<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student', 'course_id', 'student_id');
    }

    public function staff()
    {
        return $this->belongsToMany(User::class, 'course_staff', 'course_id', 'staff_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function orderedAssessments()
    {
        return $this->assessments()->orderBy('deadline')->get();
    }

    public static function findByCode($code)
    {
        return static::where('code', '=', $code)->first();
    }

    public static function fromWlmData($wlmCourse)
    {
        $code = $wlmCourse['Code'];
        $title = $wlmCourse['Title'];
        $course = static::findByCode($code);
        if (!$course) {
            $course = new static(['code' => $code]);
        }
        $course->title = $title;
        $course->save();
        return $course;
    }
}
