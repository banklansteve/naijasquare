<?php

namespace App\Signup;

use App\Models\User;
use Illuminate\Http\Request;

class CapturePersonalInvite
{
    public const SESSION_KEY = 'signup.invited_by_uid';

    public function captureFromRequest(Request $request): void
    {
        $uid = $request->query('invited_by');

        if (! is_string($uid) || $uid === '') {
            return;
        }

        $uid = strtoupper(trim($uid));

        if (! preg_match('/^[A-Z0-9]{8}$/', $uid)) {
            return;
        }

        if (! User::query()->where('uid', $uid)->exists()) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $uid);
    }

    public function inviterIdFromSession(Request $request): ?int
    {
        $uid = $request->session()->get(self::SESSION_KEY);

        if (! is_string($uid) || $uid === '') {
            return null;
        }

        $inviterId = User::query()->where('uid', $uid)->value('id');

        return is_int($inviterId) || is_numeric($inviterId) ? (int) $inviterId : null;
    }

    public function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
