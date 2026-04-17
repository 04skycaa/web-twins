<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to('/?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            $user = $request->user();
            $user->status_aktif = true;
            $user->save();
            event(new Verified($user));
        }

        return redirect()->to('/?verified=1')
            ->with('success_verified', 'Akun Anda telah aktif!');
    }
}
