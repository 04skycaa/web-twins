<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    /**
     * Verify the 6-digit OTP code against Supabase.
     */
    public function verifyOTP(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus tepat 6 digit.',
        ]);

        $user = $request->user();

        $supabase = new \App\Services\SupabaseService();
        $response = $supabase->verifyOTP($user->email, $request->otp, 'signup');

        if ($response) {
            if ($user->markEmailAsVerified()) {
                $user->status_aktif = true;
                $user->save();
                event(new Verified($user));
            }

            return redirect()->to('/')
                ->with('success', 'Akun Anda telah berhasil diverifikasi dan diaktifkan!');
        }

        return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah atau telah kedaluwarsa.']);
    }
}
