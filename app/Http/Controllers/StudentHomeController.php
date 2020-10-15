<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class StudentHomeController extends Controller
{
    public function index()
    {
        $assessments = Auth::user()->assessmentsAsJson();

        return view('student.home', compact('assessments'));
    }
}
