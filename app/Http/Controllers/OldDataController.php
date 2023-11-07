<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentFeedback;
use Illuminate\Http\RedirectResponse;

class OldDataController extends Controller
{
    public function destroy(): RedirectResponse
    {
        foreach (AssessmentFeedback::all() as $feedback) {
            $feedback->delete();
        }
        //AssessmentFeedback::truncate();
        foreach (Assessment::all() as $assessment) {
            $assessment->delete();
        }
        //Assessment::truncate();
        return redirect()->route('report.feedback')->with('success_message', 'Old data removed');
    }
}
