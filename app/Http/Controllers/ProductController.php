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
use App\Models\PriceLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\LandingController;

class ProductController extends Controller
{
    /**
     * Display the product list (Tab 1).
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $selectedStoreId = $request->get('store_id');

        $query = Product::with(['category', 'stores.store', 'priceLevels']);

        // We show all products, context-based stock is calculated in transform below
        if (!$user->isOwner()) {
            $selectedStoreId = $user->store_id;
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('kategori_id', $request->category_id);
        }

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
        }

        $products = $query->paginate(10);

        // Add current stock for each product based on store context
        $products->getCollection()->transform(function ($product) use ($user, $selectedStoreId) {
            $storeRelation = null;
            if ($user->isOwner()) {
                if ($selectedStoreId && $selectedStoreId !== 'all') {
                    $storeRelation = $product->stores->where('store_id', $selectedStoreId)->first();
                    $product->current_stok = $storeRelation ? $storeRelation->stok : 0;
                } else {
                    $product->current_stok = $product->stores->sum('stok');
                }
            } else {
                $storeRelation = $product->stores->where('store_id', $user->store_id)->first();
                $product->current_stok = $storeRelation ? $storeRelation->stok : 0;
            }
            
            $product->current_kadaluarsa = $storeRelation && $storeRelation->kadaluarsa ? \Carbon\Carbon::parse($storeRelation->kadaluarsa)->format('d F Y') : '-';
            $product->resolved_image_url = \App\Http\Controllers\LandingController::resolveImageUrl($product->image_url);
            return $product;
        });

        $categories = Category::all();
        $stores = $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]);

        return view('product.index', [
            'active_tab' => 'produk',
            'products' => $products,
            'categories' => $categories,
            'stores' => $stores,
            'selected_store_id' => $selectedStoreId,
            'all_products' => Product::all()
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
            'all_products' => Product::all(),
            'categories' => $categories,
            'unfinished_count' => $unfinished_count,
            'total_selisih_today' => $total_selisih_today,
            'completed_today_count' => $completed_today_count
        ]);
    }

    /**
     * Display the stock and expired alerts (Tab 3).
     */
    public function request(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $query = ProductStore::with(['product.category', 'store'])
            ->where(function($q) {
                $q->where('status_aktif', true)->orWhereNull('status_aktif');
            });

        if (!$user->isOwner()) {
            $query->where('store_id', $user->store_id);
        } else {
            $selectedStoreId = $request->get('store_id');
            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $query->where('store_id', $selectedStoreId);
            }
        }

