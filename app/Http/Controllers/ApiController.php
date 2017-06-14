<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Assessment;

class ApiController extends Controller
{
    public function assessmentsAsJson()
    {
        return ['data' => Assessment::with('staff', 'course')->get()];
    }
}
