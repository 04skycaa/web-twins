<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();

        if ($user->role === 'owner') {
            $data = [
                'total_outlet' => Outlet::count(),
                'total_transaksi' => Transaction::count(),
                'transaksi_terbaru' => Transaction::with('outlet')->latest()->take(10)->get(),
                'title' => 'Dashboard Owner (Semua Toko)'
            ];
        } 
        
        elseif ($user->role === 'kepala_toko') {
            if (!$user->outlet_id) {
                return abort(403, 'Anda belum ditugaskan ke outlet manapun.');
            }

            $data = [
                'outlet' => Outlet::find($user->outlet_id),
                'total_transaksi' => Transaction::where('outlet_id', $user->outlet_id)->count(),
                'transaksi_terbaru' => Transaction::where('outlet_id', $user->outlet_id)->latest()->take(10)->get(),
                'title' => 'Dashboard Outlet: ' . $user->outlet->name
            ];
        }

        return view('dashboard', $data);
    }
}
