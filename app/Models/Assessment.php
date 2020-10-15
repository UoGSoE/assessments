<?php

namespace App\Models;

use App\Exceptions\AssessmentNotOverdueException;
use App\Exceptions\NotYourCourseException;
use App\Exceptions\TooMuchTimePassedException;
use App\Notifications\ProblematicAssessment;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Assessment extends Model
{
    use Notifiable;
    use HasFactory;

    protected $fillable = ['comment', 'type', 'staff_id', 'course_id', 'type', 'deadline', 'feedback_type'];

    protected $casts = [
        'deadline' => 'datetime',
        'feedback_left' => 'datetime',
    ];

    protected $hidden = ['created_at', 'updated_at', 'office_notified', 'course_id', 'staff_id'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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

    public function scopeNotSignedOff($query)
    {
        return $query->whereNull('feedback_left');
    }

    public function negativeFeedbacks()
    {
        return $this->feedbacks()->where('feedback_given', false);
    }

    public function totalNegativeFeedbacks()
    {
        return $this->negativeFeedbacks->count();
    }

    public function percentageNegativeFeedbacks()
    {
        if ($this->course->students->count() == 0) {
            return 0;
        }
        if ($this->totalNegativeFeedbacks() == 0) {
            return 0;
        }

        return 100.0 / ($this->course->students->count() / $this->totalNegativeFeedbacks());
    }

    public function getFeedbackDueAttribute()
    {
        return $this->deadline->addDays(config('assessments.feedback_grace_days'));
    }

    public function getTitleAttribute()
    {
        return $this->course->code.' - '.$this->type;
    }

    /**
     * This is just a formatter for some reports.
     */
    public function reportSignedOff()
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
        if (! $this->feedback_left) {
            return false;
        }

        return true;
    }

    public function feedbackWasGivenLate()
    {
        if ($this->notOverdue()) {
            return false;
        }
        if (! $this->feedback_left) {
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
        if ($this->hasFeedbackFrom($student)) {
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
        if (! $this->isProblematic()) {
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

    public function hasFeedbackFrom($user)
    {
        if (is_numeric($user)) {
            $user = User::findOrFail($user);
        }

        return $this->feedbacks()->where('student_id', '=', $user->id)->first();
    }

    public function updateViaForm($request)
    {
        $this->fill($request->only(['comment', 'staff_id', 'type', 'feedback_type']));
        $this->deadline = $this->stringsToCarbon($request->date, $request->time);
        $this->save();
    }

    public static function createViaForm($request)
    {
        $assessment = new static($request->only(['comment', 'staff_id', 'type', 'course_id', 'feedback_type']));
        $assessment->deadline = $assessment->stringsToCarbon($request->date, $request->time);
        $assessment->save();

        return $assessment;
    }

    protected function stringsToCarbon($date, $time)
    {
        return Carbon::createFromFormat('d/m/Y H:i', $date.' '.$time);
    }

    public function deadlineDate()
    {
        if (! $this->deadline) {
            return '';
        }

        return $this->deadline->format('d/m/Y');
    }

    public function deadlineTime()
    {
        if (! $this->deadline) {
            return '16:00';
        }

        return $this->deadline->format('H:i');
    }

    /**
     * Used for generating the JSON encoded data for the javascript calendar.
     */
    public function toEvent($course = null, $year = false)
    {
        if (! $course) {
            $course = $this->course;
        }
        $event = [
            'id' => $this->id,
            'title' => $this->title,
            'course_code' => $course->code,
            'course_title' => $course->title,
            'start' => $this->deadline->toIso8601String(),
            'end' => $this->deadline->addHours(1)->toIso8601String(),
            'feedback_due' => $this->feedback_due->toIso8601String(),
            'type' => $this->type,
            'mine' => true,
            'discipline' => $course->discipline,
            'color' => 'whitesmoke',
            'textColor' => 'black',
            'url' => route('assessment.show', $this->id),
        ];
        if ($year) {
            $event['year'] = $year;
        }
        if (auth()->id() == $this->staff_id) {
            $event['color'] = 'steelblue';
            $event['textColor'] = 'white';
        }

        return $event;
    }

    /**
     * Gets a list of unique feedback types - used to populate the type-ahead when
     * admins are manually create/editing an assessment.
     */
    public static function getFeedbackTypes()
    {
        return static::select('feedback_type')->distinct()->orderBy('feedback_type')->get()->pluck('feedback_type');
    }
}
