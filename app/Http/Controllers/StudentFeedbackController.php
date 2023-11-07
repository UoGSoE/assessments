<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentFeedbackController extends Controller
{
    public function store(Request $request, $assessmentId): RedirectResponse
    {
        $assessment = Assessment::findOrFail($assessmentId);
        if (Gate::denies('leave_feedback', $assessment)) {
            return redirect('/home');
        }
        $request->user()->recordFeedback($assessment);

        return redirect('/home')->with('success_message', 'Feedback recorded');
    }
}
