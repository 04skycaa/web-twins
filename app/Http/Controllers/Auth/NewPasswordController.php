<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Token reset password tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Verify that this email and token have been verified via OTP
        if (!session('reset_verified') || session('reset_email') !== $request->email || session('reset_token') !== $request->token) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi reset password tidak valid atau telah kedaluwarsa. Silakan ulangi proses lupa password.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Alamat email tidak terdaftar di sistem.']);
        }

        // 1. Update password in Local Database
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // 2. Sync to Supabase Authentication
        $supabase = new \App\Services\SupabaseService();
        $supabase->updateUser($user->uuid, [
            'password' => $request->password,
        ]);

        event(new PasswordReset($user));

        // 3. Clear session keys
        session()->forget(['reset_email', 'reset_token', 'reset_verified']);

        return redirect()->route('login')
            ->with('status', 'Kata sandi Anda telah berhasil diperbarui! Silakan masuk menggunakan kata sandi baru.');
    }
}
