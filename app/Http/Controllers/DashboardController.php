<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();

        // Dummy data untuk transaksi
        $dummyTransaksi = collect([
            (object)['id' => 1, 'outlet_id' => 1, 'total_price' => 150000, 'status' => 'selesai'],
            (object)['id' => 2, 'outlet_id' => 1, 'total_price' => 50000, 'status' => 'selesai'],
        ]);

        if ($user->role === 'owner') {
            $data = [
                'total_outlet' => Outlet::count(),
                'total_transaksi' => 50, // dummy
                'transaksi_terbaru' => collect(), // dummy empty for now or use dummy array
                'title' => 'Dashboard Owner (Semua Toko)'
            ];
        } 
        
        elseif ($user->role === 'kepala_toko') {
            if (!$user->outlet_id) {
                return abort(403, 'Anda belum ditugaskan ke outlet manapun.');
            }

            $data = [
                'outlet' => Outlet::find($user->outlet_id),
                'total_transaksi' => 25, // dummy
                'transaksi_terbaru' => collect(), // dummy
                'title' => 'Dashboard Outlet: ' . ($user->outlet ? $user->outlet->name : 'Unknown')
            ];
        } else {
            // Default untuk selain owner & kepala toko
            $data = [
                'total_outlet' => 0,
                'total_transaksi' => 0,
                'transaksi_terbaru' => collect(),
                'title' => 'Dashboard Kasir'
            ];
        }

        return view('dashboard', $data);
    }
}
