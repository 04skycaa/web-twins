<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;

class OutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::all();
        return view('outlet.index', compact('outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'kode_outlet' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        Outlet::create([
            'nama_outlet' => $request->nama_outlet,
            'kode_outlet' => $request->kode_outlet ?? 'OTL'.rand(100,999),
            'alamat' => $request->alamat,
            'is_active' => true,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        $outlet = Outlet::findOrFail($id);
        $outlet->update([
            'nama_outlet' => $request->nama_outlet,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function destroy($id)
    {
        $outlet = Outlet::findOrFail($id);
        
        // Cek jika outlet masih digunakan oleh user (optional, Laravel constraint will block if foreign key exists)
        if($outlet->users()->exists()) {
            return redirect()->route('outlet.index')->with('error', 'Gagal menghapus! Ada User yang masih terdaftar di Outlet ini.');
        }

        $outlet->delete();
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }
}
