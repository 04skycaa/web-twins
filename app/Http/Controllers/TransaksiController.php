<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function index()
    {
        return $this->riwayat();
    }

    public function riwayat()
    {
        // Dummy data for riwayat
        $data = [
            ['id' => '#TW-00123', 'tanggal' => '24 Okt 2023 14:30', 'kasir' => 'Budi (Admin)', 'pelanggan' => 'Umum / Non-Member', 'qty' => 3, 'total' => 'Rp 60.000', 'status' => 'Selesai'],
            ['id' => '#TW-00124', 'tanggal' => '24 Okt 2023 15:10', 'kasir' => 'Siti (Kasir)', 'pelanggan' => 'Member (Andi)', 'qty' => 5, 'total' => 'Rp 120.000', 'status' => 'Selesai'],
            ['id' => '#TW-00125', 'tanggal' => '24 Okt 2023 16:05', 'kasir' => 'Budi (Admin)', 'pelanggan' => 'GrabFood', 'qty' => 2, 'total' => 'Rp 45.000', 'status' => 'Proses'],
        ];

        return view('transaksi.riwayat', compact('data'));
    }

    public function diskon()
    {
        if (!session()->has('dummy_diskons')) {
            session(['dummy_diskons' => [
                ['id' => 1, 'nama' => 'Diskon Member', 'kode' => 'MEMBER-ROSTER', 'tipe' => 'Potongan Harga', 'nilai' => 'Rp 5.000', 'periode' => 'Selamanya', 'status' => 'Aktif'],
                ['id' => 2, 'nama' => 'Promo Akhir Tahun', 'kode' => 'YREND-2023', 'tipe' => 'Persentase', 'nilai' => '15%', 'periode' => '01 Des - 31 Des', 'status' => 'Nonaktif'],
            ]]);
        }
        $diskons = session('dummy_diskons');

        return view('transaksi.diskon', compact('diskons'));
    }

    public function storeDiskon(Request $request)
    {
        $diskons = session('dummy_diskons', []);
        $newId = count($diskons) > 0 ? max(array_column($diskons, 'id')) + 1 : 1;
        
        $diskons[] = [
            'id' => $newId,
            'nama' => $request->nama,
            'kode' => $request->kode,
            'tipe' => $request->tipe,
            'nilai' => $request->nilai,
            'periode' => $request->periode,
            'status' => $request->status,
        ];
        
        session(['dummy_diskons' => $diskons]);
        return redirect()->route('transaksi.diskon')->with('success', 'Diskon berhasil ditambahkan');
    }

    public function updateDiskon(Request $request, $id)
    {
        $diskons = session('dummy_diskons', []);
        foreach ($diskons as &$d) {
            if ($d['id'] == $id) {
                $d['nama'] = $request->nama;
                $d['kode'] = $request->kode;
                $d['tipe'] = $request->tipe;
                $d['nilai'] = $request->nilai;
                $d['periode'] = $request->periode;
                $d['status'] = $request->status;
                break;
            }
        }
        session(['dummy_diskons' => $diskons]);
        return redirect()->route('transaksi.diskon')->with('success', 'Diskon berhasil diperbarui');
    }

    public function destroyDiskon($id)
    {
        $diskons = session('dummy_diskons', []);
        $diskons = array_filter($diskons, function($d) use ($id) {
            return $d['id'] != $id;
        });
        session(['dummy_diskons' => array_values($diskons)]);
        return redirect()->route('transaksi.diskon')->with('success', 'Diskon berhasil dihapus');
    }
}