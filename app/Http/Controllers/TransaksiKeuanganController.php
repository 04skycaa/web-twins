<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiKeuanganController extends Controller
{
    public function index()
    {
        return view('keuangan.transaksi', [
            'title' => 'Transaksi Keuangan'
        ]);
    }
}
