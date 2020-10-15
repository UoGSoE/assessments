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
     * in the jquery fullcalendar view.
     */
    protected function studentAssessmentsAsJson()
    {
        return $this->courses()->with('assessments.course')->get()->flatMap(function ($course) {
            return $course->assessments->map(function ($assessment) use ($course) {
                return $assessment->toEvent($course, null);
            });
        })->toJson();
    }

    /**
     * Returns a json encoded list of assessments from a staff account for use
     * in the jquery fullcalendar view.
     * Staff get to see a duplicate event for when feedback is due for a given
     * assessment (the 'feedbackEvent').
     */
    protected function staffAssessmentsAsJson()
    {
        $data = [];
        foreach (Course::active()->with('assessments.course')->get() as $course) {
            $year = $course->getYear();
            foreach ($course->assessments as $assessment) {
                $event = $assessment->toEvent($course, $year);
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
     * Create a modified Event array for staff for the feedback due deadline.
     */
    protected function getFeedbackEvent($event, $assessment)
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
        $feedbackEvent['title'] = 'Feedback Due '.$feedbackEvent['title'];
        $feedbackEvent['color'] = 'crimson';
        $feedbackEvent['textColor'] = 'white';
        $feedbackEvent['start'] = $assessment->feedback_due->toIso8601String();
        $feedbackEvent['end'] = $assessment->feedback_due->addHours(1)->toIso8601String();

        return $feedbackEvent;
    }
}
