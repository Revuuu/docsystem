<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $user = auth()->user();

        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('signature')) {
            if ($user->signature_path) {
                Storage::disk('private')->delete($user->signature_path);
            }

            $user->signature_path = $request->file('signature')
                ->store('signatures', 'private');
        }

        $user->save();

        return redirect()
            ->route('dashboard')
            ->with('profile_success', 'Profile updated successfully!')
            ->with('section', 'profile');
    }
}
