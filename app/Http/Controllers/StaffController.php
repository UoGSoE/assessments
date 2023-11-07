<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function show($id): View
    {
        $staff = User::findOrFail($id);

        return view('staff.show', compact('staff'));
    }

    public function toggleAdmin(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $staff->is_admin = ! $staff->is_admin;
        $staff->save();

        return ['status' => 'ok'];
    }
}
