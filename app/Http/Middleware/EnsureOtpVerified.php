<?php

namespace App\Http\Middleware;

use Closure;

class EnsureOtpVerified
{
    public function handle($request, Closure $next)
    {
        if (! session('otp_verified')) {

            return redirect()
                ->route('otp.form');
        }

        return $next($request);
    }
}