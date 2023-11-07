<?php

namespace App\Http\Controllers;

use App\Models\Assessment;

class ApiController extends Controller
{
    public function assessmentsAsJson()
    {
        return ['data' => Assessment::with('staff', 'course')->get()];
    }
}
