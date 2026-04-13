<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        if (!session()->has('dummy_products')) {
            session(['dummy_products' => [
                [
                    'id' => 1,
                    'nama_produk' => 'Bolu Susu Lembang',
                    'sku' => 'PRD-001',
                    'kategori' => 'Kue Basah',
                    'stok' => 45,
                    'minimal_stok' => 10,
                    'satuan' => 'Pcs',
                    'lokasi_rak' => 'Rak A1',
                    'harga_beli' => 25000,
                    'harga_jual' => 35000
                ],
                [
                    'id' => 2,
                    'nama_produk' => 'Brownies Panggang',
                    'sku' => 'PRD-002',
                    'kategori' => 'Kue Kering',
                    'stok' => 5,
                    'minimal_stok' => 10,
                    'satuan' => 'Kotak',
                    'lokasi_rak' => 'Rak B2',
                    'harga_beli' => 40000,
                    'harga_jual' => 55000
                ]
            ]]);
        }

        if (empty(session('dummy_opnames'))) {
            session(['dummy_opnames' => [
                ['id' => 1, 'tanggal_cek' => '20 Okt 2023 10:00', 'petugas' => 'Rina (Gudang)', 'stok_sistem' => 45, 'stok_fisik' => 45, 'selisih' => 0, 'keterangan' => 'Sesuai'],
                ['id' => 2, 'tanggal_cek' => '21 Okt 2023 14:00', 'petugas' => 'Budi (Admin)', 'stok_sistem' => 10, 'stok_fisik' => 8, 'selisih' => -2, 'keterangan' => 'Barang Rusak'],
            ]]);
        }

        if (empty(session('dummy_requests'))) {
            session(['dummy_requests' => [
                ['id' => 1, 'pemohon' => 'Siti (Outlet A)', 'jumlah_minta' => '20 Pcs', 'prioritas' => 'Tinggi', 'status' => 'Pending', 'alasan_permintaan' => 'Stok hampir habis'],
                ['id' => 2, 'pemohon' => 'Andi (Outlet B)', 'jumlah_minta' => '50 Kotak', 'prioritas' => 'Normal', 'status' => 'Disetujui', 'alasan_permintaan' => 'Restock mingguan'],
            ]]);
        }

        $products = session('dummy_products');
        $opnames = session('dummy_opnames');
        $requests = session('dummy_requests');

        return view('product', compact('products', 'opnames', 'requests'));
    }

    public function store(Request $request)
    {
        $products = session('dummy_products', []);
        $newId = count($products) > 0 ? max(array_column($products, 'id')) + 1 : 1;
        $products[] = [
            'id' => $newId,
            'nama_produk' => $request->nama_produk,
            'sku' => $request->sku,
            'kategori' => $request->kategori ?? 'Umum',
            'stok' => $request->stok ?? 0,
            'minimal_stok' => 10,
            'satuan' => $request->satuan ?? 'Pcs',
            'lokasi_rak' => $request->lokasi_rak ?? '-',
            'harga_beli' => $request->harga_beli ?? 0,
            'harga_jual' => $request->harga_jual ?? 0
        ];
        session(['dummy_products' => $products]);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $products = session('dummy_products', []);
        foreach ($products as &$product) {
            if ($product['id'] == $id) {
                $product['nama_produk'] = $request->nama_produk;
                $product['sku'] = $request->sku;
                $product['kategori'] = $request->kategori;
                $product['stok'] = $request->stok;
                $product['satuan'] = $request->satuan;
                $product['lokasi_rak'] = $request->lokasi_rak;
                $product['harga_beli'] = $request->harga_beli;
                $product['harga_jual'] = $request->harga_jual;
                break;
            }
        }
        session(['dummy_products' => $products]);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diupdate');
    }

    public function destroy($id)
    {
        $products = session('dummy_products', []);
        $products = array_values(array_filter($products, function($p) use ($id) {
            return $p['id'] != $id;
        }));
        session(['dummy_products' => $products]);
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }

    // Opname Logic
    public function storeOpname(Request $request)
    {
        $opnames = session('dummy_opnames', []);
        $newId = count($opnames) > 0 ? max(array_column($opnames, 'id')) + 1 : 1;
        
        $sistem = $request->stok_sistem ?? 0;
        $fisik = $request->stok_fisik ?? 0;

        $opnames[] = [
            'id' => $newId,
            'tanggal_cek' => \Carbon\Carbon::now()->format('d M Y H:i'),
            'petugas' => 'User Admin',
            'stok_sistem' => $sistem,
            'stok_fisik' => $fisik,
            'selisih' => $fisik - $sistem,
            'keterangan' => $request->keterangan ?? '-'
        ];
        session(['dummy_opnames' => $opnames]);
        return redirect()->route('products.index')->with('success', 'Data Opname berhasil ditambahkan');
    }

    public function updateOpname(Request $request, $id)
    {
        $opnames = session('dummy_opnames', []);
        foreach ($opnames as &$op) {
            if ($op['id'] == $id) {
                $sistem = $request->stok_sistem ?? 0;
                $fisik = $request->stok_fisik ?? 0;
                $op['stok_sistem'] = $sistem;
                $op['stok_fisik'] = $fisik;
                $op['selisih'] = $fisik - $sistem;
                $op['keterangan'] = $request->keterangan ?? '-';
                break;
            }
        }
        session(['dummy_opnames' => $opnames]);
        return redirect()->route('products.index')->with('success', 'Data Opname berhasil diupdate');
    }

    public function destroyOpname($id)
    {
        $opnames = session('dummy_opnames', []);
        $opnames = array_values(array_filter($opnames, function($o) use ($id) {
            return $o['id'] != $id;
        }));
        session(['dummy_opnames' => $opnames]);
        return redirect()->route('products.index')->with('success', 'Data Opname berhasil dihapus');
    }

    // Request Logic
    public function approveRequest($id)
    {
        $requests = session('dummy_requests', []);
        foreach ($requests as &$req) {
            if ($req['id'] == $id) {
                $req['status'] = 'Disetujui';
                break;
            }
        }
        session(['dummy_requests' => $requests]);
        return back()->with('success', 'Request stok telah disetujui');
    }

    public function rejectRequest($id)
    {
        $requests = session('dummy_requests', []);
        foreach ($requests as &$req) {
            if ($req['id'] == $id) {
                $req['status'] = 'Ditolak';
                break;
            }
        }
        session(['dummy_requests' => $requests]);
        return back()->with('success', 'Request stok telah ditolak');
    }
}