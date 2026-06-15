<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\LoginOtp;
use App\Mail\LoginOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        $trustedToken = $request->cookie('trusted_device');

        if ($trustedToken) {

            $trustedDevice = TrustedDevice::where(
                'user_id',
                $user->id
            )
            ->where(
                'token_hash',
                hash('sha256', $trustedToken)
            )
            ->where(
                'expires_at',
                '>',
                now()
            )
            ->first();

            if ($trustedDevice) {

                $trustedDevice->update([
                    'last_used_at' => now(),
                ]);

                session([
                    'otp_verified' => true,
                ]);

                return redirect()
                    ->route('dashboard');
            }
        }

        if (! $user->hasVerifiedEmail()) {

            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Your email address has not been verified. Contact the administrator.',
                ]);
        }

        LoginOtp::where('user_id', $user->id)
            ->delete();

        $otp = str_pad(
            random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        LoginOtp::create([
            'user_id' => $user->id,
            'otp_code' => Hash::make($otp),
            'expires_at' => now()->addMinutes(3),
        ]);

        Mail::to($user->email)
            ->send(new LoginOtpMail($otp));

        session([
            'otp_verified' => false,
        ]);

        session([
            'remember_device' =>
                $request->boolean('remember_device')
        ]);
        return redirect()->route('otp.form');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->forget(
            'otp_verified'
        );
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
