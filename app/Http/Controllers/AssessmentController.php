<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Assessment;
use App\User;
use App\Course;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    public function show($id)
    {
        $assessment = Assessment::findOrFail($id);
        if (Gate::denies('see_assessment', $assessment)) {
            return redirect('/');
        }
        return view('assessment.show', compact('assessment'));
    }

    public function create()
    {
        $assessment = new Assessment(['deadline' => Carbon::now()->hour(16)->minute(0)]);
        $staff = User::staff()->orderBy('surname')->get();
        $courses = Course::orderBy('code')->get();
        return view('assessment.create', compact('assessment', 'staff', 'courses'));
    }

    public function store(Request $request)
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

    public function edit($id)
    {
        $assessment = Assessment::findOrFail($id);
        $staff = User::staff()->orderBy('surname')->get();
        return view('assessment.edit', compact('assessment', 'staff'));
    }

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->feedbacks()->get()->each->delete();
        $assessment->delete();
        return redirect()->route('report.feedback')->with('success_message', 'Assessment deleted');
    }
}
