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
            'name.unique' => 'Nama/Username ini sudah terdaftar.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);


        return DB::transaction(function () use ($request) {
            $operator = DB::table('operator')->where('nama', 'User')->first();
            $operatorId = $operator ? $operator->uuid : null;

            $user = User::create([
                'username' => $request->name, 
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'operator_id' => $operatorId, 
                'store_id' => null, 
                'status_aktif' => false,
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('verification.notice')
                ->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi.');

        });
    }

}