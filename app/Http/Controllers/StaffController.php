<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function show($id)
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
