<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoginOtp;
use Illuminate\Support\Facades\Auth;
use App\Mail\LoginOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{   
    public function show()
    {
        return view('auth.otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => [
                'required',
                'digits:6'
            ]
        ]);

        $otp = LoginOtp::where(
            'user_id',
            auth()->id()
        )->latest()->first();

        if (! $otp) {

            return back()
                ->withErrors([
                    'otp' => 'OTP not found.'
                ]);
        }

        if ($otp->verified_at) {

            return back()
                ->withErrors([
                    'otp' => 'OTP already used.'
                ]);
        }

        if ($otp->expires_at->isPast()) {

            return back()
                ->withErrors([
                    'otp' => 'OTP expired.'
                ]);
        }

        if (! Hash::check($request->otp, $otp->otp_code)) {

            $otp->increment('attempts');

            if ($otp->attempts >= 5) {

                Auth::logout();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Too many OTP attempts.'
                    ]);
            }

            return back()
                ->withErrors([
                    'otp' => 'Invalid OTP.'
                ]);
        }

        $otp->update([
            'verified_at' => now(),
        ]);

        session([
            'otp_verified' => true,
        ]);

        return redirect()
            ->route('dashboard');
    }
    
    public function resend()
    {
        $user = auth()->user();

        LoginOtp::where(
            'user_id',
            $user->id
        )->delete();

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
            ->send(
                new LoginOtpMail($otp)
            );

        return back()->with(
            'success',
            'OTP resent successfully.'
        );
    }
}
