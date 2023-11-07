<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Assessment;
use App\Models\User;

class ReportController extends Controller
{
    public function assessments(): View
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('deadline', 'desc')->get();

        return view('report.assessments', compact('assessments'));
    }

    public function feedback(): View
    {
        $assessments = Assessment::with('course.students', 'feedbacks', 'negativeFeedbacks', 'staff')->orderBy('staff_id')->get();

        return view('report.feedback', compact('assessments'));
    }

    public function staff(): View
    {
        $staff = User::staff()->with('assessments.feedbacks', 'assessmentsWithFeedbacks', 'assessmentsWhereFeedbacksDue')->orderBy('surname')->get();

        return view('report.staff', compact('staff'));
    }
}
