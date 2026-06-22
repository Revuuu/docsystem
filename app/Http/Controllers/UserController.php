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

        $user->addRole($request->role);

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
         
        Mail::to($user->email)->send(new WelcomeUserMail($setupUrl));
        
        return redirect()->route(
        'dashboard', 
        ['section' => 'users'])
        ->with('role_success', 'User created successfully.');
    }

    public function resetPassword(User $user)
    {
        $token = Password::createToken($user);

        $resetUrl = url(
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
            ->send(new WelcomeUserMail($resetUrl));

        return redirect()
            ->route('dashboard', [
                'section' => 'user-management'
            ])
            ->with(
                'user_success',
                'Password reset email sent successfully.'
            );
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {

            return redirect()
                ->back()
                ->with(
                    'role_error',
                    'You cannot delete your own account.'
                );
        }

        $user->syncRoles([]);

        $user->delete();

    return redirect()
        ->route('dashboard', [
            'section' => 'user-management'
        ])
        ->with('user_success', 'User deleted successfully.');
    }
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:staff,supervisor,depthead,division,executive,admin',
            'gender' => 'nullable|in:male,female',
        ]);

        $oldRole = $user->roles->first()?->name;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'gender' => $request->gender,
        ]);

        if ($oldRole !== $request->role) {
            $user->syncRoles([$request->role]);
        }

    return redirect()
        ->route('dashboard', [
            'section' => 'user-management'
        ])
        ->with('user_success', 'User updated successfully.');
    }

    public function unverify(User $user)
    {
        $user->forceFill([
            'email_verified_at' => null,
        ])->save();

        return redirect()
            ->route('dashboard', ['section' => 'user-management'])
            ->with('user_success', 'User email has been marked as unverified.');
    }
}