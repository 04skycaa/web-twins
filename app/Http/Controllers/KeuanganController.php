<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        return $this->manage($request);
    }

    public function manage(Request $request)
    {
        $user = auth()->user();
        
        // 1. Data Cashbox with Balances (Optimized with a single grouped query to prevent N+1 queries)
        $cashFlowQuery = \App\Models\CashFlow::select('metode_pembayaran', 'jenis', \DB::raw('SUM(nominal) as total'));
        if ($user->role !== 'owner' && $user->store_id) {
            $cashFlowQuery->where('store_id', $user->store_id);
        }
        $cashFlowTotals = $cashFlowQuery->groupBy('metode_pembayaran', 'jenis')->get();

        $balances = [];
        foreach ($cashFlowTotals as $flow) {
            $metode = $flow->metode_pembayaran;
            if (!isset($balances[$metode])) {
                $balances[$metode] = 0;
            }
            if ($flow->jenis === 'pemasukan') {
                $balances[$metode] += floatval($flow->total);
            } elseif ($flow->jenis === 'pengeluaran') {
                $balances[$metode] -= floatval($flow->total);
            }
        }

        $cashboxes = PaymentMethod::orderBy('nama_metode', 'asc')->get()->map(function($cb) use ($balances) {
            $cb->saldo = $balances[$cb->uuid] ?? 0;
            return $cb;
        });

        // 2. Data Arus Uang
        // Filter Outlet
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = \App\Models\Outlet::all();
        } elseif ($user->role === 'kepala_toko' && $user->store_id) {
            $outlets = \App\Models\Outlet::where('uuid', $user->store_id)->get();
        }
        
        $defaultStore = $user->role === 'owner' ? 'all' : ($user->store_id ?? ($outlets->first()->uuid ?? null));
        $store_id = $request->input('store_id', $defaultStore);
        
        // Filter Tanggal
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        // Query Cash Flow
        $query = \App\Models\CashFlow::with(['outlet', 'user', 'paymentMethod']);
        
        if ($store_id !== 'all') {
            $query->where('store_id', $store_id);
        }
        
        if ($start_date) {
            $query->whereDate('tanggal', '>=', $start_date);
        }
        if ($end_date) {
            $query->whereDate('tanggal', '<=', $end_date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%$search%")
                  ->orWhere('jenis', 'like', "%$search%");
            });
        }

        $historyQuery = clone $query;
        $history = $historyQuery->orderBy('tanggal', 'desc')->get();

        // Calculate Summaries in a single high-performance grouped query
        $summaryTotals = (clone $query)
            ->select('jenis', \DB::raw('SUM(nominal) as total'))
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $pemasukan = floatval($summaryTotals->get('pemasukan', 0));
        $pengeluaran = floatval($summaryTotals->get('pengeluaran', 0));
        $saldo_bersih = $pemasukan - $pengeluaran;

        return view('keuangan.cashbox', compact(
            'cashboxes', 'cashFlowTotals', 'history', 'pemasukan', 'pengeluaran', 'saldo_bersih', 'start_date', 'end_date', 'outlets', 'store_id'
        ));
    }

    public function storeCashbox(Request $request)
    {
        $request->validate([
            'nama_metode' => 'required|string|max:255|unique:payment_methods,nama_metode',
        ]);

        PaymentMethod::create([
            'uuid' => (string) Str::uuid(),
            'nama_metode' => $request->nama_metode
        ]);

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil ditambahkan!');
    }

    public function updateCashbox(Request $request, $id)
    {
        $pm = PaymentMethod::findOrFail($id);
        
        $request->validate([
            'nama_metode' => 'required|string|max:255|unique:payment_methods,nama_metode,' . $id . ',uuid',
        ]);

        $pm->update([
            'nama_metode' => $request->nama_metode
        ]);

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil diperbarui!');
    }

    public function destroyCashbox($id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $pm->delete();

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil dihapus!');
    }

    public function kasBox()
    {
        return redirect()->route('keuangan.index', ['tab' => 'cashbox']);
    }

    public function arusUang(Request $request)
    {
        return redirect()->route('keuangan.index', ['tab' => 'arus-uang']);
    }

    public function transferSaldo(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'from_cashbox_id' => 'required',
            'to_cashbox_id' => 'required|different:from_cashbox_id',
            'nominal' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
        ], [
            'to_cashbox_id.different' => 'Akun tujuan harus berbeda dengan akun asal.'
        ]);

        $fromCashbox = \App\Models\PaymentMethod::findOrFail($request->from_cashbox_id);
        $toCashbox = \App\Models\PaymentMethod::findOrFail($request->to_cashbox_id);

        $tanggal = $request->tanggal;
        if ($tanggal == date('Y-m-d')) {
            $tanggal = date('Y-m-d H:i:s');
        } else {
            $tanggal = $tanggal . ' ' . date('H:i:s');
        }

        $userId = auth()->user()->uuid ?? auth()->id();
        $keteranganSuffix = $request->keterangan ? ' - ' . $request->keterangan : '';

        try {
            DB::beginTransaction();

            // 1. Pengeluaran dari Akun Asal
            \App\Models\CashFlow::create([
                'store_id' => $request->store_id,
                'user_id' => $userId,
                'jenis' => 'pengeluaran',
                'nominal' => $request->nominal,
                'keterangan' => 'Pemindahan Saldo ke ' . $toCashbox->nama_metode . $keteranganSuffix,
                'tanggal' => $tanggal,
                'metode_pembayaran' => $fromCashbox->uuid,
            ]);

            // 2. Pemasukan ke Akun Tujuan
            \App\Models\CashFlow::create([
                'store_id' => $request->store_id,
                'user_id' => $userId,
                'jenis' => 'pemasukan',
                'nominal' => $request->nominal,
                'keterangan' => 'Pemindahan Saldo dari ' . $fromCashbox->nama_metode . $keteranganSuffix,
                'tanggal' => $tanggal,
                'metode_pembayaran' => $toCashbox->uuid,
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Pemindahan saldo berhasil dicatat!']);
            }

            return redirect()->route('keuangan.index', ['tab' => 'arus-uang'])->with('success', 'Pemindahan saldo berhasil dicatat!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mencatat pemindahan saldo. Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

}
