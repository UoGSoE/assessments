<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Assessment;
use App\AssessmentFeedback;

class OldDataController extends Controller
{
    public function destroy()
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