        // Filter based on type
        $type = $request->get('type');
        if ($type == 'stok_habis') {
            $query->where(function($q) {
                $q->where('stok', '<=', 10)->orWhereNull('stok');
            });
        } elseif ($type == 'expired') {
            $query->whereNotNull('kadaluarsa')
                  ->where('kadaluarsa', '<=', now()->addDays(30));
        } else {
            // Default: Show both alerts
            $query->where(function($q) {
                $q->where(function($sq) {
                    $sq->where('stok', '<=', 10)->orWhereNull('stok');
                })
                ->orWhere(function($sq) {
                    $sq->whereNotNull('kadaluarsa')
                       ->where('kadaluarsa', '<=', now()->addDays(30));
                });
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query->whereHas('product', function($q) use ($search) {
                $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('kategori_id', $request->category_id);
            });
        }
        $alerts = $query->paginate(10)->withQueryString();
        
        // Count for stats
        $baseStatsQuery = ProductStore::where(function($q) {
            $q->where('status_aktif', true)->orWhereNull('status_aktif');
        });

        if (!$user->isOwner()) {
            $baseStatsQuery->where('store_id', $user->store_id);
        } else {
            $selectedStoreId = $request->get('store_id');
            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $baseStatsQuery->where('store_id', $selectedStoreId);
            }
        }
        
        $stok_habis_count = (clone $baseStatsQuery)->where(function($q) {
            $q->where('stok', '<=', 10)->orWhereNull('stok');
        })->count();
        $expired_count = (clone $baseStatsQuery)->whereNotNull('kadaluarsa')
                                               ->where('kadaluarsa', '<=', now()->addDays(30))->count();

        $categories = Category::all();
        $stores = $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]);

        return view('product.index', [
            'active_tab' => 'request',
            'alerts' => $alerts,
            'categories' => $categories,
            'stores' => $stores,
            'selected_store_id' => $selectedStoreId ?? 'all',
            'stok_habis_count' => $stok_habis_count,
            'expired_count' => $expired_count,
            'all_products' => Product::all()
        ]);
    }

    public function updateStoreData(Request $request, $uuid)
    {
        $productStore = ProductStore::findOrFail($uuid);
        
        $request->validate([
            'stok' => 'required|integer',
            'kadaluarsa' => 'nullable|date',
        ]);

        $oldStok = $productStore->stok;
        $productStore->update([
            'stok' => $request->stok,
            'kadaluarsa' => $request->kadaluarsa,
        ]);

        // Log to stock card if stock changed
        if ($oldStok != $request->stok) {
            StockCard::create([
                'product_id' => $productStore->product_id,
                'store_id' => $productStore->store_id,
                'jmlh' => $request->stok - $oldStok,
                'keterangan' => 'Penyesuaian stok manual di menu Stok & Expired',
            ]);
        }

        return redirect()->back()->with('success', 'Data stok dan kadaluarsa berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:category,uuid',
            'harga_modal' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
        ]);

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            $cloudinaryUrl = LandingController::uploadToCloudinary($base64Image, 'products');
            
            if ($cloudinaryUrl) {
                $imageUrl = $cloudinaryUrl;
            } else {
                // Fallback to local
                @list(, $fileData) = explode(';', $base64Image);
                @list(, $fileData) = explode(',', $fileData);
                $imageBinary = base64_decode($fileData);
                $fileName = \Illuminate\Support\Str::uuid() . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put('products/' . $fileName, $imageBinary);
                $imageUrl = 'products/' . $fileName;
            }
        }

        $product = Product::create([
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
            'image_url' => $imageUrl,
        ]);

        // Save Price Levels (Grosir)
        if ($request->has('price_levels')) {
            foreach ($request->price_levels as $level) {
                if ($level['jmlh'] > 0 && $level['harga'] > 0) {
                    PriceLevel::create([
                        'product_id' => $product->uuid,
                        'jmlh' => $level['jmlh'],
                        'harga' => $level['harga'],
                    ]);
                }
            }
        }

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

        $updateData = [
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
        ];

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            \Illuminate\Support\Facades\Log::info('Updating product image for: ' . $product->uuid);
            
            $cloudinaryUrl = LandingController::uploadToCloudinary($base64Image, 'products');

            if ($cloudinaryUrl) {
                $updateData['image_url'] = $cloudinaryUrl;
                \Illuminate\Support\Facades\Log::info('Cloudinary upload success: ' . $cloudinaryUrl);
            } else {
                \Illuminate\Support\Facades\Log::info('Falling back to local storage for product image');
                @list(, $fileData) = explode(';', $base64Image);
                @list(, $fileData) = explode(',', $fileData);
                
                if ($fileData) {
                    $imageBinary = base64_decode($fileData);
                    $fileName = \Illuminate\Support\Str::uuid() . '.png';
                    $newPath = 'products/' . $fileName;
                    
                    // Deletion logic with path normalization
                    if ($product->image_url && !str_starts_with($product->image_url, 'http')) {
                        $oldPath = ltrim(str_replace(['storage/', '/storage/'], '', $product->image_url), '/');
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                            \Illuminate\Support\Facades\Log::info('Old local image deleted: ' . $oldPath);
                        }
                    }

                    \Illuminate\Support\Facades\Storage::disk('public')->put($newPath, $imageBinary);
                    $updateData['image_url'] = '/storage/' . $newPath;
                    \Illuminate\Support\Facades\Log::info('New local image saved: /storage/' . $newPath);
                } else {
                    \Illuminate\Support\Facades\Log::warning('Invalid base64 image data received for product: ' . $product->uuid);
                }
            }
        }

        $product->update($updateData);

        // Update Price Levels (Grosir) - Simple Sync
        PriceLevel::where('product_id', $product->uuid)->delete();
        if ($request->has('price_levels')) {
            foreach ($request->price_levels as $level) {
                if ($level['jmlh'] > 0 && $level['harga'] > 0) {
                    PriceLevel::create([
                        'product_id' => $product->uuid,
                        'jmlh' => $level['jmlh'],
                        'harga' => $level['harga'],
                    ]);
                }
            }
        }

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
        $title = "Laporan " . ($tab == 'produk' ? 'Produk' : ($tab == 'opname' ? 'Stock Opname' : 'Stok & Expired'));

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
            $selectedStoreId = $request->get('store_id');
            
            if (!$user->isOwner()) {
                $selectedStoreId = $user->store_id;
            }

            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $query->whereHas('stores', function($q) use ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId)->where('status_aktif', true);
                });
            } else {
                $query->whereHas('stores', function($q) {
                    $q->where('status_aktif', true);
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
            $query = ProductStore::with(['product.category', 'store'])->where('status_aktif', true);
            if (!$user->isOwner()) $query->where('store_id', $user->store_id);
            if ($request->search) {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('nama_produk', 'ilike', '%' . $request->search . '%');
                });
            }
            if ($request->type == 'stok_habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->type == 'expired') {
                $query->whereNotNull('kadaluarsa')->where('kadaluarsa', '<=', now()->addDays(30));
            }
            return $query->get();
        }

        return collect();
    }

    private function getExportColumns($tab)
    {
        if ($tab == 'produk') return ['Nama Produk', 'Barcode', 'Kategori', 'Harga Modal', 'Harga Jual', 'Stok'];
        if ($tab == 'opname') return ['No Ref', 'Tanggal', 'User', 'Outlet', 'Total Item', 'Total Selisih', 'Status'];
        if ($tab == 'request') return ['Produk', 'Outlet', 'Stok', 'Kadaluarsa', 'Kategori'];
        return [];
    }

    private function formatExportRow($item, $tab)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($tab == 'produk') {
            $selectedStoreId = request('store_id');
            $stok = 0;

            if (!$user->isOwner()) {
                $selectedStoreId = $user->store_id;
            }

            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $stok = $item->stores->where('store_id', $selectedStoreId)->first()->stok ?? 0;
            } else {
                $stok = $item->stores->sum('stok');
            }

             return [
                $item->nama_produk,
                $item->barcode,
                $item->category->nama_category ?? '-',
                $item->harga_modal,
                $item->harga_jual,
                $stok
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
                $item->store->nama ?? '-',
                $item->stok,
                $item->kadaluarsa ? \Carbon\Carbon::parse($item->kadaluarsa)->format('d-m-Y') : '-',
                $item->product->category->nama_category ?? '-'
            ];
        }
        return [];
    }
}