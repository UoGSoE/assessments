<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title'];

    protected $hidden = ['created_at', 'updated_at'];

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
        return $this->assessments->sortBy('deadline');
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

    public static function fromTODBData($todbCourse)
    {
        $code = $todbCourse['code'];
        $course = static::findByCode($code);
        if (!$course) {
            $course = new static(['code' => $code]);
        }
        $course->title = $todbCourse['title'];
        $course->discipline = $todbCourse['discipline']['title'];
        $course->is_active = $todbCourse['is_current'];
        $course->save();
        return $course;
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

    public function getLevelAttribute()
    {
        $result = preg_match("/^[^\d]*(\d)/", $this->code, $matches);
        if ($result === 0) {
            return 'Unknown';
        }
        return $matches[1];
    }
}
