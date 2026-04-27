<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class KontakController extends Controller
{
    public function index()
    {
        $pelanggan = Contact::where('tipe', 'customer')->latest('uuid')->get();
        $supplier = Contact::where('tipe', 'supplier')->latest('uuid')->get();

        return view('kontak.index', compact('pelanggan', 'supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'tipe' => 'required|in:customer,supplier',
        ]);

        Contact::create([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'tipe' => $request->tipe,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil diperbarui');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil dihapus');
    }
}
