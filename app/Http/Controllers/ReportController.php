<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function assessments()
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('deadline', 'desc')->get();

        return view('report.assessments', compact('assessments'));
    }

    public function feedback()
    {
        $assessments = Assessment::with('course.students', 'feedbacks', 'negativeFeedbacks', 'staff')->orderBy('staff_id')->get();

        return view('report.feedback', compact('assessments'));
    }

    public function staff()
    {
        $staff = User::staff()->with('assessments.feedbacks', 'assessmentsWithFeedbacks', 'assessmentsWhereFeedbacksDue')->orderBy('surname')->get();

        return view('report.staff', compact('staff'));
    }
}
