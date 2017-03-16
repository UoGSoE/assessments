<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Assessment;
use App\User;

class AssessmentController extends Controller
{
    public function show($id)
    {
        $assessment = Assessment::findOrFail($id);
        if (Gate::denies('can_see_assessment', $assessment)) {
            return redirect('/');
        }
        return view('assessment.show', compact('assessment'));
    }

    public function edit($id)
    {
        $assessment = Assessment::findOrFail($id);
        $staff = User::staff()->orderBy('surname')->get();
        return view('assessment.edit', compact('assessment', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i',
        ]);
        $assessment = Assessment::findOrFail($id);
        $assessment->updateFromForm($request);
        return redirect()->route('assessment.show', $id)->with('success_message', 'Updated');
    }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->feedbacks()->get()->each->delete();
        $assessment->delete();
        return redirect()->route('report.assessments')->with('success_message', 'Assessment deleted');
    }
}
