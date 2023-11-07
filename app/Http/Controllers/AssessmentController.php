<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function show($id): View
    {
        $assessment = Assessment::findOrFail($id);

        return view('assessment.show', compact('assessment'));
    }

    public function create(): View
    {
        $assessment = new Assessment(['deadline' => Carbon::now()->hour(16)->minute(0)]);
        $staff = User::staff()->orderBy('surname')->get();
        $courses = Course::orderBy('code')->get();
        $feedbackTypes = Assessment::getFeedbackTypes();

        return view('assessment.create', compact('assessment', 'staff', 'courses', 'feedbackTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'staff_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i',
            'type' => 'required',
            'course_id' => 'required|integer|exists:courses,id',
        ]);
        $assessment = Assessment::createViaForm($request);

        return redirect()->route('assessment.show', $assessment->id)->with('success_message', 'Created');
    }

    public function edit($id): View
    {
        $assessment = Assessment::findOrFail($id);
        $staff = User::staff()->orderBy('surname')->get();
        $feedbackTypes = Assessment::getFeedbackTypes();

        return view('assessment.edit', compact('assessment', 'staff', 'feedbackTypes'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'staff_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i',
        ]);
        $assessment = Assessment::findOrFail($id);
        $assessment->updateViaForm($request);

        return redirect()->route('assessment.show', $id)->with('success_message', 'Updated');
    }

    public function destroy($id): RedirectResponse
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->feedbacks()->get()->each->delete();
        $assessment->delete();

        return redirect()->route('report.feedback')->with('success_message', 'Assessment deleted');
    }
}
