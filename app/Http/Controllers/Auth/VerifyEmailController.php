<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail(
            $request->route('id')
        );

        if (! $user->hasVerifiedEmail()) {

            $user->markEmailAsVerified();

            event(
                new Verified($user)
            );
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Email verified successfully. You may now sign in.'
            );
    }
}