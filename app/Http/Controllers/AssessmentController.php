<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Assessment;

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
}
