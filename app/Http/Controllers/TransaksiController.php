<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        return view('transaksi.index');
    }

    public function riwayat()
    {
        return view('transaksi.riwayat');
    }

    public function diskon()
    {
        return view('transaksi.diskon');
    }
}
