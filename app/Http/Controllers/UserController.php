<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Outlet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('outlet')->latest()->get();
        $outlets = Outlet::all();
        
        return view('users.index', compact('users', 'outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'no_hp' => ['required', 'string', 'max:15', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required'],
            'outlet_id' => ['nullable'], 
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'outlet_id' => $request->outlet_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // $id di sini adalah UUID karena primary key model adalah uuid
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->uuid.',uuid'],
            'no_hp' => ['required', 'string', 'max:15', 'unique:users,no_hp,'.$user->uuid.',uuid'],
            'role' => ['required'],
            'outlet_id' => ['nullable'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
            'outlet_id' => $request->outlet_id,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => [Rules\Password::defaults()]]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if(Auth::user()->uuid == $user->uuid) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus akun sendiri!');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

}
