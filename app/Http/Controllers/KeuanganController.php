<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Base Query untuk transaksi selesai (sesuaikan dengan status kalian jika ada)
        // Kita sementara pakai semua transaksi, atau jika kolom status ada: ->where('status', 'selesai')
        $query = Transaction::query();

        // Jika Kepala Toko, batasi data keungan hanya untuk outletnya
        if ($user->role === 'kepala_toko') {
            $query->where('idoutlet', $user->outlet_id);
        }

        // Data Ringkasan Hari Ini
        $todayIncome = (clone $query)->whereDate('tanggalorder', Carbon::today())->sum('grandtotal');
        $todayTransactions = (clone $query)->whereDate('tanggalorder', Carbon::today())->count();
        
        // Data Ringkasan Bulan Ini
        $monthIncome = (clone $query)->whereMonth('tanggalorder', Carbon::now()->month)
                                     ->whereYear('tanggalorder', Carbon::now()->year)
                                     ->sum('grandtotal');

        // Data Grafik 7 Hari Terakhir
        $chartDates = [];
        $chartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartDates[] = $date->format('d M');
            $income = (clone $query)->whereDate('tanggalorder', $date)->sum('grandtotal');
            $chartData[] = $income;
        }

        // Transaksi Terbaru
        $recentTransactions = (clone $query)->orderBy('tanggalorder', 'desc')->take(10)->get();

        return view('keuangan.index', compact(
            'todayIncome', 
            'todayTransactions', 
            'monthIncome', 
            'chartDates', 
            'chartData',
            'recentTransactions'
        ));
    }
}
