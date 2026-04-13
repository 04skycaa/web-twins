<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    public function index()
    {
        // Use dummy data since we bypass database
        $todayIncome = 450000;
        $todayTransactions = 15;
        
        $monthIncome = 12500000;

        // Data Grafik 7 Hari Terakhir
        $chartDates = [];
        $chartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartDates[] = $date->format('d M');
            // Random income between 200k and 1m for dummy
            $chartData[] = rand(200000, 1000000);
        }

        // Transaksi Terbaru Dummy
        $recentTransactions = collect([
            (object)['idorder' => 1001, 'tanggalorder' => Carbon::now()->subMinutes(10), 'grandtotal' => 150000, 'status' => 'selesai'],
            (object)['idorder' => 1002, 'tanggalorder' => Carbon::now()->subMinutes(45), 'grandtotal' => 50000, 'status' => 'selesai'],
            (object)['idorder' => 1003, 'tanggalorder' => Carbon::now()->subHours(2), 'grandtotal' => 200000, 'status' => 'selesai'],
            (object)['idorder' => 1004, 'tanggalorder' => Carbon::now()->subHours(5), 'grandtotal' => 75000, 'status' => 'selesai'],
            (object)['idorder' => 1005, 'tanggalorder' => Carbon::now()->subDays(1), 'grandtotal' => 300000, 'status' => 'selesai'],
        ]);

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
