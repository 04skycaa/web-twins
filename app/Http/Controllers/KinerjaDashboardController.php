<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Outlet;
use Carbon\Carbon;

class KinerjaDashboardController extends Controller
{
    /**
     * Membangun cache key dinamis berdasarkan filter
     */
    private function getCacheKey($prefix, Request $request)
    {
        $storeId = $request->input('store_id', 'all');
        $startDate = $request->input('start_date', 'all');
        $endDate = $request->input('end_date', 'all');
        return "kinerja_{$prefix}_{$storeId}_{$startDate}_{$endDate}";
    }

    /**
     * Terapkan filter tanggal dan outlet ke query builder secara generic
     */
    private function applyFilters($query, Request $request, $dateColumn = 'tanggal', $storeColumn = 'store_id')
    {
        if ($request->has('store_id') && $request->store_id !== 'all' && $request->store_id !== '') {
            $query->where($storeColumn, $request->store_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate($dateColumn, '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate($dateColumn, '<=', $request->end_date);
        }

        return $query;
    }

    public function getStatistikUtama(Request $request)
    {
        $cacheKey = $this->getCacheKey('statistik_utama', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            // Omset POS
            $posOmzetQuery = DB::table('transactions')
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posOmzetQuery = $this->applyFilters($posOmzetQuery, $request, 'tanggal', 'store_id');
            $posOmzet = $posOmzetQuery->sum('total');

            // Omset Online
            $onlineOmzetQuery = DB::table('payment_orders')
                ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            $onlineOmzetQuery = $this->applyFilters($onlineOmzetQuery, $request, 'created_at', 'outlet_id');
            $onlineOmzet = $onlineOmzetQuery->sum('total_amount');

            $omsetTotal = $posOmzet + $onlineOmzet;

            // Laba Kotor (Hanya dari POS karena harga_modal terekam di transaction_detail, jika online ada bisa disesuaikan, untuk simplicity asumsikan POS merepresentasikan Laba Kotor proporsional atau kita hitung detailnya)
            $labaKotorQuery = DB::table('transaction_detail')
                ->join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
                ->where('transactions.jenis', 'penjualan')
                ->whereIn('transactions.status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $labaKotorQuery = $this->applyFilters($labaKotorQuery, $request, 'transactions.tanggal', 'transactions.store_id');
            $labaKotorPos = $labaKotorQuery->sum(DB::raw('(transaction_detail.harga_jual - transaction_detail.harga_modal) * transaction_detail.jmlh'));

            // Pengeluaran Operasional (Dari cash_flows)
            $pengeluaranQuery = DB::table('cash_flows')
                ->where('jenis', 'pengeluaran');
            $pengeluaranQuery = $this->applyFilters($pengeluaranQuery, $request, 'tanggal', 'store_id');
            $totalPengeluaran = $pengeluaranQuery->sum('nominal');

            // Laba Bersih = Laba Kotor - Pengeluaran
            // Catatan: Ini perhitungan simplifikasi, Laba bersih aktual online harus ditambah laba kotor online jika terhubung ke harga_modal.
            // Jika Laba kotor online = (final_price - harga_modal)*qty
            $labaKotorOnlineQuery = DB::table('payment_order_items')
                ->join('payment_orders', 'payment_order_items.payment_order_id', '=', 'payment_orders.id')
                ->join('products', 'payment_order_items.product_id', '=', DB::raw('CAST(products.uuid AS varchar)'))
                ->whereIn('payment_orders.payment_status', ['paid', 'settlement', 'success']);
            $labaKotorOnlineQuery = $this->applyFilters($labaKotorOnlineQuery, $request, 'payment_orders.created_at', 'payment_orders.outlet_id');
            
            // Cast final_price to numeric just in case
            $labaKotorOnline = $labaKotorOnlineQuery->sum(DB::raw('(payment_order_items.final_price - COALESCE(products.harga_modal, 0)) * payment_order_items.quantity'));

            $totalLabaKotor = $labaKotorPos + $labaKotorOnline;
            $labaBersih = $totalLabaKotor - $totalPengeluaran;

            return [
                'omset' => $omsetTotal,
                'laba_kotor' => $totalLabaKotor,
                'laba_bersih' => $labaBersih,
                'trend_omset' => 0, // placeholder untuk persentase
                'trend_laba_kotor' => 0,
                'trend_laba_bersih' => 0
            ];
        });

        return response()->json($cachedData);
    }

    public function getArusKas(Request $request)
    {
        $cacheKey = $this->getCacheKey('arus_kas', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            // Pemasukan Penjualan
            $posOmzetQuery = DB::table('transactions')
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posOmzetQuery = $this->applyFilters($posOmzetQuery, $request, 'tanggal', 'store_id');
            $pemasukanPos = $posOmzetQuery->sum('total');

            $onlineOmzetQuery = DB::table('payment_orders')
                ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            $onlineOmzetQuery = $this->applyFilters($onlineOmzetQuery, $request, 'created_at', 'outlet_id');
            $pemasukanOnline = $onlineOmzetQuery->sum('total_amount');

            $pemasukanLainQuery = DB::table('cash_flows')
                ->where('jenis', 'pemasukan');
            $pemasukanLainQuery = $this->applyFilters($pemasukanLainQuery, $request, 'tanggal', 'store_id');
            $pemasukanLain = $pemasukanLainQuery->sum('nominal');

            $totalPemasukan = $pemasukanPos + $pemasukanOnline + $pemasukanLain;

            // Pembelian Stok
            $pembelianQuery = DB::table('transactions')
                ->where('jenis', 'pembelian')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $pembelianQuery = $this->applyFilters($pembelianQuery, $request, 'tanggal', 'store_id');
            $pembelianStok = $pembelianQuery->sum('total');

            // Biaya Operasional & Pengeluaran Lain dari cash flows
            // Sederhananya kita gabung di cash_flows sebagai biaya operasional
            $pengeluaranQuery = DB::table('cash_flows')
                ->where('jenis', 'pengeluaran');
            $pengeluaranQuery = $this->applyFilters($pengeluaranQuery, $request, 'tanggal', 'store_id');
            $biayaOperasional = $pengeluaranQuery->sum('nominal');

            return [
                'pemasukan_penjualan' => $totalPemasukan,
                'pembelian_stok' => $pembelianStok,
                'biaya_operasional' => $biayaOperasional,
                'pengeluaran_lain' => 0 // Jika ada kategori pengeluaran lain di cash_flows bisa dipisah
            ];
        });

        return response()->json($cachedData);
    }

    public function getRingkasanPerforma(Request $request)
    {
        $cacheKey = $this->getCacheKey('ringkasan_performa', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            // Total Transaksi
            $posTrxQuery = DB::table('transactions')
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posTrxQuery = $this->applyFilters($posTrxQuery, $request, 'tanggal', 'store_id');
            $totalTrxPos = $posTrxQuery->count();

            $onlineTrxQuery = DB::table('payment_orders')
                ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            $onlineTrxQuery = $this->applyFilters($onlineTrxQuery, $request, 'created_at', 'outlet_id');
            $totalTrxOnline = $onlineTrxQuery->count();

            $totalTransaksi = $totalTrxPos + $totalTrxOnline;

            // Omset
            $posOmzetQuery = DB::table('transactions')
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posOmzetQuery = $this->applyFilters($posOmzetQuery, $request, 'tanggal', 'store_id');
            $omsetTotal = $posOmzetQuery->sum('total');

            $onlineOmzetQuery = DB::table('payment_orders')
                ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            $onlineOmzetQuery = $this->applyFilters($onlineOmzetQuery, $request, 'created_at', 'outlet_id');
            $omsetTotal += $onlineOmzetQuery->sum('total_amount');

            $rataRataTransaksi = $totalTransaksi > 0 ? $omsetTotal / $totalTransaksi : 0;

            // Total Item Terjual
            $posItemQuery = DB::table('transaction_detail')
                ->join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
                ->where('transactions.jenis', 'penjualan')
                ->whereIn('transactions.status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posItemQuery = $this->applyFilters($posItemQuery, $request, 'transactions.tanggal', 'transactions.store_id');
            $totalItemTerjual = $posItemQuery->sum('transaction_detail.jmlh');

            $onlineItemQuery = DB::table('payment_order_items')
                ->join('payment_orders', 'payment_order_items.payment_order_id', '=', 'payment_orders.id')
                ->whereIn('payment_orders.payment_status', ['paid', 'settlement', 'success']);
            $onlineItemQuery = $this->applyFilters($onlineItemQuery, $request, 'payment_orders.created_at', 'payment_orders.outlet_id');
            $totalItemTerjual += $onlineItemQuery->sum('payment_order_items.quantity');

            // Total Pengeluaran
            $biayaOperasionalQuery = DB::table('cash_flows')
                ->where('jenis', 'pengeluaran');
            $biayaOperasionalQuery = $this->applyFilters($biayaOperasionalQuery, $request, 'tanggal', 'store_id');
            $totalPengeluaran = $biayaOperasionalQuery->sum('nominal');

            $pembelianStokQuery = DB::table('transactions')
                ->where('jenis', 'pembelian')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $pembelianStokQuery = $this->applyFilters($pembelianStokQuery, $request, 'tanggal', 'store_id');
            $totalPengeluaran += $pembelianStokQuery->sum('total');

            return [
                'total_transaksi' => $totalTransaksi,
                'rata_rata_transaksi' => $rataRataTransaksi,
                'total_item_terjual' => $totalItemTerjual,
                'total_pengeluaran' => $totalPengeluaran
            ];
        });

        return response()->json($cachedData);
    }

    public function getGrafikPenjualan(Request $request)
    {
        $cacheKey = $this->getCacheKey('grafik_penjualan', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            // Kita ambil data 7 hari terakhir secara default jika tidak ada filter
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subDays(6);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

            $dates = [];
            $current = $startDate->copy();
            while($current <= $endDate) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }

            // POS
            $posQuery = DB::table('transactions')
                ->select(DB::raw('CAST(tanggal AS DATE) as date'), DB::raw('SUM(total) as total'))
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            $posQuery = $this->applyFilters($posQuery, $request, 'tanggal', 'store_id');
            
            // If specific dates not set in filter, force last 7 days for the query
            if (!$request->filled('start_date')) {
                $posQuery->whereDate('tanggal', '>=', $startDate->format('Y-m-d'));
            }

            $posData = $posQuery->groupBy(DB::raw('CAST(tanggal AS DATE)'))->pluck('total', 'date')->toArray();

            // Online
            $onlineQuery = DB::table('payment_orders')
                ->select(DB::raw('CAST(created_at AS DATE) as date'), DB::raw('SUM(total_amount) as total'))
                ->whereIn('payment_status', ['paid', 'settlement', 'success']);
            $onlineQuery = $this->applyFilters($onlineQuery, $request, 'created_at', 'outlet_id');
            
            if (!$request->filled('start_date')) {
                $onlineQuery->whereDate('created_at', '>=', $startDate->format('Y-m-d'));
            }

            $onlineData = $onlineQuery->groupBy(DB::raw('CAST(created_at AS DATE)'))->pluck('total', 'date')->toArray();

            $labels = [];
            $values = [];

            foreach ($dates as $date) {
                $labels[] = Carbon::parse($date)->format('d M');
                $posVal = $posData[$date] ?? 0;
                $onlineVal = $onlineData[$date] ?? 0;
                $values[] = $posVal + $onlineVal;
            }

            return [
                'labels' => $labels,
                'data' => $values,
                'dataset_label' => 'Penjualan'
            ];
        });

