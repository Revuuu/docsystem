<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 3;
    private const LOCK_SECONDS = 30;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'User ID is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {

            RateLimiter::hit(
                $this->throttleKey(),
                self::LOCK_SECONDS
            );

            $remainingAttempts = RateLimiter::remaining(
                $this->throttleKey(),
                self::MAX_ATTEMPTS
            );

            if ($remainingAttempts <= 0) {
                event(new Lockout($this));

                throw ValidationException::withMessages([
                    'email' => 'Too many incorrect login attempts. Please try again in ' .
                        RateLimiter::availableIn($this->throttleKey()) .
                        ' seconds.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Incorrect email or password. You have ' .
                    $remainingAttempts .
                    ' attempt' .
                    ($remainingAttempts === 1 ? '' : 's') .
                    ' remaining.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Your account is temporarily locked. Please try again in ' .
                $seconds .
                ' seconds.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->input('email')) . '|' . $this->ip()
        );
    }
}