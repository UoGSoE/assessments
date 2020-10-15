<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StaffFeedbackController extends Controller
{
    public function store(Request $request, $id)
    {
        $this->validate($request, ['date' => 'required|date_format:d/m/Y']);
        $assessment = Assessment::findOrFail($id);
        if (Gate::denies('complete_feedback', $assessment)) {
            return redirect()->back()->withErrors(['feedback' => 'Already marked as complete']);
        }
        $assessment->feedback_left = Carbon::createFromFormat('d/m/Y', $request->date);
        $assessment->save();

        return redirect()->route('assessment.show', $id)->with('success_message', 'Feedback marked as complete');
    }
}
