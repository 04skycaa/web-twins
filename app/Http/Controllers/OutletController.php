<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index()
    {
        if (!session()->has('dummy_outlets')) {
            session(['dummy_outlets' => [
                ['idoutlet' => 1, 'kode_outlet' => 'OTL001', 'nama_outlet' => 'SweetBake Pusat', 'alamat' => 'Jl. Sudirman No 1'],
                ['idoutlet' => 2, 'kode_outlet' => 'OTL002', 'nama_outlet' => 'SweetBake Cab. A', 'alamat' => 'Jl. Thamrin No 50'],
            ]]);
        }
        $outlets = session('dummy_outlets');
        return view('outlet.index', compact('outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'kode_outlet' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $outlets = session('dummy_outlets', []);
        $newId = count($outlets) > 0 ? max(array_column($outlets, 'idoutlet')) + 1 : 1;
        
        $outlets[] = [
            'idoutlet' => $newId,
            'kode_outlet' => $request->kode_outlet ?? 'OTL'.rand(100,999),
            'nama_outlet' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'is_active' => true,
        ];

        session(['dummy_outlets' => $outlets]);
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        $outlets = session('dummy_outlets', []);
        foreach ($outlets as &$outlet) {
            if ($outlet['idoutlet'] == $id) {
                $outlet['nama_outlet'] = $request->nama_outlet;
                $outlet['alamat'] = $request->alamat;
                break;
            }
        }
        
        session(['dummy_outlets' => $outlets]);
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function destroy($id)
    {
        $outlets = session('dummy_outlets', []);
        $outlets = array_values(array_filter($outlets, function($o) use ($id) {
            return $o['idoutlet'] != $id;
        }));
        
        session(['dummy_outlets' => $outlets]);
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }
}
