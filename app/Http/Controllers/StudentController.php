<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class StudentController extends Controller
{
    public function show($id)
    {
        $student = User::findOrFail($id);
        return view('student.show', compact('student'));
    }
}
