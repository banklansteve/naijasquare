<?php

namespace App\Signup;

use App\Mail\VerifyEmailCode;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationCodes
{
    public function issue(User $user): void
    {
        $this->guardResend($user);

        $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        EmailVerificationCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => $this->hash($plain),
            'expires_at' => now()->addMinutes((int) config('signup.code_ttl_minutes')),
        ]);

        Mail::to($user)->send(new VerifyEmailCode($plain));
    }

    public function verify(User $user, string $plain): void
    {
        $plain = preg_replace('/\D+/', '', $plain) ?? '';

        $code = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($code === null || $code->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'That code has expired. Ask for a new one.',
            ]);
        }

        $maxAttempts = (int) config('signup.code_max_attempts');

        if ($code->attempts >= $maxAttempts) {
            $code->forceFill(['used_at' => now()])->save();

            throw ValidationException::withMessages([
                'code' => 'Too many tries. Ask for a new code.',
            ]);
        }

        if (! hash_equals($code->code_hash, $this->hash($plain))) {
            $code->increment('attempts');
            $code->refresh();

            if ($code->attempts >= $maxAttempts) {
                $code->forceFill(['used_at' => now()])->save();

                throw ValidationException::withMessages([
                    'code' => 'Too many tries. Ask for a new code.',
                ]);
            }

            throw ValidationException::withMessages([
                'code' => 'That code does not match. Try again.',
            ]);
        }

        $code->forceFill(['used_at' => now()])->save();
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    private function guardResend(User $user): void
    {
        $minute = 'email-code:'.$user->id;
        $hour = 'email-code-hour:'.$user->id;

        if (RateLimiter::tooManyAttempts($hour, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many codes have been sent. Try again later.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($minute, 1)) {
            throw ValidationException::withMessages([
                'code' => 'Wait a moment before asking for another code.',
            ]);
        }

        RateLimiter::hit($minute, 60);
        RateLimiter::hit($hour, 3600);
    }

    private function hash(string $plain): string
    {
        return hash_hmac('sha256', $plain, (string) config('app.key'));
    }
}
