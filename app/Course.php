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

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
        $course->is_active = $course->getWlmStatus($wlmCourse);
        $course->title = $title;
        $course->save();
        return $course;
    }

    protected function getWlmStatus($wlmCourse)
    {
        if (!array_key_exists('CurrentFlag', $wlmCourse)) {
            return false;
        }
        if ($wlmCourse['CurrentFlag'] === 'Yes') {
            return true;
        }
        return false;
    }

    public function getYear()
    {
        if (!preg_match('/[0-9]/', $this->code, $match)) {
            return false;
        }
        return $match[0];
    }

    public function isActive()
    {
        return !! $this->is_active;
    }
}
