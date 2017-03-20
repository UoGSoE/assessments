<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Assessment;
use App\AssessmentFeedback;

class OldDataController extends Controller
{
    public function destroy()
    {
        AssessmentFeedback::truncate();
        Assessment::truncate();
        return redirect()->route('report.feedback')->with('success_message', 'Old data removed');
    }
}
