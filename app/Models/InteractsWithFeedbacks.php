<?php

namespace App\Models;

use App\Notifications\OverdueFeedback;

trait InteractsWithFeedbacks
{
    /**
     * Records a student leaving feedback about an assessment.
     */
    public function recordFeedback($assessment)
    {
        if (is_numeric($assessment)) {
            $assessment = findOrFail($assessment);
        }
        $assessment->addFeedback($this);
    }

    /**
     * Check if a student has left feedback for an assessment.
     */
    public function hasLeftFeedbackFor($assessment)
    {
        $feedback = $this->feedbacks()->where('assessment_id', '=', $assessment->id)->first();
        if ($feedback) {
            return true;
        }

        return false;
    }

    /**
     * Sends a member of staff a notification if they have any new feedback
     * left by students.
     */
    public function notifyAboutNewFeedback()
    {
        $newFeedbacks = $this->newFeedbacks();
        if ($newFeedbacks->count() == 0) {
            return;
        }
        $this->notify(new OverdueFeedback($newFeedbacks));
        $this->markAllFeedbacksAsNotified($newFeedbacks);
    }

    /**
     * Loops over all the new feedback for a staff-member and marks them as
     * having been sent a notification.
     * If it's called with an empty list it will try and find the new feedbacks
     * itself.
     */
    public function markAllFeedbacksAsNotified($feedbacks = [])
    {
        if (! $feedbacks instanceof \Illuminate\Support\Collection) {
            $feedbacks = collect($feedbacks);
        }
        if (count($feedbacks) == 0) {
            $feedbacks = $this->newFeedbacks();
        }
        $feedbacks->each->markAsNotified();
    }

    /**
     * Checks if a student has left any feedback at all (just used in some reports).
     */
    public function hasLeftFeedbacks()
    {
        if ($this->feedbacks()->count() == 0) {
            return false;
        }

        return true;
    }
}
