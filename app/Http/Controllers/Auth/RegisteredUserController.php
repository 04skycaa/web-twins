<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,username'], 
            'no_hp' => ['required', 'string', 'max:15', 'unique:users,no_hp'], 
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.unique' => 'Nama/Username ini sudah terdaftar.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'no_hp.required' => 'Nomor handphone wajib diisi.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
            'no_hp.max' => 'Nomor HP tidak boleh lebih dari 15 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        // 1. Get default operator ID
        $operator = DB::table('operator')->where('nama', 'User')->first();
        $operatorId = $operator ? $operator->uuid : null;

        // 2. Create User in Supabase Auth (triggers email verification OTP)
        $supabase = new \App\Services\SupabaseService();
        $supabaseUser = $supabase->signup([
            'email' => $request->email,
            'password' => $request->password,
            'username' => $request->name,
            'no_hp' => $request->no_hp,
            'operator_id' => $operatorId,
            'role' => 'user',
        ]);

        if (!$supabaseUser || isset($supabaseUser['error'])) {
            $errorMessage = $supabaseUser['error'] ?? 'Gagal mendaftarkan akun ke sistem autentikasi Supabase. Periksa pengaturan email/SMTP Anda.';
            
            if (stripos($errorMessage, 'confirmation email') !== false || stripos($errorMessage, 'sending') !== false) {
                $errorMessage = 'Gagal mendaftarkan akun: Supabase gagal mengirim email konfirmasi. Periksa kembali pengaturan Custom SMTP Anda di Dashboard Supabase (pastikan Google App Password benar dan SSL dinonaktifkan jika port 587).';
            }
            
            return back()->withInput($request->only('name', 'email', 'no_hp'))
                ->with('error', $errorMessage);
        }

        $supabaseUid = $supabaseUser['id'] ?? ($supabaseUser['user']['id'] ?? null);
        if (!$supabaseUid) {
            return back()->withInput($request->only('name', 'email', 'no_hp'))
                ->with('error', 'Gagal mendaftarkan akun ke sistem autentikasi (UID tidak valid).');
        }

        // 3. Store registration details in session temporarily (do NOT write to DB yet!)
        session([
            'register_uuid' => $supabaseUid,
            'register_name' => $request->name,
            'register_no_hp' => $request->no_hp,
            'register_email' => $request->email,
            'register_password' => $request->password,
            'register_operator_id' => $operatorId,
        ]);

        return redirect()->route('register.verify-view')
            ->with('success', 'Registrasi awal berhasil! Silakan masukkan kode OTP yang telah dikirim ke email Anda untuk mengaktifkan akun.');
    }

    /**
     * Display the registration OTP verification view.
     */
    public function showVerifyForm(): View|RedirectResponse
    {
        if (!session()->has('register_email')) {
            return redirect()->route('register')
                ->with('error', 'Silakan isi form registrasi terlebih dahulu.');
        }

        return view('auth.verify-email');
    }

    /**
     * Verify the registration OTP code, then write to database.
     */
    public function verifyOTP(Request $request): RedirectResponse
    {
        if (!session()->has('register_email')) {
            return redirect()->route('register')
                ->with('error', 'Sesi registrasi telah kedaluwarsa. Silakan mendaftar ulang.');
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus tepat 6 digit.',
        ]);

        $email = session('register_email');

        $supabase = new \App\Services\SupabaseService();
        $response = $supabase->verifyOTP($email, $request->otp, 'signup');

        if ($response) {
            // OTP verified! Create user in local database now
            return DB::transaction(function () {
                $user = User::create([
                    'uuid' => session('register_uuid'),
                    'username' => session('register_name'), 
                    'no_hp' => session('register_no_hp'),
                    'email' => session('register_email'),
                    'email_verified_at' => now(), // marked as verified immediately!
                    'password' => Hash::make(session('register_password')),
                    'operator_id' => session('register_operator_id'), 
                    'store_id' => null, 
                    'status_aktif' => true, // active immediately!
                ]);

                event(new Registered($user));

                // Log the user in
                Auth::login($user);

                // Clear temporary session data
                session()->forget([
                    'register_uuid',
                    'register_name',
                    'register_no_hp',
                    'register_email',
                    'register_password',
                    'register_operator_id'
                ]);

                return redirect()->to('/')
                    ->with('success', 'Registrasi berhasil! Akun Anda telah aktif dan Anda otomatis masuk.');
            });
        }

        return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah atau telah kedaluwarsa.']);
    }

    /**
     * Resend registration OTP.
     */
    public function resendOTP(): RedirectResponse
    {
        if (!session()->has('register_email')) {
            return redirect()->route('register')
                ->with('error', 'Sesi registrasi tidak ditemukan.');
        }

        $email = session('register_email');

        $supabase = new \App\Services\SupabaseService();
        $success = $supabase->resendSignupOTP($email);

        if ($success) {
            return back()->with('status', 'verification-link-sent');
        }

        return back()->with('error', 'Gagal mengirim ulang kode OTP. Silakan coba beberapa saat lagi.');
    }

    /**
     * Cancel verification and return to register form.
     */
    public function cancelVerification(): RedirectResponse
    {
        session()->forget([
            'register_uuid',
            'register_name',
            'register_no_hp',
            'register_email',
            'register_password',
            'register_operator_id'
        ]);

        return redirect()->route('register');
    }

}