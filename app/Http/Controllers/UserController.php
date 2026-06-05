<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

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

        $tempPassword = Str::random(8);

        // Create the user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'role' => $request->role,
            'gender' => $request->gender,
            'email_verified_at' => null,
        ]);

        $token = Password::createToken($user);

        $setupUrl = url(
            route(
                'password.reset',
                [
                    'token' => $token,
                    'email' => $user->email,
                ],
                false
            )
        );
         
            Mail::to($user->email)
    ->send(new WelcomeUserMail($setupUrl));
        return redirect()->back()->with('success', 'User created successfully!');
    }
}