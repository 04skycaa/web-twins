<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Promo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransaksiController extends Controller
{
    public function index()
    {
        return $this->riwayat();
    }

    public function riwayat()
    {
        // Dummy data for riwayat (can be refactored later to use real transactions)
        $data = [
            ['id' => '#TW-00123', 'tanggal' => '24 Okt 2023 14:30', 'kasir' => 'Budi (Admin)', 'pelanggan' => 'Umum / Non-Member', 'qty' => 3, 'total' => 'Rp 60.000', 'status' => 'Selesai'],
            ['id' => '#TW-00124', 'tanggal' => '24 Okt 2023 15:10', 'kasir' => 'Siti (Kasir)', 'pelanggan' => 'Member (Andi)', 'qty' => 5, 'total' => 'Rp 120.000', 'status' => 'Selesai'],
            ['id' => '#TW-00125', 'tanggal' => '24 Okt 2023 16:05', 'kasir' => 'Budi (Admin)', 'pelanggan' => 'GrabFood', 'qty' => 2, 'total' => 'Rp 45.000', 'status' => 'Proses'],
        ];

        return view('transaksi.riwayat', compact('data'));
    }

    public function diskon()
    {
        $diskons = Promo::orderBy('tanggal_mulai', 'desc')->get();
        return view('transaksi.diskon', compact('diskons'));
    }

    public function storeDiskon(Request $request)
    {
        $request->validate([
            'nama_promo' => 'required|string|max:255',
            'tipe' => 'required|string',
            'nilai' => 'required|numeric',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'image_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->all();
        $data['status'] = $request->status == 'Aktif' ? true : false;

        if ($request->hasFile('image_banner')) {
            $file = $request->file('image_banner');
            $filename = time() . '_' . Str::slug($request->nama_promo) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('promos', $filename, 'public');
            $data['image_banner'] = $path;
        }

        Promo::create($data);
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil ditambahkan');
    }

    public function updateDiskon(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        
        $request->validate([
            'nama_promo' => 'required|string|max:255',
            'tipe' => 'required|string',
            'nilai' => 'required|numeric',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'image_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->all();
        $data['status'] = $request->status == 'Aktif' ? true : false;

        if ($request->hasFile('image_banner')) {
            // Delete old banner if exists
            if ($promo->image_banner) {
                Storage::disk('public')->delete($promo->image_banner);
            }
            
            $file = $request->file('image_banner');
            $filename = time() . '_' . Str::slug($request->nama_promo) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('promos', $filename, 'public');
            $data['image_banner'] = $path;
        }

        $promo->update($data);
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil diperbarui');
    }

    public function destroyDiskon($id)
    {
        $promo = Promo::findOrFail($id);
        if ($promo->image_banner) {
            Storage::disk('public')->delete($promo->image_banner);
        }
        $promo->delete();
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil dihapus');
    }
}