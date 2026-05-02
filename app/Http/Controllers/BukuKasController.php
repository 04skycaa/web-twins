<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashFlow;
use App\Models\Debt;
use App\Models\DetailDebt;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuKasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }
        
        $defaultStore = $user->role === 'owner' ? 'all' : ($user->outlet_id ?? ($outlets->first()->uuid ?? null));
        $store_id = $request->input('store_id', $defaultStore);

        $pengeluaranQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pengeluaran');
        $pemasukanQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pemasukan');
        $hutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) IN (?, ?)', ['hutang', 'utang']);
        $piutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) = ?', ['piutang']);
        $suppliersQuery = Contact::whereRaw('LOWER(tipe) = ?', ['supplier']);
        $customersQuery = Contact::whereRaw('LOWER(tipe) = ?', ['customer']);

        if ($store_id !== 'all') {
            $pengeluaranQuery->where('store_id', $store_id);
            $pemasukanQuery->where('store_id', $store_id);
            $hutangQuery->where('store_id', $store_id);
            $piutangQuery->where('store_id', $store_id);
            $suppliersQuery->where(function($q) use ($store_id) {
                $q->where('store_id', $store_id)->orWhereNull('store_id');
            });
            $customersQuery->where(function($q) use ($store_id) {
                $q->where('store_id', $store_id)->orWhereNull('store_id');
            });
        }

        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->get();
        $pemasukan = $pemasukanQuery->orderBy('tanggal', 'desc')->get();
        $hutang = $hutangQuery->orderBy('jatuh_tempo', 'asc')->get();
        $piutang = $piutangQuery->orderBy('jatuh_tempo', 'asc')->get();
        $suppliers = $suppliersQuery->get();
        $customers = $customersQuery->get();

        return view('buku_kas.buku_kas', [
            'title' => 'Buku Kas',
            'pengeluaran' => $pengeluaran,
            'pemasukan' => $pemasukan,
            'hutang' => $hutang,
            'piutang' => $piutang,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'outlets' => $outlets,
            'store_id' => $store_id,
        ]);
    }

    public function storeCashFlow(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
        ]);

        CashFlow::create([
            'store_id' => $request->store_id,
            'user_id' => auth()->user()->uuid ?? auth()->id(),
            'jenis' => strtolower($request->jenis),
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->back()->with('success', $request->jenis . ' berhasil dicatat!')->with('active_tab', strtolower($request->jenis));
    }

    public function updateCashFlow(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
        ]);

        $cf = CashFlow::findOrFail($id);
        $cf->update([
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!')->with('active_tab', strtolower($cf->jenis));
    }

    public function deleteCashFlow($id)
    {
        $cf = CashFlow::findOrFail($id);
        $jenis = strtolower($cf->jenis);
        $cf->delete();
        return redirect()->back()->with('success', 'Data ' . ucfirst($jenis) . ' berhasil dihapus!')->with('active_tab', $jenis);
    }

    public function storeDebt(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'tipe' => 'required|in:Hutang,Piutang',
            'kontak_id' => 'nullable',
            'kontak_nama' => 'required_without:kontak_id',
            'nominal' => 'required|numeric',
            'uang_muka' => 'nullable|numeric',
            'jatuh_tempo' => 'required|date',
        ]);

        $kontakId = $request->kontak_id;

        if (!$kontakId && $request->kontak_nama) {
            $contact = Contact::firstOrCreate([
                'store_id' => $request->store_id,
                'nama' => $request->kontak_nama,
                'tipe' => $request->tipe == 'Hutang' ? 'supplier' : 'customer',
            ], ['no_hp' => '-']);
            $kontakId = $contact->uuid;
        }

        $sisa = $request->nominal - ($request->uang_muka ?? 0);

        $debt = Debt::create([
            'store_id' => $request->store_id,
            'kontak_id' => $kontakId,
            'tipe' => strtolower($request->tipe) === 'hutang' ? 'utang' : strtolower($request->tipe),
            'nominal' => $request->nominal,
            'sisa' => $sisa,
            'jatuh_tempo' => $request->jatuh_tempo,
        ]);

        if ($request->uang_muka > 0) {
            DetailDebt::create([
                'debts_id' => $debt->uuid,
                'sebelum' => $request->nominal,
                'bayar' => $request->uang_muka,
                'sisa' => $sisa
            ]);
        }

        return redirect()->back()->with('success', $request->tipe . ' berhasil dicatat!')->with('active_tab', strtolower($request->tipe));
    }

    public function updateDebt(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric',
            'jatuh_tempo' => 'required|date',
            'kontak_nama' => 'required'
        ]);

        $debt = Debt::findOrFail($id);
        
        $contact = Contact::firstOrCreate([
            'store_id' => $debt->store_id,
            'nama' => $request->kontak_nama,
            'tipe' => $debt->tipe == 'Hutang' ? 'supplier' : 'customer',
        ], ['no_hp' => '-']);

        $diff = $request->nominal - $debt->nominal;
        $sisaBaru = $debt->sisa + $diff;

        $debt->update([
            'kontak_id' => $contact->uuid,
            'nominal' => $request->nominal,
            'sisa' => $sisaBaru,
            'jatuh_tempo' => $request->jatuh_tempo,
        ]);

        $tabTipe = strtolower($debt->tipe) === 'utang' ? 'hutang' : strtolower($debt->tipe);
        return redirect()->back()->with('success', 'Data berhasil diperbarui!')->with('active_tab', $tabTipe);
    }

    public function payDebt(Request $request, $id)
    {
        $request->validate([
            'bayar' => 'required|numeric|min:1'
        ]);

        $debt = Debt::findOrFail($id);
        
        $sebelum = $debt->sisa;
        $sisaBaru = max(0, $debt->sisa - $request->bayar);

        DetailDebt::create([
            'debts_id' => $debt->uuid,
            'sebelum' => $sebelum,
            'bayar' => $request->bayar,
            'sisa' => $sisaBaru
        ]);

        $debt->update(['sisa' => $sisaBaru]);

        $tabTipe = strtolower($debt->tipe) === 'utang' ? 'hutang' : strtolower($debt->tipe);
        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat!')->with('active_tab', $tabTipe);
    }

    public function deleteDebt($id)
    {
        $debt = Debt::findOrFail($id);
        $tipe = strtolower($debt->tipe);
        DetailDebt::where('debts_id', $debt->uuid)->delete();
        $debt->delete();
        $tabTipe = $tipe === 'utang' ? 'hutang' : $tipe;
        $namaTipe = $tipe === 'utang' ? 'Hutang' : ucfirst($tipe);
        return redirect()->back()->with('success', 'Data ' . $namaTipe . ' berhasil dihapus!')->with('active_tab', $tabTipe);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $kategoriList = $request->input('kategori', []);
        $store_id = $request->input('store_id', 'all');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $pengeluaranQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pengeluaran');
        $pemasukanQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pemasukan');
        $hutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) IN (?, ?)', ['hutang', 'utang']);
        $piutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) = ?', ['piutang']);

        if ($store_id !== 'all') {
            $pengeluaranQuery->where('store_id', $store_id);
            $pemasukanQuery->where('store_id', $store_id);
            $hutangQuery->where('store_id', $store_id);
            $piutangQuery->where('store_id', $store_id);
            $outlet_name = DB::table('store')->where('uuid', $store_id)->value('nama') ?? 'Semua Outlet';
        } else {
            $outlet_name = 'Semua Outlet';
        }

        if ($start_date) {
            $pengeluaranQuery->whereDate('tanggal', '>=', $start_date);
            $pemasukanQuery->whereDate('tanggal', '>=', $start_date);
            $hutangQuery->whereDate('jatuh_tempo', '>=', $start_date);
            $piutangQuery->whereDate('jatuh_tempo', '>=', $start_date);
        }
        if ($end_date) {
            $pengeluaranQuery->whereDate('tanggal', '<=', $end_date);
            $pemasukanQuery->whereDate('tanggal', '<=', $end_date);
            $hutangQuery->whereDate('jatuh_tempo', '<=', $end_date);
            $piutangQuery->whereDate('jatuh_tempo', '<=', $end_date);
        }

        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->get();
        $pemasukan = $pemasukanQuery->orderBy('tanggal', 'desc')->get();
        $hutang = $hutangQuery->orderBy('jatuh_tempo', 'asc')->get();
        $piutang = $piutangQuery->orderBy('jatuh_tempo', 'asc')->get();

        $total_pemasukan = $pemasukan->sum('nominal');
        $total_pengeluaran = $pengeluaran->sum('nominal');
        $total_sisa_hutang = $hutang->sum('sisa');
        $total_sisa_piutang = $piutang->sum('sisa');

        $data = compact(
            'kategoriList', 'start_date', 'end_date', 'outlet_name',
            'pengeluaran', 'pemasukan', 'hutang', 'piutang',
            'total_pemasukan', 'total_pengeluaran', 'total_sisa_hutang', 'total_sisa_piutang'
        );

        if ($format === 'excel') {
            return response(view('buku_kas.export_pdf', $data))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="Export_Buku_Kas_'.date('Ymd_His').'.xls"');
        }

        $pdf = Pdf::loadView('buku_kas.export_pdf', $data);
        return $pdf->download('Export_Buku_Kas_'.date('Ymd_His').'.pdf');
    }
}
