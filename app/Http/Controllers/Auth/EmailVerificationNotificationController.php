<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        $supabase = new \App\Services\SupabaseService();
        $success = $supabase->resendSignupOTP($request->user()->email);

        if ($success) {
            return back()->with('status', 'verification-link-sent');
        }

        return back()->withErrors(['error' => 'Gagal mengirim ulang kode OTP. Silakan coba beberapa saat lagi.']);
    }
}
