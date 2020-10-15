<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function show($id)
    {
        if (auth()->user()->is_student) {
            return redirect('/');
        }
        $student = User::findOrFail($id);

        return view('student.show', compact('student'));
    }
}
