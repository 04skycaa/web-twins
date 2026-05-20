<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Contact;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Models\CashFlow;
use App\Models\Debt;
use App\Models\PaymentOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $storeId = $request->get('store_id');
        if ($user->role !== 'owner') {
            $storeId = $user->store_id;
        }

        // If AJAX request, return the dynamic chart/cashflow data
        if ($request->ajax()) {
            $preset = $request->get('preset');
            $type = $request->get('type', 'main');
            
            if ($type === 'main') {
                $yearFrom = $request->get('year_from');
                $yearTo = $request->get('year_to');
                return response()->json($this->getMainChartPresetData($preset, $storeId, $today, $yearFrom, $yearTo));
            } else {
                return response()->json($this->getCashFlowPresetData($preset, $storeId));
            }
        }

        $stores = Outlet::all();

        $cacheKey = 'dashboard_v8_data_' . ($storeId ?? 'all') . '_' . $user->role . '_' . ($user->isOwner() ? 'owner' : $user->store_id);

        if ($request->get('refresh') == '1') {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function() use ($storeId, $today, $yesterday, $user) {
            $stats = $this->getStats($storeId, $today, $yesterday);

            // 1. SALES CHART DATA (Lazy load non-active presets)
            $chartHarian = $this->getMainChartPresetData('harian', $storeId, $today);
            $chartMingguan = ['labels' => [], 'offline' => [], 'online' => []];
            $chartBulanan = ['labels' => [], 'offline' => [], 'online' => []];
            $chartTahunan = ['labels' => [], 'offline' => [], 'online' => []];

            // 2. CASHFLOW DATA PRESETS (All lazy loaded - fetched via AJAX on demand)
            $cfHarian = ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]];
            $cfMingguan = ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]];
            $cfBulanan = ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]];
            $cfTahunan = ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]];

            // 3. OTHER WIDGETS
            $totalPiutang = Debt::where('tipe', 'piutang')->where('sisa', '>', 0)->when($storeId, fn($q) => $q->where('store_id', $storeId))->sum('sisa');
            $totalHutang = Debt::whereIn('tipe', ['utang', 'hutang'])->where('sisa', '>', 0)->when($storeId, fn($q) => $q->where('store_id', $storeId))->sum('sisa');

            // Added limits to prevent pulling massive numbers of models in memory
            $lowStockProducts = ProductStore::with(['product', 'store'])
                ->where('stok', '<=', DB::raw('COALESCE(stok_minimum, 10)'))
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->orderBy('stok', 'asc')
                ->limit(20)
                ->get();

            $expiredProducts = ProductStore::with(['product', 'store'])
                ->whereNotNull('kadaluarsa')
                ->where('kadaluarsa', '<=', Carbon::now()->addDays(30))
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->orderBy('kadaluarsa', 'asc')
                ->limit(20)
                ->get();
            
            // Replaced whereHas with index-friendly joins for performance
            $topOffline = TransactionDetail::join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
                ->select('transaction_detail.product_id', DB::raw('SUM(transaction_detail.jmlh) as total_qty'), DB::raw('SUM(transaction_detail.jmlh * transaction_detail.harga_jual) as total_revenue'))
                ->whereBetween('transactions.tanggal', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
                ->groupBy('transaction_detail.product_id')
                ->get();

            $topOnline = PaymentOrderItem::join('payment_orders', 'payment_order_items.payment_order_id', '=', 'payment_orders.id')
                ->select('payment_order_items.product_id', DB::raw('SUM(payment_order_items.quantity) as total_qty'), DB::raw('SUM(payment_order_items.subtotal) as total_revenue'))
                ->whereBetween('payment_orders.paid_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->whereNotNull('payment_orders.paid_at')
                ->when($storeId, fn($q) => $q->where('payment_orders.outlet_id', $storeId))
                ->groupBy('payment_order_items.product_id')
                ->get();

            $mergedMap = [];
            foreach ($topOffline as $off) {
                $mergedMap[$off->product_id] = [
                    'product_id' => $off->product_id,
                    'qty' => (int)$off->total_qty,
                    'rev' => (float)$off->total_revenue
                ];
            }
            foreach ($topOnline as $on) {
                if (isset($mergedMap[$on->product_id])) {
                    $mergedMap[$on->product_id]['qty'] += (int)$on->total_qty;
                    $mergedMap[$on->product_id]['rev'] += (float)$on->total_revenue;
                } else {
                    $mergedMap[$on->product_id] = [
                        'product_id' => $on->product_id,
                        'qty' => (int)$on->total_qty,
                        'rev' => (float)$on->total_revenue
                    ];
                }
            }
            $mergedTop = collect(array_values($mergedMap));

            // Eliminated Loop N+1 query: fetch all products in 1 whereIn query instead of 5 separate queries
            $topProductList = $mergedTop->sortByDesc('qty')->take(5);
            $topProductIds = $topProductList->pluck('product_id')->filter()->all();
            $products = Product::whereIn('uuid', $topProductIds)->get()->keyBy('uuid');

            $topProductsArray = $topProductList->map(function($item) use ($products) {
                $p = $products->get($item['product_id']);
                return [
                    'product' => $p ? $p->toArray() : null,
                    'total_qty' => $item['qty'],
                    'total_revenue' => $item['rev']
                ];
            })->values()->all();

            // Fetch only 5 latest from each source at DB level, then merge max 10 records in PHP
            $offlineActs = Transaction::with('user:uuid,username')
                ->select('uuid', 'user_id', 'tanggal', 'store_id')
                ->whereBetween('tanggal', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->orderByDesc('tanggal')
                ->limit(5)
                ->get()
                ->map(fn($trx) => [
                    'user'      => $trx->user->name ?? 'Guest',
                    'role'      => 'Offline',
                    'action'    => 'melakukan pembelian kasir',
                    'time'      => Carbon::parse($trx->tanggal)->format('H:i'),
                    'timestamp' => $trx->tanggal,
                    'icon'      => 'solar:cart-large-minimalistic-bold'
                ]);

            $onlineActs = PaymentOrder::with('user:uuid,username')
                ->select('id', 'user_id', 'paid_at', 'outlet_id', 'recipient_name')
                ->whereBetween('paid_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->whereNotNull('paid_at')
                ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
                ->orderByDesc('paid_at')
                ->limit(5)
                ->get()
                ->map(fn($po) => [
                    'user'      => $po->user->name ?? $po->recipient_name,
                    'role'      => 'Online',
                    'action'    => 'melakukan pembelian web',
                    'time'      => Carbon::parse($po->paid_at)->format('H:i'),
                    'timestamp' => $po->paid_at,
                    'icon'      => 'solar:global-bold'
                ]);

            $activitiesArray = $offlineActs->concat($onlineActs)
                ->sortByDesc('timestamp')
                ->take(5)
                ->map(fn($act) => [
                    'user'   => $act['user'] ?? '',
                    'role'   => $act['role'] ?? '',
                    'action' => $act['action'] ?? '',
                    'time'   => $act['time'] ?? '',
                    'icon'   => $act['icon'] ?? ''
                ])
                ->values()
                ->all();

            return [
                'stats' => $stats,
                'chartHarian' => $chartHarian,
                'chartMingguan' => $chartMingguan,
                'chartBulanan' => $chartBulanan,
                'chartTahunan' => $chartTahunan,
                'cfHarian' => $cfHarian,
                'cfMingguan' => $cfMingguan,
                'cfBulanan' => $cfBulanan,
                'cfTahunan' => $cfTahunan,
                'totalPiutang' => $totalPiutang,
                'totalHutang' => $totalHutang,
                'lowStockProducts' => $lowStockProducts->toArray(),
                'expiredProducts' => $expiredProducts->toArray(),
                'topProducts' => $topProductsArray,
                'activities' => $activitiesArray
            ];
        });

        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (!is_array($data)) {
            $data = [];
        }
        
        $statsArray = $data['stats'] ?? [];
        if (!is_array($statsArray)) {
            $statsArray = [];
        }

        return view('dashboard', array_merge($statsArray, [
            'chartHarian' => $data['chartHarian'] ?? ['labels' => [], 'offline' => [], 'online' => []], 
            'chartMingguan' => $data['chartMingguan'] ?? ['labels' => [], 'offline' => [], 'online' => []], 
            'chartBulanan' => $data['chartBulanan'] ?? ['labels' => [], 'offline' => [], 'online' => []], 
            'chartTahunan' => $data['chartTahunan'] ?? ['labels' => [], 'offline' => [], 'online' => []],
            'cfHarian' => $data['cfHarian'] ?? ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]], 
            'cfMingguan' => $data['cfMingguan'] ?? ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]], 
            'cfBulanan' => $data['cfBulanan'] ?? ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]], 
            'cfTahunan' => $data['cfTahunan'] ?? ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]],
            'totalPiutang' => $data['totalPiutang'] ?? 0, 
            'totalHutang' => $data['totalHutang'] ?? 0,
            'lowStockProducts' => collect($data['lowStockProducts'] ?? []), 
            'expiredProducts' => collect($data['expiredProducts'] ?? []), 
            'topProducts' => collect($data['topProducts'] ?? []), 
            'activities' => collect($data['activities'] ?? []),
            'stores' => $stores, 
            'currentStoreId' => $storeId, 
            'title' => $user->role === 'owner' ? 'Dashboard Owner' : 'Dashboard ' . ucfirst($user->role)
        ]));
    }

    private function getCashFlowData($start, $end, $storeId)
    {
        // 1. Get sums using high-performance SQL select
        $sums = DB::table('cash_flows')
            ->whereBetween('tanggal', [$start, $end])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->select(
                DB::raw("SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END) as total_pemasukan"),
                DB::raw("SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END) as total_pengeluaran")
            )
            ->first();

        // 2. Get last 7 nominal values for pemasukan and pengeluaran
        $pData = DB::table('cash_flows')
            ->whereBetween('tanggal', [$start, $end])
            ->where('jenis', 'pemasukan')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->orderBy('tanggal', 'desc')
            ->limit(7)
            ->pluck('nominal')
            ->reverse()
            ->values()
            ->all();

        $eData = DB::table('cash_flows')
            ->whereBetween('tanggal', [$start, $end])
            ->where('jenis', 'pengeluaran')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->orderBy('tanggal', 'desc')
            ->limit(7)
            ->pluck('nominal')
            ->reverse()
            ->values()
            ->all();

        return [
            'total_pemasukan' => (float)($sums->total_pemasukan ?? 0),
            'total_pengeluaran' => (float)($sums->total_pengeluaran ?? 0),
            'p_series' => $pData ?: [0],
            'e_series' => $eData ?: [0]
        ];
    }

    private function getStats($storeId, $today, $yesterday)
    {
        $startToday = $today->copy()->startOfDay();
        $endToday = $today->copy()->endOfDay();
        $startYesterday = $yesterday->copy()->startOfDay();
        $endYesterday = $yesterday->copy()->endOfDay();

        // 1. Transaction stats in 1 range query instead of 4 separate query round-trips
        $trxStats = DB::table('transactions')
            ->whereBetween('tanggal', [$startYesterday, $endToday])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->select(
                DB::raw("COUNT(CASE WHEN tanggal >= '{$startToday}' THEN 1 END) as count_today"),
                DB::raw("COUNT(CASE WHEN tanggal < '{$startToday}' THEN 1 END) as count_yesterday"),
                DB::raw("SUM(CASE WHEN tanggal >= '{$startToday}' THEN total ELSE 0 END) as sum_today"),
                DB::raw("SUM(CASE WHEN tanggal < '{$startToday}' THEN total ELSE 0 END) as sum_yesterday")
            )
            ->first();

        // 2. PaymentOrder stats in 1 range query instead of 6 separate query round-trips
        $poStats = DB::table('payment_orders')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startYesterday, $endToday])
            ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
            ->select(
                DB::raw("COUNT(CASE WHEN paid_at >= '{$startToday}' THEN 1 END) as count_today"),
                DB::raw("COUNT(CASE WHEN paid_at < '{$startToday}' THEN 1 END) as count_yesterday"),
                DB::raw("SUM(CASE WHEN paid_at >= '{$startToday}' THEN total_amount ELSE 0 END) as sum_today"),
                DB::raw("SUM(CASE WHEN paid_at < '{$startToday}' THEN total_amount ELSE 0 END) as sum_yesterday"),
                DB::raw("SUM(CASE WHEN paid_at >= '{$startToday}' THEN items_count ELSE 0 END) as sold_today"),
                DB::raw("SUM(CASE WHEN paid_at < '{$startToday}' THEN items_count ELSE 0 END) as sold_yesterday")
            )
            ->first();

        // 3. Replaced slow exists whereHas queries with 1 high-performance range join
        $soldOffline = DB::table('transaction_detail')
            ->join('transactions', 'transaction_detail.transaction_id', '=', 'transactions.uuid')
            ->whereBetween('transactions.tanggal', [$startYesterday, $endToday])
            ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
            ->select(
                DB::raw("SUM(CASE WHEN transactions.tanggal >= '{$startToday}' THEN transaction_detail.jmlh ELSE 0 END) as sold_today"),
                DB::raw("SUM(CASE WHEN transactions.tanggal < '{$startToday}' THEN transaction_detail.jmlh ELSE 0 END) as sold_yesterday")
            )
            ->first();

        $trxToday = ($trxStats->count_today ?? 0) + ($poStats->count_today ?? 0);
        $trxPrev = ($trxStats->count_yesterday ?? 0) + ($poStats->count_yesterday ?? 0);
        
        $revOfflineToday = (float)($trxStats->sum_today ?? 0);
        $revOfflinePrev = (float)($trxStats->sum_yesterday ?? 0);
        $revOnlineToday = (float)($poStats->sum_today ?? 0);
        $revOnlinePrev = (float)($poStats->sum_yesterday ?? 0);

        $soldToday = ($soldOffline->sold_today ?? 0) + ($poStats->sold_today ?? 0);
        $soldPrev = ($soldOffline->sold_yesterday ?? 0) + ($poStats->sold_yesterday ?? 0);

        $userOperator = DB::table('operator')->where('nama', 'User')->first();
        $custQuery = User::query();
        if ($userOperator) {
            $custQuery->where(function($q) use ($userOperator) {
                $q->whereNull('operator_id')
                  ->orWhere('operator_id', $userOperator->uuid);
            });
        } else {
            $custQuery->whereNull('operator_id');
        }
        $cust = $custQuery->count();

        $revTotal = $revOfflineToday + $revOnlineToday;
        $revPrevTotal = $revOfflinePrev + $revOnlinePrev;

        $lowStockCount = ProductStore::where('stok', '<=', DB::raw('COALESCE(stok_minimum, 10)'))->when($storeId, fn($q) => $q->where('store_id', $storeId))->count();
        $employeeOperatorIds = DB::table('operator')->whereNotIn(DB::raw('LOWER(nama)'), ['user', 'owner'])->pluck('uuid');
        $activeEmployees = User::whereIn('operator_id', $employeeOperatorIds)->when($storeId, fn($q) => $q->where('store_id', $storeId))->count();
        $totalEmployees = $activeEmployees;

        return [
            'total_transaksi' => $trxToday, 'diff_transaksi' => $this->calculateDiff($trxToday, $trxPrev),
            'total_pendapatan' => (float)$revTotal, 'diff_pendapatan' => $this->calculateDiff($revTotal, $revPrevTotal),
            'rev_offline' => $revOfflineToday, 'diff_offline' => $this->calculateDiff($revOfflineToday, $revOfflinePrev),
            'rev_online' => $revOnlineToday, 'diff_online' => $this->calculateDiff($revOnlineToday, $revOnlinePrev),
            'total_produk_terjual' => (int)$soldToday, 'diff_produk_terjual' => $this->calculateDiff($soldToday, $soldPrev),
            'total_customers' => $cust,
            'low_stock_count' => $lowStockCount,
            'active_employees' => $activeEmployees,
            'total_employees' => $totalEmployees,
        ];
    }

    private function calculateDiff($t, $y) { if ($y == 0) return $t > 0 ? 100 : 0; return round((($t - $y) / $y) * 100, 1); }

    private function getMainChartPresetData($preset, $storeId, $today, $yearFrom = null, $yearTo = null)
    {
        if ($preset === 'harian') {
            $hTrxData = Transaction::whereBetween('tanggal', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->select(DB::raw('EXTRACT(HOUR FROM tanggal) as hour'), DB::raw('SUM(total) as total'))
                ->groupBy('hour')
                ->pluck('total', 'hour')
                ->all();

            $hPOData = PaymentOrder::whereBetween('paid_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->whereNotNull('paid_at')
                ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
                ->select(DB::raw('EXTRACT(HOUR FROM paid_at) as hour'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('hour')
                ->pluck('total', 'hour')
                ->all();

            $chartHarian = ['labels' => [], 'offline' => [], 'online' => []];
            for ($i = 0; $i < 24; $i++) {
                $chartHarian['labels'][] = sprintf('%02d:00', $i);
                $chartHarian['offline'][] = (float)($hTrxData[$i] ?? 0);
                $chartHarian['online'][] = (float)($hPOData[$i] ?? 0);
            }
            return $chartHarian;
        }

        if ($preset === 'mingguan') {
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);
            $wTrxData = Transaction::whereBetween('tanggal', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->select(DB::raw('EXTRACT(DOW FROM tanggal) as dow'), DB::raw('SUM(total) as total'))
                ->groupBy('dow')
                ->pluck('total', 'dow')
                ->all();

            $wPOData = PaymentOrder::whereBetween('paid_at', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()])
                ->whereNotNull('paid_at')
                ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
                ->select(DB::raw('EXTRACT(DOW FROM paid_at) as dow'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('dow')
                ->pluck('total', 'dow')
                ->all();

            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
            $chartMingguan = ['labels' => [], 'offline' => [], 'online' => []];
            foreach ($days as $dow => $name) {
                $chartMingguan['labels'][] = $name;
                $chartMingguan['offline'][] = (float)($wTrxData[$dow] ?? 0);
                $chartMingguan['online'][] = (float)($wPOData[$dow] ?? 0);
            }
            return $chartMingguan;
        }

        if ($preset === 'bulanan') {
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear = Carbon::now()->endOfYear();
            $mTrxData = Transaction::whereBetween('tanggal', [$startOfYear->copy()->startOfDay(), $endOfYear->copy()->endOfDay()])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->select(DB::raw('EXTRACT(MONTH FROM tanggal) as month'), DB::raw('SUM(total) as total'))
                ->groupBy('month')
                ->pluck('total', 'month')
                ->all();

            $mPOData = PaymentOrder::whereBetween('paid_at', [$startOfYear->copy()->startOfDay(), $endOfYear->copy()->endOfDay()])
                ->whereNotNull('paid_at')
                ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
                ->select(DB::raw('EXTRACT(MONTH FROM paid_at) as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->pluck('total', 'month')
                ->all();

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $chartBulanan = ['labels' => [], 'offline' => [], 'online' => []];
            for ($i = 1; $i <= 12; $i++) {
                $chartBulanan['labels'][] = $months[$i-1];
                $chartBulanan['offline'][] = (float)($mTrxData[$i] ?? 0);
                $chartBulanan['online'][] = (float)($mPOData[$i] ?? 0);
            }
            return $chartBulanan;
        }

        if ($preset === 'tahunan') {
            if (!$yearFrom) { $yearFrom = Carbon::now()->year - 4; }
            if (!$yearTo) { $yearTo = Carbon::now()->year; }
            $yTrxData = Transaction::whereBetween('tanggal', [Carbon::create($yearFrom, 1, 1)->startOfDay(), Carbon::create($yearTo, 12, 31)->endOfDay()])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->select(DB::raw('EXTRACT(YEAR FROM tanggal) as year'), DB::raw('SUM(total) as total'))
                ->groupBy('year')
                ->pluck('total', 'year')
                ->all();

            $yPOData = PaymentOrder::whereBetween('paid_at', [Carbon::create($yearFrom, 1, 1)->startOfDay(), Carbon::create($yearTo, 12, 31)->endOfDay()])
                ->whereNotNull('paid_at')
                ->when($storeId, fn($q) => $q->where('outlet_id', $storeId))
                ->select(DB::raw('EXTRACT(YEAR FROM paid_at) as year'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('year')
                ->pluck('total', 'year')
                ->all();

            $chartTahunan = ['labels' => [], 'offline' => [], 'online' => []];
            for ($y = $yearFrom; $y <= $yearTo; $y++) {
                $chartTahunan['labels'][] = (string)$y;
                $chartTahunan['offline'][] = (float)($yTrxData[$y] ?? 0);
                $chartTahunan['online'][] = (float)($yPOData[$y] ?? 0);
            }
            return $chartTahunan;
        }

        return ['labels' => [], 'offline' => [], 'online' => []];
    }

    private function getCashFlowPresetData($preset, $storeId)
    {
        $start = null;
        $end = null;

        if ($preset === 'harian') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        } elseif ($preset === 'mingguan') {
            $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        } elseif ($preset === 'bulanan') {
            $start = Carbon::now()->startOfYear()->startOfDay();
            $end = Carbon::now()->endOfYear()->endOfDay();
        } elseif ($preset === 'tahunan') {
            $start = Carbon::now()->subYears(4)->startOfYear()->startOfDay();
            $end = Carbon::now()->endOfYear()->endOfDay();
        }

        if ($start && $end) {
            return $this->getCashFlowData($start, $end, $storeId);
        }

        return ['total_pemasukan' => 0, 'total_pengeluaran' => 0, 'p_series' => [0], 'e_series' => [0]];
    }
}