        return response()->json($cachedData);
    }

    public function getProdukTerlaris(Request $request)
    {
        $cacheKey = $this->getCacheKey('produk_terlaris', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            $posSalesQuery = DB::table('transaction_detail')
                ->join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
                ->select('transaction_detail.product_id', DB::raw('SUM(transaction_detail.jmlh) as qty'))
                ->where('transactions.jenis', 'penjualan')
                ->whereIn('transactions.status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            
            $posSalesQuery = $this->applyFilters($posSalesQuery, $request, 'transactions.tanggal', 'transactions.store_id');
            $posSales = $posSalesQuery->groupBy('transaction_detail.product_id')->pluck('qty', 'product_id');

            $onlineSalesQuery = DB::table('payment_order_items')
                ->join('payment_orders', 'payment_order_items.payment_order_id', '=', 'payment_orders.id')
                ->select('payment_order_items.product_id', DB::raw('SUM(payment_order_items.quantity) as qty'))
                ->whereIn('payment_orders.payment_status', ['paid', 'settlement', 'success']);
            
            $onlineSalesQuery = $this->applyFilters($onlineSalesQuery, $request, 'payment_orders.created_at', 'payment_orders.outlet_id');
            $onlineSales = $onlineSalesQuery->groupBy('payment_order_items.product_id')->pluck('qty', 'product_id');

            // Combine
            $allProductIds = collect(array_keys($posSales->toArray()))->concat(array_keys($onlineSales->toArray()))->unique();
            $mergedSales = $allProductIds->mapWithKeys(function($id) use ($posSales, $onlineSales) {
                return [$id => (float)($posSales->get($id, 0) + $onlineSales->get($id, 0))];
            })->sortDesc()->take(5);

            $products = DB::table('products')->whereIn('uuid', $mergedSales->keys())->get()->keyBy('uuid');

            $result = [];
            $maxQty = $mergedSales->first() ?? 1;

            foreach ($mergedSales as $id => $qty) {
                $prod = $products->get($id);
                if ($prod) {
                    $result[] = [
                        'nama' => $prod->nama_produk,
                        'qty' => $qty,
                        'percentage' => ($qty / $maxQty) * 100
                    ];
                }
            }

            return $result;
        });

        return response()->json($cachedData);
    }

    public function getPerformaOutlet(Request $request)
    {
        // Karena ada pagination, cache key harus mencakup parameter page
        $page = $request->input('page', 1);
        $cacheKey = $this->getCacheKey('performa_outlet_p' . $page, $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            $outletsQuery = DB::table('store')->where('status_aktif', true);
            
            if ($request->has('store_id') && $request->store_id !== 'all') {
                $outletsQuery->where('uuid', $request->store_id);
            }

            $outlets = $outletsQuery->paginate(10);
            
            $data = [];
            foreach ($outlets as $outlet) {
                // POS
                $posSalesQuery = DB::table('transactions')
                    ->where('store_id', $outlet->uuid)
                    ->where('jenis', 'penjualan')
                    ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
                
                if ($request->filled('start_date')) {
                    $posSalesQuery->whereDate('tanggal', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $posSalesQuery->whereDate('tanggal', '<=', $request->end_date);
                }

                $omsetPos = $posSalesQuery->sum('total');
                $trxPos = $posSalesQuery->count();

                // Laba Kotor Pos
                $labaKotorQuery = DB::table('transaction_detail')
                    ->join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
                    ->where('transactions.store_id', $outlet->uuid)
                    ->where('transactions.jenis', 'penjualan')
                    ->whereIn('transactions.status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
                
                if ($request->filled('start_date')) {
                    $labaKotorQuery->whereDate('transactions.tanggal', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $labaKotorQuery->whereDate('transactions.tanggal', '<=', $request->end_date);
                }
                
                $labaKotorPos = $labaKotorQuery->sum(DB::raw('(transaction_detail.harga_jual - transaction_detail.harga_modal) * transaction_detail.jmlh'));

                // Online
                $onlineSalesQuery = DB::table('payment_orders')
                    ->where('outlet_id', $outlet->uuid)
                    ->whereIn('payment_status', ['paid', 'settlement', 'success']);
                
                if ($request->filled('start_date')) {
                    $onlineSalesQuery->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $onlineSalesQuery->whereDate('created_at', '<=', $request->end_date);
                }

                $omsetOnline = $onlineSalesQuery->sum('total_amount');
                $trxOnline = $onlineSalesQuery->count();

                $omset = $omsetPos + $omsetOnline;
                $transaksi = $trxPos + $trxOnline;

                // Pengeluaran
                $pengeluaranQuery = DB::table('cash_flows')
                    ->where('store_id', $outlet->uuid)
                    ->where('jenis', 'pengeluaran');
                
                if ($request->filled('start_date')) {
                    $pengeluaranQuery->whereDate('tanggal', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $pengeluaranQuery->whereDate('tanggal', '<=', $request->end_date);
                }
                $pengeluaran = $pengeluaranQuery->sum('nominal');

                $labaBersih = $labaKotorPos - $pengeluaran; // Simplified

                $data[] = [
                    'nama_outlet' => $outlet->nama,
                    'omset' => $omset,
                    'jumlah_transaksi' => $transaksi,
                    'laba_bersih' => $labaBersih,
                    'performa_naik' => true // dummy logic, bisa dikembangkan
                ];
            }

            return [
                'data' => $data,
                'current_page' => $outlets->currentPage(),
                'last_page' => $outlets->lastPage()
            ];
        });

        return response()->json($cachedData);
    }

    public function getOutletPerhatian(Request $request)
    {
        $cacheKey = $this->getCacheKey('outlet_perhatian', $request);
        
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            $alerts = [];
            
            // Cari stok menipis
            $lowStockQuery = DB::table('product_store')
                ->join('store', 'product_store.store_id', '=', 'store.uuid')
                ->where('product_store.status_aktif', true)
                ->whereRaw('product_store.stok <= COALESCE(product_store.stok_minimum, 5)');
            
            if ($request->has('store_id') && $request->store_id !== 'all') {
                $lowStockQuery->where('product_store.store_id', $request->store_id);
            }

            $lowStockCount = $lowStockQuery->count();
            if ($lowStockCount > 0) {
                $alerts[] = [
                    'tipe' => 'stok_menipis',
                    'pesan' => "Ada $lowStockCount item produk dengan stok menipis.",
                    'warna' => 'warning'
                ];
            }

            // Cari toko belum ada transaksi hari ini
            if (!$request->filled('start_date')) {
                $today = Carbon::today()->format('Y-m-d');
                $stores = DB::table('store')->where('status_aktif', true);
                if ($request->has('store_id') && $request->store_id !== 'all') {
                    $stores->where('uuid', $request->store_id);
                }
                
                $storeIds = $stores->pluck('uuid');
                
                $trxStoreIds = DB::table('transactions')
                    ->whereDate('tanggal', $today)
                    ->whereIn('store_id', $storeIds)
                    ->pluck('store_id')->unique()->toArray();
                
                $noTrxCount = $storeIds->count() - count($trxStoreIds);
                if ($noTrxCount > 0) {
                    $alerts[] = [
                        'tipe' => 'belum_ada_transaksi',
                        'pesan' => "$noTrxCount outlet belum mencatat transaksi hari ini.",
                        'warna' => 'danger'
                    ];
                }
            }

            if (empty($alerts)) {
                $alerts[] = [
                    'tipe' => 'aman',
                    'pesan' => "Semua outlet beroperasi dengan normal.",
                    'warna' => 'success'
                ];
            }

            return $alerts;
        });

        return response()->json($cachedData);
    }
}
