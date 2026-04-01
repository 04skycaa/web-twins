<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction; 

class TransaksiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'kepala_toko') {
            $data = Transaction::where('idoutlet', $user->outlet_id)->get();
        } else {
            $data = Transaction::all();
        }

        return view('dashboard', compact('data'));
    }
}