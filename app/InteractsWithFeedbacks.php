<?php

namespace App;

use App\Notifications\OverdueFeedback;

trait InteractsWithFeedbacks
{
    public function recordFeedback($assessment)
    {
        if (is_numeric($assessment)) {
            $assessment = findOrFail($assessment);
        }
        $assessment->addFeedback($this);
    }

    public function hasLeftFeedbackFor($assessment)
    {
        $feedback = $this->feedbacks()->where('assessment_id', '=', $assessment->id)->first();
        if ($feedback) {
            return true;
        }
        return false;
    }

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

    public function notifyAboutNewFeedback()
    {
        $newFeedbacks = $this->newFeedbacks();
        if ($newFeedbacks->count() == 0) {
            return;
        }
        $this->notify(new OverdueFeedback($newFeedbacks));
        $this->markAllFeedbacksAsNotified($newFeedbacks);
    }

    public function hasLeftFeedbacks()
    {
        if ($this->feedbacks()->count() == 0) {
            return false;
        }
        return true;
    }
}
