<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotYourCourseException;

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

    public function negativeFeedbacks()
    {
        return $this->feedbacks()->where('feedback_given', false);
    }

    public function totalNegativeFeedbacks()
    {
        return $this->negativeFeedbacks()->count();
    }

    public function percentageNegativeFeedbacks()
    {
        return 100.0 / ($this->course->students()->count() / $this->totalNegativeFeedbacks());
    }

    public function getFeedbackDueAttribute()
    {
        return $this->deadline->addWeeks(3);
    }

    public function addFeedback($student)
    {
        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }
        if ($student->notOnCourse($this->course)) {
            throw new NotYourCourseException;
        }
        $feedback = $this->feedbacks()->where('user_id', $student->id)->first();
        if (!$feedback) {
            $feedback = new AssessmentFeedback;
        }
        $feedback->course_id = $this->course->id;
        $feedback->user_id = $student->id;
        $feedback->feedback_given = false;
        $feedback->assessment_id = $this->id;
        $feedback->save();
    }
}
