<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Assessment;

class StudentFeedbackController extends Controller
{
    public function store(Request $request, $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);
        if (Gate::denies('leave_feedback', $assessment)) {
            return redirect('/home');
        }
        $request->user()->recordFeedback($assessment);
        return redirect('/home')->with('success_message', 'Feedback recorded');
    }
}
