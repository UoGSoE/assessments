<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotYourCourseException;
use App\Exceptions\TooMuchTimePassedException;
use App\Exceptions\AssessmentNotOverdueException;
use Carbon\Carbon;

class Assessment extends Model
{
    protected $fillable = ['comment', 'type', 'user_id', 'course_id', 'type'];

    protected $casts = [
        'deadline' => 'datetime',
        'feedback_left' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(AssessmentFeedback::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
        if ($this->course->students()->count() == 0) {
            return 0;
        }
        if ($this->totalNegativeFeedbacks() == 0) {
            return 0;
        }
        return 100.0 / ($this->course->students()->count() / $this->totalNegativeFeedbacks());
    }

    public function getFeedbackDueAttribute()
    {
        return $this->deadline->addWeeks(3);
    }

    public function getTitleAttribute()
    {
        return $this->course->code . ' - ' . $this->type;
    }

    public function reportFeedbackLeft()
    {
        if ($this->feedback_left) {
            return $this->feedback_left->format('Y-m-d');
        }
        return 'No';
    }
    public function overdue()
    {
        if ($this->feedback_due->lt(Carbon::now())) {
            return true;
        }
        return false;
    }

    public function notOverdue()
    {
        return ! $this->overdue();
    }

    public function addFeedback($student)
    {
        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }
        if ($student->notOnCourse($this->course)) {
            throw new NotYourCourseException;
        }
        if ($this->isReallyOld()) {
            throw new TooMuchTimePassedException;
        }
        if ($this->notOverdue()) {
            throw new AssessmentNotOverdueException;
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

    public function isReallyOld()
    {
        return $this->deadline->lt(Carbon::now()->subMonths(3));
    }

    public function isProblematic()
    {
        if ($this->percentageNegativeFeedbacks() > 30) {
            return true;
        }
        return false;
    }

    public function feedbackFrom($user)
    {
        if (is_numeric($user)) {
            $user = User::findOrFail($user);
        }
        return $this->feedbacks()->where('user_id', '=', $user->id)->first();
    }

    public function updateFromForm($request)
    {
        $this->fill($request->only(['comment', 'user_id', 'type']));
        $this->deadline = Carbon::createFromFormat('d/m/Y H:i', $request->date . ' ' . $request->time);
        $this->save();
    }

    public static function createFromForm($request)
    {
        $assessment = new static;
        $assessment->fill($request->only(['comment', 'user_id', 'type', 'course_id']));
        $assessment->deadline = Carbon::createFromFormat('d/m/Y H:i', $request->date . ' ' . $request->time);
        $assessment->save();
        return $assessment;
    }

    public function deadlineDate()
    {
        if (!$this->deadline) {
            return '';
        }
        return $this->deadline->format('d/m/Y');
    }

    public function deadlineTime()
    {
        if (!$this->deadline) {
            return '16:00';
        }
        return $this->deadline->format('H:i');
    }
}
