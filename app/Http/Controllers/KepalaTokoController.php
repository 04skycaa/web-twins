<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Transaction; 
use Illuminate\Support\Facades\Auth;

class KepalaTokoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->outlet_id) {
            return abort(403, 'Akun Anda tidak terhubung dengan outlet manapun.');
        }

        $outlet = Outlet::withCount('transactions')
                        ->where('id', $user->outlet_id)
                        ->firstOrFail();

        $recentTransactions = Transaction::where('outlet_id', $user->outlet_id)
                                        ->latest()
                                        ->take(5)
                                        ->get();

        return view('kepala_toko.dashboard', compact('outlet', 'recentTransactions'));
    }
}