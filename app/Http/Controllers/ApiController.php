<?php

namespace App\Http\Controllers;

use App\Assessment;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function assessmentsAsJson()
    {
        return ['data' => Assessment::with('staff', 'course')->get()];
    }
}
