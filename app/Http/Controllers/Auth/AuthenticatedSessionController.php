<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\LoginOtp;
use App\Mail\LoginOtpMail;
use Illuminate\Support\Facades\Mail;

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
    'otp_code' => $otp,
    'expires_at' => now()->addMinutes(3),
]);

Mail::to($user->email)
    ->send(new LoginOtpMail($otp));

session([
    'otp_verified' => false,
]);

return redirect()->route('otp.form');
    }

    /**
     * Destroy an authenticated session.
     */
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
