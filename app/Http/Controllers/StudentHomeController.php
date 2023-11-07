<?php

namespace App\Http\Controllers;

use Auth;

class StudentHomeController extends Controller
{
    public function index()
    {
        $assessments = Auth::user()->assessmentsAsJson();

        return view('student.home', compact('assessments'));
    }
}
