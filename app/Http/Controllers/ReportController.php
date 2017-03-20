<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Assessment;

class ReportController extends Controller
{
    public function assessments()
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('deadline', 'desc')->get();
        return view('report.assessments', compact('assessments'));
    }

    public function feedback()
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('user_id', 'desc')->get();
        return view('report.feedback', compact('assessments'));
    }
}
