<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\View\View;

class StudentHomeController extends Controller
{
    public function index(): View
    {
        $assessments = Auth::user()->assessmentsAsJson();

        return view('student.home', compact('assessments'));
    }
}
