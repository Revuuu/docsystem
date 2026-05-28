<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'required|in:staff,supervisor,depthead,division,executive',
        ]);

        // Concatenate full name 
        $fullName = trim( 
            $request->first_name . ' ' . 
            ($request->middle_name 
            ? $request->middle_name . ' '
             : '') . 
             $request->last_name 
        );

        // Create the user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gender' => $request->gender,
        ]);

        return redirect()->back()->with('success', 'User created successfully!');
    }
}