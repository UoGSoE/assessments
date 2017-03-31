<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotYourCourseException;
use App\Exceptions\TooMuchTimePassedException;
use App\Exceptions\AssessmentNotOverdueException;
use App\Notifications\ProblematicAssessment;
use Carbon\Carbon;

class Assessment extends Model
{
    use Notifiable;

    protected $fillable = ['comment', 'type', 'staff_id', 'course_id', 'type', 'deadline'];

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

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function scopeNoAcademicFeedback($query)
    {
        return $query->whereNull('feedback_left');
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
        return $this->deadline->addDays(config('assessments.feedback_grace_days'));
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

    public function canBeAutoSignedOff()
    {
        if ($this->notOverdue()) {
            return false;
        }
        if ($this->feedbackWasGiven()) {
            return false;
        }
        if ($this->totalNegativeFeedbacks() > 0) {
            return false;
        }
        if ($this->feedback_due->addDays(21)->gte(Carbon::now())) {
            return false;
        }
        return true;
    }

    public function autoSignOff()
    {
        $this->feedback_left = $this->feedback_due;
        $this->save();
    }

    public function feedbackWasGiven()
    {
        if (!$this->feedback_left) {
            return false;
        }
        return true;
    }

    public function feedbackWasGivenLate()
    {
        if ($this->notOverdue()) {
            return false;
        }
        if (!$this->feedback_left) {
            return true;
        }
        return $this->feedback_left->gt($this->feedback_due);
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
        $feedback = $this->feedbacks()->where('student_id', $student->id)->first();
        if ($feedback) {
            return;
        }
        $feedback = new AssessmentFeedback;
        $feedback->course_id = $this->course->id;
        $feedback->student_id = $student->id;
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

    public function notifyIfProblematic()
    {
        if ($this->officeHaveBeenNotified()) {
            return false;
        }
        if (!$this->isProblematic()) {
            return false;
        }
        $this->notify(new ProblematicAssessment($this));
        $this->markOfficeNotified();
        return true;
    }

    public function markOfficeNotified()
    {
        $this->office_notified = true;
        $this->save();
    }

    public function officeHaveBeenNotified()
    {
        return (bool) $this->office_notified;
    }

    public function routeNotificationForMail()
    {
        return config('assessments.office_email');
    }

    public function feedbackFrom($user)
    {
        if (is_numeric($user)) {
            $user = User::findOrFail($user);
        }
        return $this->feedbacks()->where('student_id', '=', $user->id)->first();
    }

    public function updateFromForm($request)
    {
        $this->fill($request->only(['comment', 'staff_id', 'type']));
        $this->deadline = Carbon::createFromFormat('d/m/Y H:i', $request->date . ' ' . $request->time);
        $this->save();
    }

    public static function createFromForm($request)
    {
        $assessment = new static;
        $assessment->fill($request->only(['comment', 'staff_id', 'type', 'course_id']));
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
