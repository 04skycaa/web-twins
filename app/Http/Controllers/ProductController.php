<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Outlet;
use App\Models\StockRequest;
use App\Models\StockCard;
use App\Models\Category;
use App\Models\ProductStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    /**
     * Display the product list (Tab 1).
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'stores']);

        /** @var User $user */
        $user = Auth::user();
        if (!$user->isOwner()) {
            $query->whereHas('stores', function($q) use ($user) {
                $q->where('store_id', $user->store_id)
                  ->where('status_aktif', true);
            });
        } else {
            $query->where(function($q) {
                $q->whereDoesntHave('stores')
                  ->orWhereHas('stores', function($sq) {
                      $sq->where('status_aktif', true);
                  });
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('kategori_id', $request->category_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'ilike', '%' . $request->search . '%');
        }

        $products = $query->paginate(10);

        // Add current stock for each product based on user store context
        $products->getCollection()->transform(function ($product) use ($user) {
            if ($user->isOwner()) {
                $product->current_stok = $product->stores->sum('stok');
            } else {
                $product->current_stok = $product->stores->where('store_id', $user->store_id)->first()->stok ?? 0;
            }
            return $product;
        });
        $categories = Category::all();

        return view('product.index', [
            'active_tab' => 'produk',
            'products' => $products,
            'categories' => $categories
        ]);
    }

    /**
     * Display the stock opname list (Tab 2).
     */
    public function opname(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Opname::with(['store', 'user', 'details.product'])->orderBy('tanggal', 'desc');

        if (!$user->isOwner()) {
            $query->where('store_id', $user->store_id);
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('details.product', function($q) use ($request) {
                $q->where('kategori_id', $request->category_id);
            });
        }

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'Draft') {
                $query->whereDoesntHave('details', function($q) {
                    $q->where('stok_fisik', '>', 0);
                });
            } elseif ($status == 'Proses') {
                $query->whereHas('details', function($q) {
                    $q->where('stok_fisik', '>', 0);
                })->whereHas('details', function($q) {
                    $q->where('stok_fisik', 0);
                });
            } elseif ($status == 'Selesai') {
                // items_filled == total_items
                $query->whereDoesntHave('details', function($q) {
                    $q->where('stok_fisik', 0);
                })->whereHas('details');
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('details.product', function($sq) use ($search) {
                    $sq->where('nama_produk', 'ilike', '%' . $search . '%');
                })->orWhereHas('store', function($sq) use ($search) {
                    $sq->where('nama', 'ilike', '%' . $search . '%');
                });
            });
        }

        $opnames = $query->paginate(10);
        $categories = Category::all();
        $unfinishedCountQuery = Opname::whereDate('tanggal', today())
            ->whereHas('details', function($q) {
                $q->where('stok_fisik', 0);
            });
        
        if (!$user->isOwner()) {
            $unfinishedCountQuery->where('store_id', $user->store_id);
        }
        $unfinished_count = $unfinishedCountQuery->count();

        $total_selisih_today = 0;
        $completed_today_count = 0;
        
        if ($user->isOwner()) {
            $total_selisih_today = OpnameDetail::whereHas('opname', function($q) {
                $q->whereDate('tanggal', today());
            })->sum('selisih');

            $completed_today_count = Opname::whereDate('tanggal', today())
                ->whereDoesntHave('details', function($q) {
                    $q->where('stok_fisik', 0);
                })->whereHas('details')->count();
        }

        if (!$user->isOwner()) {
            $stores = Outlet::where('uuid', $user->store_id)->get();
            $products = Product::whereHas('stores', function($q) use ($user) {
                $q->where('store_id', $user->store_id);
            })->get();
        } else {
            $stores = Outlet::all();
            $products = Product::all();
        }

        return view('product.index', [
            'active_tab' => 'opname',
            'opnames' => $opnames,
            'stores' => $stores,
            'products' => $products,
            'categories' => $categories,
            'unfinished_count' => $unfinished_count,
            'total_selisih_today' => $total_selisih_today,
            'completed_today_count' => $completed_today_count
        ]);
    }

    /**
     * Display the request product list (Tab 3).
     */
    public function request(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockRequest::with(['product.category', 'product.stores', 'store'])->orderBy('uuid', 'desc');

        if (!$user->isOwner()) {
            $query->where('store_id', $user->store_id);
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('kategori_id', $request->category_id);
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('prioritas') && $request->prioritas != '') {
            $query->where('prioritas', $request->prioritas);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($sq) use ($search) {
                    $sq->where('nama_produk', 'ilike', '%' . $search . '%');
                })->orWhere('status', 'ilike', '%' . $search . '%');
            });
        }

        $summaryQuery = StockRequest::query();
        if (!$user->isOwner()) {
            $summaryQuery->where('store_id', $user->store_id);
        }
        $pending_requests_count = (clone $summaryQuery)->where('status', 'Pending')->count();
        $high_priority_count = (clone $summaryQuery)->where('prioritas', 'Tinggi')->count();

        $requests = $query->paginate(10)->withQueryString();
        $categories = Category::all();

        if (!$user->isOwner()) {
            $products = Product::whereHas('stores', function($q) use ($user) {
                $q->where('store_id', $user->store_id);
            })->get();
        } else {
            $products = Product::all();
        }

        $all_products = Product::all();
        $all_stores = Outlet::all();

        return view('product.index', [
            'active_tab' => 'request',
            'requests' => $requests,
            'products' => $products,
            'categories' => $categories,
            'pending_requests_count' => $pending_requests_count,
            'high_priority_count' => $high_priority_count,
            'all_products' => $all_products,
            'all_stores' => $all_stores,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:category,uuid',
            'harga_modal' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
        ]);

        $imageUrl = null;
        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            @list(, $fileData) = explode(';', $base64Image);
            @list(, $fileData)      = explode(',', $fileData);
            
            $imageBinary = base64_decode($fileData);
            $fileName = \Illuminate\Support\Str::uuid() . '.png';
            
            \Illuminate\Support\Facades\Storage::disk('public')->put('products/' . $fileName, $imageBinary);
            $imageUrl = '/storage/products/' . $fileName;
        }

        $product = Product::create([
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
            'image_url' => $imageUrl,
        ]);
        StockCard::create([
            'product_id' => $product->uuid,
            'jmlh' => 0,
            'keterangan' => 'Produk baru ditambahkan ke sistem',
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:category,uuid',
            'harga_modal' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
        ]);

        $product->update([
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus secara permanen!');
    }

    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,uuid'
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->isOwner()) {
            ProductStore::whereIn('product_id', $request->ids)
                ->where('store_id', $user->store_id)
                ->update(['status_aktif' => false]);
        } else {
            ProductStore::whereIn('product_id', $request->ids)
                ->update(['status_aktif' => false]);
        }

        return redirect()->back()->with('success', count($request->ids) . ' Produk berhasil dihapus dari daftar!');
    }

    public function storeOpname(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'store_id' => 'required|exists:store,uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,uuid',
            'items.*.stok_sistem' => 'required|integer',
            'items.*.stok_fisik' => 'nullable|integer',
        ]);

        $store_id = $request->store_id;
        if (!$user->isOwner()) {
            $store_id = $user->store_id;
        }

        DB::beginTransaction();
        try {
            $opname = Opname::create([
                'tanggal' => now(),
                'store_id' => $store_id,
                'user_id' => $user->uuid,
            ]);

            foreach ($request->items as $item) {
                $fisik = $item['stok_fisik'] ?? 0;
                $selisih = $fisik - $item['stok_sistem'];

                OpnameDetail::create([
                    'opname_id' => $opname->uuid,
                    'product_id' => $item['product_id'],
                    'stok_sistem' => $item['stok_sistem'],
                    'stok_fisik' => $fisik,
                    'selisih' => $selisih,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                if ($selisih != 0 && $fisik > 0) {
                    StockCard::create([
                        'product_id' => $item['product_id'],
                        'store_id' => $store_id,
                        'jmlh' => $selisih,
                        'keterangan' => 'Penyesuaian stok melalui Opname',
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Opname produk berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan opname: ' . $e->getMessage());
        }
    }

    public function updateOpname(Request $request, $id)
    {
        $opname = Opname::findOrFail($id);
        
        $request->validate([
            'store_id' => 'required|exists:store,uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,uuid',
            'items.*.stok_sistem' => 'required|integer',
            'items.*.stok_fisik' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $opname->update(['store_id' => $request->store_id]);
            OpnameDetail::where('opname_id', $opname->uuid)->delete();
            $productIds = collect($request->items)->pluck('product_id')->toArray();
            StockCard::whereIn('product_id', $productIds)
                ->where('store_id', $request->store_id)
                ->where('keterangan', 'Penyesuaian stok melalui Opname')
                ->whereDate('created_at', '>=', $opname->tanggal) 
                ->delete();

            foreach ($request->items as $item) {
                $fisik = $item['stok_fisik'] ?? 0;
                $selisih = $fisik - $item['stok_sistem'];

                OpnameDetail::create([
                    'opname_id' => $opname->uuid,
                    'product_id' => $item['product_id'],
                    'stok_sistem' => $item['stok_sistem'],
                    'stok_fisik' => $fisik,
                    'selisih' => $selisih,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                if ($selisih != 0 && $fisik > 0) {
                    StockCard::create([
                        'product_id' => $item['product_id'],
                        'store_id' => $request->store_id,
                        'jmlh' => $selisih,
                        'keterangan' => 'Penyesuaian stok melalui Opname',
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Opname produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui opname: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $opname = Opname::with(['details.product', 'store', 'user'])->findOrFail($id);
        return response()->json($opname);
    }

    public function destroyOpname($id)
    {
        $opname = Opname::findOrFail($id);
        OpnameDetail::where('opname_id', $opname->uuid)->delete();
        $opname->delete();

        return redirect()->back()->with('success', 'Riwayat opname berhasil dihapus!');
    }

    public function storeRequest(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'product_id' => 'required|exists:products,uuid',
            'jumlah_minta' => 'required|integer|min:1',
            'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
            'store_id' => 'nullable|exists:store,uuid',
        ]);

        $target_store_id = $user->isOwner() && $request->has('store_id') ? $request->store_id : $user->store_id;

        StockRequest::create([
            'product_id' => $request->product_id,
            'jumlah_minta' => $request->jumlah_minta,
            'prioritas' => $request->prioritas,
            'pemohon' => $user->name,
            'alasan_permintaan' => $request->alasan_permintaan,
            'status' => 'Pending',
            'store_id' => $target_store_id,
        ]);

        return redirect()->back()->with('success', 'Request produk berhasil dikirim!');
    }

    public function updateRequest(Request $request, $id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Pending') {
            return redirect()->back()->with('error', 'Hanya request pending yang bisa diubah.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,uuid',
            'jumlah_minta' => 'required|integer|min:1',
            'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
        ]);

        $req->update([
            'product_id' => $request->product_id,
            'jumlah_minta' => $request->jumlah_minta,
            'prioritas' => $request->prioritas,
            'alasan_permintaan' => $request->alasan_permintaan,
        ]);
        return redirect()->back()->with('success', 'Request produk berhasil diperbarui!');
    }

    public function destroyRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Pending') {
            return redirect()->back()->with('error', 'Hanya request pending yang bisa dibatalkan.');
        }

        $req->delete();
        return redirect()->back()->with('success', 'Request produk berhasil dibatalkan!');
    }

    public function approveRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'Diproses']);

        return redirect()->back()->with('success', 'Request produk telah disetujui dan sedang diproses!');
    }

    public function rejectRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'Ditolak']);
        return redirect()->back()->with('success', 'Request produk telah ditolak!');
    }

    public function shipRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Diproses') {
            return redirect()->back()->with('error', 'Hanya request yang sedang diproses yang bisa dikirim.');
        }
        $req->update(['status' => 'Dikirim']);
        return redirect()->back()->with('success', 'Request produk berhasil ditandai sebagai dikirim!');
    }

    public function receiveRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Dikirim') {
            return redirect()->back()->with('error', 'Hanya request yang sedang dikirim yang bisa diselesaikan.');
        }
        
        $req->update(['status' => 'Selesai']);
        $productStore = ProductStore::firstOrCreate(
            ['product_id' => $req->product_id, 'store_id' => $req->store_id],
            ['stok' => 0, 'status_aktif' => true]
        );
        $productStore->increment('stok', $req->jumlah_minta);
        $productStore->update(['status_aktif' => true]);

        return redirect()->back()->with('success', 'Barang telah diterima, request selesai dan stok cabang bertambah otomatis!');
    }

    /*untuk export ke Excel*/
    public function exportExcel(Request $request)
    {
        $tab = $request->active_tab ?? 'produk';
        $data = $this->getExportData($request, $tab);
        $filename = "export_{$tab}_" . date('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = $this->getExportColumns($tab);

        $callback = function() use ($data, $columns, $tab) {
            $file = fopen('php://output', 'w');
            // Add BOM to fix Excel encoding and parsing issues
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($data as $item) {
                $row = $this->formatExportRow($item, $tab);
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*untuk export ke PDF*/
    public function exportPdf(Request $request)
    {
        $tab = $request->active_tab ?? 'produk';
        $data = $this->getExportData($request, $tab);
        $title = "Laporan " . ($tab == 'produk' ? 'Produk' : ($tab == 'opname' ? 'Stock Opname' : 'Request Produk'));

        $pdf = Pdf::loadView('exports.pdf', [
            'title' => $title,
            'tab' => $tab,
            'data' => $data,
            'date' => date('d F Y')
        ])->setPaper('a4', 'landscape');

        return $pdf->download("export_{$tab}_" . date('Y-m-d') . ".pdf");
    }

    private function getExportData(Request $request, $tab)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($tab == 'produk') {
            $query = Product::with(['category', 'stores']);
            if (!$user->isOwner()) {
                $query->whereHas('stores', function($q) use ($user) {
                    $q->where('store_id', $user->store_id)->where('status_aktif', true);
                });
            }
            if ($request->category_id) $query->where('kategori_id', $request->category_id);
            if ($request->search) $query->where('nama_produk', 'ilike', '%' . $request->search . '%');
            return $query->get();
        } 
        
        if ($tab == 'opname') {
            $query = Opname::with(['store', 'user', 'details.product'])->orderBy('tanggal', 'desc');
            if (!$user->isOwner()) $query->where('store_id', $user->store_id);
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('uuid', 'ilike', '%' . $request->search . '%')
                      ->orWhereHas('user', function($uq) use ($request) {
                          $uq->where('name', 'ilike', '%' . $request->search . '%');
                      });
                });
            }
            return $query->get();
        }

        if ($tab == 'request') {
            $query = StockRequest::with(['product', 'store'])->orderBy('uuid', 'desc');
            if (!$user->isOwner()) $query->where('store_id', $user->store_id);
            if ($request->search) {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('nama_produk', 'ilike', '%' . $request->search . '%');
                });
            }
            if ($request->status) $query->where('status', $request->status);
            return $query->get();
        }

        return collect();
    }

    private function getExportColumns($tab)
    {
        if ($tab == 'produk') return ['Nama Produk', 'Barcode', 'Kategori', 'Harga Modal', 'Harga Jual', 'Stok'];
        if ($tab == 'opname') return ['No Ref', 'Tanggal', 'User', 'Outlet', 'Total Item', 'Total Selisih', 'Status'];
        if ($tab == 'request') return ['Produk', 'Pemohon', 'Outlet', 'Jumlah', 'Prioritas', 'Status', 'Tanggal'];
        return [];
    }

    private function formatExportRow($item, $tab)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($tab == 'produk') {
             return [
                $item->nama_produk,
                $item->barcode,
                $item->category->nama_category ?? '-',
                $item->harga_modal,
                $item->harga_jual,
                $user->isOwner() ? $item->stores->sum('stok') : ($item->stores->where('store_id', $user->store_id)->first()->stok ?? 0)
            ];
        }
        if ($tab == 'opname') {
            return [
                $item->uuid,
                ' ' . \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y'),
                $item->user->name ?? '-',
                $item->store->nama ?? '-',
                $item->total_items,
                $item->total_selisih,
                $item->status
            ];
        }
        if ($tab == 'request') {
            return [
                $item->product->nama_produk ?? '-',
                $item->pemohon,
                $item->store->nama ?? '-',
                $item->jumlah_minta,
                $item->prioritas,
                $item->status,
                '-'
            ];
        }
        return [];
    }
}