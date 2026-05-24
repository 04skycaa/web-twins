<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $parentFitur = \App\Models\Fitur::where('nama', 'Operasional Outlet')->first();
        $sub_menus = $parentFitur ? \App\Models\Fitur::where('parent_id', $parentFitur->id)->orderBy('id')->get() : collect();

        $hasDataOutlet = false;
        $hasKinerjaOutlet = false;
        $hasRiwayatStok = false;

        foreach($sub_menus as $sm) {
            if ($sm->nama == 'Data Outlet' && $user->hasFeature($sm->id)) $hasDataOutlet = true;
            if ($sm->nama == 'Kinerja Outlet' && $user->hasFeature($sm->id)) $hasKinerjaOutlet = true;
            if ($sm->nama == 'Riwayat Stok' && $user->hasFeature($sm->id)) $hasRiwayatStok = true;
        }

        $activeTab = $request->query('active_tab', 'data');
        if ($activeTab == 'data' && !$hasDataOutlet) $activeTab = '';
        if ($activeTab == 'kinerja' && !$hasKinerjaOutlet) $activeTab = '';
        if ($activeTab == 'riwayat' && !$hasRiwayatStok) $activeTab = '';

        if (!$activeTab) {
            if ($hasDataOutlet) $activeTab = 'data';
            elseif ($hasKinerjaOutlet) $activeTab = 'kinerja';
            elseif ($hasRiwayatStok) $activeTab = 'riwayat';
        }

        $outlets = Outlet::with(['users.operator'])->get();
        
        $performanceData = collect();
        $top3All = [];

        // Tab Isolation: Only calculate performance stats if 'kinerja' tab is active
        if ($activeTab === 'kinerja') {
            $cacheKey = 'outlet_performance_data_v2';
            if ($request->get('refresh') == '1') {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($outlets) {
                // Fetch Performance Data per Outlet
                $performanceData = $outlets->map(function($outlet) {
                    // Omset & Volume Transaksi from Transactions (optimized SQL aggregation)
                    $salesQuery = \App\Models\Transaction::where('store_id', $outlet->uuid)
                        ->where('jenis', 'penjualan')
                        ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);

                    $omset = $salesQuery->sum('total');
                    $volumeTransaksi = $salesQuery->count();

                    // Laba Kotor directly via Database Aggregation (extremely fast, no hydrating models)
                    $labaKotor = \App\Models\TransactionDetail::whereHas('transaction', function($q) use ($outlet) {
                            $q->where('store_id', $outlet->uuid)
                              ->where('jenis', 'penjualan')
                              ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
                        })
                        ->select(\DB::raw('SUM((harga_jual - harga_modal) * jmlh) as total_laba'))
                        ->value('total_laba') ?? 0;

                    // Pemasukan & Pengeluaran directly via Database aggregation
                    $pemasukan = \App\Models\CashFlow::where('store_id', $outlet->uuid)
                        ->where('jenis', 'pemasukan')
                        ->sum('nominal');
                    $pengeluaran = \App\Models\CashFlow::where('store_id', $outlet->uuid)
                        ->where('jenis', 'pengeluaran')
                        ->sum('nominal');

                    // Laba Bersih
                    $labaBersih = $labaKotor - $pengeluaran;

                    // 1. Get POS Top Product (transaction_detail)
                    $posSales = \App\Models\TransactionDetail::whereHas('transaction', function($q) use ($outlet) {
                            $q->where('store_id', $outlet->uuid)
                              ->where('jenis', 'penjualan')
                              ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
                        })
                        ->select('product_id', \DB::raw('SUM(jmlh) as qty'))
                        ->groupBy('product_id')
                        ->orderByDesc('qty')
                        ->take(3)
                        ->pluck('qty', 'product_id');

                    // 2. Get Online Top Product (payment_order_items)
                    $onlineSales = \App\Models\PaymentOrderItem::whereHas('paymentOrder', function($query) use ($outlet) {
                            $query->where('outlet_id', $outlet->uuid)
                                  ->whereIn('payment_status', ['paid', 'settlement', 'success']);
                        })
                        ->select('product_id', \DB::raw('SUM(quantity) as qty'))
                        ->groupBy('product_id')
                        ->orderByDesc('qty')
                        ->take(3)
                        ->pluck('qty', 'product_id');

                    // Combine both sources
                    $allProductIds = $posSales->keys()->concat($onlineSales->keys())->unique();
                    $mergedSales = $allProductIds->mapWithKeys(function($id) use ($posSales, $onlineSales) {
                        return [$id => (float)($posSales->get($id, 0) + $onlineSales->get($id, 0))];
                    })->sortDesc();

                    // Bulk fetch products to avoid N+1 query
                    $productIds = $mergedSales->take(3)->keys()->toArray();
                    $products = \App\Models\Product::whereIn('uuid', $productIds)->get()->keyBy('uuid');

                    $top3 = $mergedSales->take(3)->map(function($qty, $id) use ($products) {
                        $prod = $products->get($id);
                        if (!$prod) return null;
                        return [
                            'nama' => $prod->nama_produk,
                            'image' => $prod->resolved_image_url,
                            'qty' => $qty
                        ];
                    })->filter()->values()->toArray();

                    // Nilai Aset Stok via optimized single join query
                    $nilaiAset = \App\Models\ProductStore::where('store_id', $outlet->uuid)
                        ->join('products', 'product_store.product_id', '=', 'products.uuid')
                        ->select(\DB::raw('SUM(product_store.stok * COALESCE(products.harga_modal, 0)) as total_asset'))
                        ->value('total_asset') ?? 0;

                    return [
                        'outlet_uuid' => $outlet->uuid,
                        'nama' => $outlet->nama,
                        'omset' => $omset,
                        'laba_kotor' => $labaKotor,
                        'laba_bersih' => $labaBersih,
                        'pemasukan' => $pemasukan,
                        'pengeluaran' => $pengeluaran,
                        'volume_transaksi' => $volumeTransaksi,
                        'top_products' => $top3,
                        'nilai_aset' => $nilaiAset
                    ];
                });

                // Global Top Product (All Outlets) - Merged POS & Online
                $posSalesAll = \App\Models\TransactionDetail::whereHas('transaction', function($q) {
                        $q->where('jenis', 'penjualan')
                          ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
                    })
                    ->select('product_id', \DB::raw('SUM(jmlh) as qty'))
                    ->groupBy('product_id')
                    ->orderByDesc('qty')
                    ->take(3)
                    ->pluck('qty', 'product_id');

                $onlineSalesAll = \App\Models\PaymentOrderItem::whereHas('paymentOrder', function($q) {
                        $q->whereIn('payment_status', ['paid', 'settlement', 'success']);
                    })
                    ->select('product_id', \DB::raw('SUM(quantity) as qty'))
                    ->groupBy('product_id')
                    ->orderByDesc('qty')
                    ->take(3)
                    ->pluck('qty', 'product_id');

                $allIdsAll = $posSalesAll->keys()->concat($onlineSalesAll->keys())->unique();
                $mergedAll = $allIdsAll->mapWithKeys(function($id) use ($posSalesAll, $onlineSalesAll) {
                    return [$id => (float)($posSalesAll->get($id, 0) + $onlineSalesAll->get($id, 0))];
                })->sortDesc();

                // Bulk fetch global top products to avoid N+1 query
                $productIdsAll = $mergedAll->take(3)->keys()->toArray();
                $productsAll = \App\Models\Product::whereIn('uuid', $productIdsAll)->get()->keyBy('uuid');

                $top3All = $mergedAll->take(3)->map(function($qty, $id) use ($productsAll) {
                    $prod = $productsAll->get($id);
                    if (!$prod) return null;
                    return [
                        'nama' => $prod->nama_produk,
                        'image' => $prod->resolved_image_url,
                        'qty' => $qty
                    ];
                })->filter()->values()->toArray();

                return ['performanceData' => $performanceData->toArray(), 'top3All' => $top3All];
            });

            $performanceData = collect($cachedData['performanceData'] ?? []);
            $top3All = $cachedData['top3All'] ?? [];
        }

        // Tab Isolation: Only query stock history if 'riwayat' tab or AJAX filtering is active
        $stockHistory = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        if ($activeTab === 'riwayat' || $request->ajax()) {
            $stockHistoryQuery = \App\Models\StockCard::with(['product', 'store'])
                ->orderBy('created_at', 'desc');

            if ($request->has('search') && $request->search != '') {
                $search = strtolower($request->search);
                $stockHistoryQuery->where(function($q) use ($search) {
                    $q->whereHas('product', function($sq) use ($search) {
                        $sq->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$search}%"]);
                    })->orWhereRaw('LOWER(keterangan) LIKE ?', ["%{$search}%"]);
                });
            }

            if ($request->has('store_id') && $request->store_id != 'all' && $request->store_id != '') {
                $stockHistoryQuery->where('store_id', $request->store_id);
            }

            if ($request->has('start_date') && $request->start_date != '') {
                $stockHistoryQuery->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date != '') {
                $stockHistoryQuery->whereDate('created_at', '<=', $request->end_date);
            }

            $stockHistory = $stockHistoryQuery->paginate(10)->withQueryString();
        }

        if ($request->ajax()) {
            return view('outlet.index', [
                'outlets' => $outlets,
                'stockHistory' => $stockHistory,
                'active_tab' => 'riwayat',
                'performanceData' => $performanceData,
                'topProductsAll' => $top3All,
                'hasDataOutlet' => $hasDataOutlet,
                'hasKinerjaOutlet' => $hasKinerjaOutlet,
                'hasRiwayatStok' => $hasRiwayatStok,
                'sub_menus' => $sub_menus
            ])->fragment('stock-history-table');
        }

        return view('outlet.index', [
            'outlets' => $outlets,
            'active_tab' => $activeTab,
            'performanceData' => $performanceData,
            'topProductsAll' => $top3All,
            'stockHistory' => $stockHistory,
            'hasDataOutlet' => $hasDataOutlet,
            'hasKinerjaOutlet' => $hasKinerjaOutlet,
            'hasRiwayatStok' => $hasRiwayatStok,
            'sub_menus' => $sub_menus
        ]);
    }

    /**
     * API endpoint: mengambil statistik real-time untuk side panel Data Outlet.
     * Menggabungkan data POS (transactions) + Online (payment_orders).
     */
    public function getOutletStats($uuid)
    {
        $outlet = Outlet::with(['users.operator'])->where('uuid', $uuid)->firstOrFail();

        // --- OMZET & TRANSAKSI ---
        // POS (kasir)
        $posOmzet       = \App\Models\Transaction::where('store_id', $uuid)
            ->where('jenis', 'penjualan')
            ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui'])
            ->sum('total');

        $posTrxCount    = \App\Models\Transaction::where('store_id', $uuid)
            ->where('jenis', 'penjualan')
            ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui'])
            ->count();

        // Online (payment_orders)
        $onlineOmzet    = \App\Models\PaymentOrder::where('outlet_id', $uuid)
            ->whereIn('payment_status', ['paid', 'settlement', 'success'])
            ->sum('total_amount');

        $onlineTrxCount = \App\Models\PaymentOrder::where('outlet_id', $uuid)
            ->whereIn('payment_status', ['paid', 'settlement', 'success'])
            ->count();

        $totalOmzet     = (float)$posOmzet + (float)$onlineOmzet;
        $totalTrx       = $posTrxCount + $onlineTrxCount;

        // --- PRODUK TERLARIS (gabung POS + Online) ---
        $posSales = \App\Models\TransactionDetail::whereHas('transaction', function($q) use ($uuid) {
                $q->where('store_id', $uuid)
                  ->where('jenis', 'penjualan')
                  ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            })
            ->select('product_id', \DB::raw('SUM(jmlh) as qty'))
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->take(5)
            ->pluck('qty', 'product_id');

        $onlineSales = \App\Models\PaymentOrderItem::whereHas('paymentOrder', function($q) use ($uuid) {
                $q->where('outlet_id', $uuid)
                  ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            })
            ->select('product_id', \DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->take(5)
            ->pluck('qty', 'product_id');

        $allIds = $posSales->keys()->concat($onlineSales->keys())->unique();
        $merged = $allIds->mapWithKeys(fn($id) => [
            $id => (float)($posSales->get($id, 0) + $onlineSales->get($id, 0))
        ])->sortDesc();

        $topProductId  = $merged->keys()->first();
        $topProductQty = $merged->first() ?? 0;
        $topProductName = '-';

        if ($topProductId) {
            $prod = \App\Models\Product::where('uuid', $topProductId)->first();
            $topProductName = $prod ? $prod->nama_produk : '-';
        }

        // --- STOK MENIPIS ---
        // Produk dengan stok ≤ stok_minimum (atau ≤ 5 jika minimum tidak diset)
        $lowStockCount = \App\Models\ProductStore::where('store_id', $uuid)
            ->where('status_aktif', true)
            ->where(function($q) {
                $q->whereRaw('stok <= COALESCE(stok_minimum, 5)')
                  ->orWhere('stok', '<=', 0);
            })
            ->count();

        // --- INFO OUTLET ---
        $kepalaUser = $outlet->users->first(fn($u) => optional($u->operator)->nama === 'Kepala Toko'
            || optional($u->operator)->nama === 'kepala_toko');

        return response()->json([
            'nama'           => $outlet->nama,
            'status_aktif'   => $outlet->status_aktif,
            'alamat'         => $outlet->alamat ?? '-',
            'notelp'         => $outlet->notelp ?? '-',
            'jam_buka'       => $outlet->jam_buka ?? '-',
            'kepala'         => $kepalaUser ? $kepalaUser->username : '-',
            'email'          => $kepalaUser ? $kepalaUser->email : '-',
            'omzet'          => $totalOmzet,
            'omzet_pos'      => (float)$posOmzet,
            'omzet_online'   => (float)$onlineOmzet,
            'total_transaksi'=> $totalTrx,
            'trx_pos'        => $posTrxCount,
            'trx_online'     => $onlineTrxCount,
            'produk_terlaris'=> $topProductName,
            'terlaris_qty'   => (int)$topProductQty,
            'stok_menipis'   => $lowStockCount,
        ]);
    }

    public function kinerja()
    {
        return redirect()->route('outlet.index', ['active_tab' => 'kinerja']);
    }

    public function riwayat()
    {
        return redirect()->route('outlet.index', ['active_tab' => 'riwayat']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
            'jam_buka' => 'nullable|string|max:255',
        ]);

        Outlet::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'jam_buka' => $request->jam_buka ?? '08.00 - 23.59',
            'status_aktif' => true,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
            'jam_buka' => 'nullable|string|max:255',
        ]);

        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        
        $outlet->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'jam_buka' => $request->jam_buka,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function toggleStatus($uuid)
    {
        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        $outlet->status_aktif = !$outlet->status_aktif;
        $outlet->save();

        $status = $outlet->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('outlet.index')->with('success', "Outlet berhasil $status");
    }

    public function destroy($uuid)
    {
        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        $outlet->delete();
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }
}
