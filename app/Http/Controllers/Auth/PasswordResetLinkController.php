<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // Verify the user exists in our local database
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Alamat email tidak terdaftar di sistem.']);
        }

        // Trigger Supabase recovery OTP email
        $supabase = new SupabaseService();
        $success = $supabase->sendRecoveryEmail($request->email);

        if ($success) {
            return redirect()->route('password.verify-otp-view', ['email' => $request->email])
                ->with('status', 'Kode verifikasi telah dikirim ke email Anda.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Gagal mengirimkan kode verifikasi. Silakan coba lagi nanti.']);
    }

    /**
     * Display the OTP verification form for recovery.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Masukkan email Anda terlebih dahulu.']);
        }

        return view('auth.verify-recovery-otp', ['email' => $email]);
    }

    /**
     * Verify the recovery OTP code.
     */
    public function verifyOTP(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus tepat 6 digit.',
        ]);

        $supabase = new SupabaseService();
        $response = $supabase->verifyOTP($request->email, $request->otp, 'recovery');

        if ($response) {
            // Store reset verification status in session
            session([
                'reset_email' => $request->email,
                'reset_token' => $request->otp,
                'reset_verified' => true
            ]);

            return redirect()->route('password.reset', [
                'token' => $request->otp,
                'email' => $request->email
            ]);
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali email Anda.']);
    }
}
