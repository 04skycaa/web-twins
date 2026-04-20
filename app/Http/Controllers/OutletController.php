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
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
        ]);

        Outlet::create([
            'nama' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'status_aktif' => true,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
        ]);

        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        
        $outlet->update([
            'nama' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function destroy($uuid)
    {
        $outlet = Outlet::where('uuid', $uuid)->first();
        if ($outlet) {
            $outlet->delete();
            return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
        }
        
        return redirect()->route('outlet.index')->with('error', 'Outlet tidak ditemukan');
    }
}
