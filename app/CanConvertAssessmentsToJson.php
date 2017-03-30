<?php

namespace App;

trait CanConvertAssessmentsToJson
{
    public function assessmentsAsJson()
    {
        if ($this->isStaff()) {
            return $this->staffAssessmentsAsJson();
        }
        return $this->studentAssessmentsAsJson();
    }

    /**
     * Returns a json encoded list of assessments from a student account for use
     * in the jquery fullcalendar view
     */
    protected function studentAssessmentsAsJson()
    {
        return $this->courses()->with('assessments.feedbacks')->get()->flatMap(function ($course) {
            return $course->assessments->map(function ($assessment) use ($course) {
                return $this->getEvent($assessment, $course, false);
            });
        })->toJson();
    }

    /**
     * Rreturns a json encoded list of assessments from a staff account for use
     * in the jquery fullcalendar view.
     * Staff get to see a duplicate event for when feedback is due for a given
     * assessment (the 'feedbackEvent').
     */
    protected function staffAssessmentsAsJson()
    {
        $data = [];
        foreach (Course::with('assessments.feedbacks')->get() as $course) {
            $year = $course->getYear();
            foreach ($course->assessments as $assessment) {
                $event = $this->getEvent($assessment, $course, $year);
                $feedbackEvent = $this->getFeedbackEvent($event, $assessment);
                if ($feedbackEvent) {
                    $data[] = $feedbackEvent;
                }
                $data[] = $event;
            }
        }
        return json_encode($data);
    }

    /**
     * Generic transform of an assessment to an array for json encoding.
     */
    public function getEvent($assessment, $course, $year)
    {
        $event = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'course_code' => $course->code,
            'course_title' => $course->title,
            'start' => $assessment->deadline->toIso8601String(),
            'end' => $assessment->deadline->addHours(1)->toIso8601String(),
            'feedback_due' => $assessment->feedback_due->toIso8601String(),
            'type' => $assessment->type,
            'mine' => $this->can('see_assessment', $assessment),
            'color' => 'steelblue',
        ];
        if ($year) {
            $event['year'] = $year;
        }
        if ($this->cannot('see_assessment', $assessment)) {
            $event['color'] = 'whitesmoke';
            $event['textColor'] = 'black';
        }
        return $event;
    }

    /**
     * Create a modified Event array for staff for the feedback due deadline
     */
    public function getFeedbackEvent($event, $assessment)
    {
        if ($this->is_admin) {
            return false;
        }
        if ($assessment->feedback_left) {
            return false;
        }
        if ($this->cannot('see_assessment', $assessment)) {
            return false;
        }
        $feedbackEvent = $event;
        $feedbackEvent['title'] = 'Feedback Due ' . $feedbackEvent['title'];
        $feedbackEvent['color'] = 'crimson';
        $feedbackEvent['textColor'] = 'white';
        $feedbackEvent['start'] = $assessment->feedback_due->toIso8601String();
        $feedbackEvent['end'] = $assessment->feedback_due->addHours(1)->toIso8601String();
        return $feedbackEvent;
    }
}
