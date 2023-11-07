<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Auth;

class StudentHomeController extends Controller
{
    public function index(): View
    {
        $assessments = Auth::user()->assessmentsAsJson();

        return view('student.home', compact('assessments'));
    }
}
