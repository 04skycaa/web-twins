<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiController extends Controller
{
    /**
     * Main page — 4 tabs: Master Shift, Jadwal Karyawan, Riwayat Absensi, Rekap Absensi
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Outlets available to this user
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->where('status_aktif', true)->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }

        $defaultStore = $user->role === 'owner' ? 'all' : ($user->outlet_id ?? ($outlets->first()->uuid ?? null));
        $store_id = $request->input('store_id', $defaultStore);
        $shift_id = $request->input('shift_id', 'all');
        
        $parentFitur = \App\Models\Fitur::where('nama', 'Absensi')->first();
        $sub_menus = $parentFitur ? \App\Models\Fitur::where('parent_id', $parentFitur->id)->orderBy('id')->get() : collect();

        $hasShift = false;
        $hasJadwal = false;
        $hasRiwayat = false;
        $hasRekap = false;

        foreach($sub_menus as $sm) {
            if ($sm->nama == 'Master Shift' && $user->hasFeature($sm->id)) $hasShift = true;
            if ($sm->nama == 'Jadwal Karyawan' && $user->hasFeature($sm->id)) $hasJadwal = true;
            if ($sm->nama == 'Riwayat Absensi' && $user->hasFeature($sm->id)) $hasRiwayat = true;
            if ($sm->nama == 'Rekap Absensi' && $user->hasFeature($sm->id)) $hasRekap = true;
        }

        $active_tab = $request->input('active_tab', session('active_tab'));
        if ($active_tab == 'shift' && !$hasShift) $active_tab = '';
        if ($active_tab == 'jadwal' && !$hasJadwal) $active_tab = '';
        if ($active_tab == 'riwayat' && !$hasRiwayat) $active_tab = '';
        if ($active_tab == 'rekap' && !$hasRekap) $active_tab = '';

        if (!$active_tab) {
            if ($hasShift) $active_tab = 'shift';
            elseif ($hasJadwal) $active_tab = 'jadwal';
            elseif ($hasRiwayat) $active_tab = 'riwayat';
            elseif ($hasRekap) $active_tab = 'rekap';
        }

        // ─── TAB 1: MASTER SHIFT (global, not per-store) ───
        $shifts = Shift::orderBy('waktu_mulai', 'asc')->get();

        // ─── TAB 2: JADWAL KARYAWAN ───
        $jadwalQuery = Jadwal::with(['user.operator', 'user.store', 'shift']);
        if ($store_id !== 'all') {
            $jadwalQuery->where('store_id', $store_id);
        }
        if ($shift_id !== 'all') {
            $jadwalQuery->where('shift_id', $shift_id);
        }
        $jadwals = $jadwalQuery->orderBy('hari_dalam_minggu', 'asc')->get();

        // Karyawan list for form dropdowns
        if ($user->role === 'owner') {
            $usersQuery = User::whereHas('operator', function ($q) {
                $q->whereNotIn(DB::raw('LOWER(nama)'), ['owner']);
            })->where('status_aktif', true);
        } else {
            $usersQuery = User::whereHas('operator', function ($q) {
                $q->whereNotIn(DB::raw('LOWER(nama)'), ['owner', 'kepala toko']);
            })->where('status_aktif', true);
        }

        $karyawanList = $usersQuery->with(['operator', 'store'])->get();

        // ─── TAB 3: RIWAYAT ABSENSI (server-side paginated) ───
        $riwayatQuery = Absensi::with(['jadwal.user.operator', 'jadwal.shift', 'store']);
        if ($store_id !== 'all') {
            $riwayatQuery->where('store_id', $store_id);
        }
        if ($shift_id !== 'all') {
            $riwayatQuery->whereHas('jadwal', function($q) use ($shift_id) {
                $q->where('shift_id', $shift_id);
            });
        }

        // Filters for riwayat
        $filterBulan = $request->input('filter_bulan');
        $filterKaryawan = $request->input('filter_karyawan');

        if ($filterBulan) {
            $riwayatQuery->where('tanggal_absensi', 'like', $filterBulan . '%');
        }
        if ($filterKaryawan) {
            $riwayatQuery->whereHas('jadwal.user', function ($q) use ($filterKaryawan) {
                $q->where('username', 'ilike', '%' . $filterKaryawan . '%');
            });
        }

        $riwayat = $riwayatQuery->orderBy('tanggal_absensi', 'desc')
            ->orderBy('waktu_check_in', 'desc')
            ->get();

        // ─── TAB 4: REKAP ABSENSI (DB-level aggregation) ───
        $rekapBulan = $request->input('rekap_bulan') ?: \Carbon\Carbon::now()->format('Y-m');
        $rekapStoreId = ($store_id !== 'all') ? $store_id : null;
        $rekapKaryawan = $request->input('filter_karyawan');

        $rekap = $this->getRekapAbsensi($rekapBulan, $rekapStoreId, $rekapKaryawan);

        return view('absensi.index', [
            'title'         => 'Kelola Jadwal & Absensi',
            'shifts'        => $shifts,
            'jadwals'       => $jadwals,
            'karyawanList'  => $karyawanList,
            'riwayat'       => $riwayat,
            'rekap'         => $rekap,
            'rekapBulan'    => $rekapBulan,
            'outlets'       => $outlets,
            'store_id'      => $store_id,
            'shift_id'      => $shift_id,
            'active_tab'    => $active_tab,
            'filterBulan'   => $filterBulan,
            'filterKaryawan' => $filterKaryawan,
            'hasShift'      => $hasShift,
            'hasJadwal'     => $hasJadwal,
            'hasRiwayat'    => $hasRiwayat,
            'hasRekap'      => $hasRekap,
            'sub_menus'     => $sub_menus,
            'jadwalListJson' => $jadwals->map(function($j) {
                return [
                    'user_id' => $j->user_id,
                    'hari' => $j->hari_dalam_minggu,
                    'store_id' => $j->store_id
                ];
            })
        ]);
    }

    /**
     * Get rekap absensi with DB-level aggregation + caching
     */
    private function getRekapAbsensi(string $bulan, ?string $store_id, ?string $filter_karyawan = null)
    {
        $cacheKey = "rekap_absensi_{$bulan}_{$store_id}_{$filter_karyawan}";

        // Cache for past months (1 hour), skip cache for current month
        $isCurrentMonth = ($bulan === Carbon::now()->format('Y-m'));
        $cacheDuration = $isCurrentMonth ? 0 : 3600; // 1 hour for past months

        if (!$isCurrentMonth && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $query = DB::table('users as u')
            ->leftJoin('jadwal as j', 'j.user_id', '=', 'u.uuid')
            ->leftJoin('absensi as a', function ($join) use ($bulan) {
                $join->on('a.jadwal_id', '=', 'j.uuid')
                    ->where('a.tanggal_absensi', 'like', $bulan . '%');
            })
            ->where('u.status_aktif', true)
            ->whereExists(function ($sub) {
                // Only users that are actual employees (not owner)
                $sub->select(DB::raw(1))
                    ->from('operator as op')
                    ->whereColumn('op.uuid', 'u.operator_id')
                    ->whereRaw("LOWER(op.nama) != 'owner'");
            });

        if ($store_id) {
            $query->where('u.store_id', $store_id);
        }

        if ($filter_karyawan) {
            $query->where('u.username', 'ilike', '%' . $filter_karyawan . '%');
        }

        if ($shift_id = request('shift_id', 'all')) {
            if ($shift_id !== 'all') {
                $query->where('j.shift_id', $shift_id);
            }
        }

        $rekap = $query->select(
            'u.uuid',
            'u.username',
            'u.store_id',
            DB::raw("SUM(CASE WHEN a.status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
            DB::raw("SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as total_izin"),
            DB::raw("SUM(CASE WHEN a.status_kehadiran = 'alpha' THEN 1 ELSE 0 END) as total_alpha"),
            DB::raw("COUNT(a.uuid) as total_record")
        )
            ->groupBy('u.uuid', 'u.username', 'u.store_id')
            ->orderBy('u.username', 'asc')
            ->get();

        if (!$isCurrentMonth) {
            Cache::put($cacheKey, $rekap, $cacheDuration);
        }

        return $rekap;
    }

    // ═══════════════════════════════════════════
    //  SHIFT CRUD
    // ═══════════════════════════════════════════

    public function storeShift(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:100',
            'waktu_mulai'   => 'required',
            'waktu_selesai'  => 'required',
        ]);

        Shift::create([
            'uuid'          => Str::uuid(),
            'nama'    => $request->nama,
            'waktu_mulai'   => $request->waktu_mulai,
            'waktu_selesai'  => $request->waktu_selesai,
        ]);

        return redirect()->back()
            ->with('success', 'Shift berhasil ditambahkan!')
            ->with('active_tab', 'shift');
    }

    public function updateShift(Request $request, $uuid)
    {
        $request->validate([
            'nama'    => 'required|string|max:100',
            'waktu_mulai'   => 'required',
            'waktu_selesai'  => 'required',
        ]);

        Shift::where('uuid', $uuid)->update([
            'nama'    => $request->nama,
            'waktu_mulai'   => $request->waktu_mulai,
            'waktu_selesai'  => $request->waktu_selesai,
        ]);

        return redirect()->back()
            ->with('success', 'Shift berhasil diperbarui!')
            ->with('active_tab', 'shift');
    }

    public function deleteShift($uuid)
    {
        // Restrict on delete: check if shift is used in jadwal
        $count = Jadwal::where('shift_id', $uuid)->count();
        if ($count > 0) {
            return redirect()->back()
                ->with('error', "Shift tidak bisa dihapus karena masih digunakan oleh {$count} jadwal karyawan. Hapus jadwal terkait terlebih dahulu.")
                ->with('active_tab', 'shift');
        }

        Shift::where('uuid', $uuid)->delete();

        return redirect()->back()
            ->with('success', 'Shift berhasil dihapus!')
            ->with('active_tab', 'shift');
    }

    // ═══════════════════════════════════════════
    //  JADWAL KARYAWAN CRUD
    // ═══════════════════════════════════════════

    public function storeJadwal(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|uuid',
            'shift_id'          => 'required|uuid',
            'store_id'          => 'required|uuid',
            'hari_dalam_minggu' => 'required|array|min:1',
            'hari_dalam_minggu.*' => 'integer|between:1,7',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($request->hari_dalam_minggu as $hari) {
            // Check duplicate: same user, same day, same store
            $exists = Jadwal::where('user_id', $request->user_id)
                ->where('store_id', $request->store_id)
                ->where('hari_dalam_minggu', $hari)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Jadwal::create([
                'uuid'              => Str::uuid(),
                'user_id'           => $request->user_id,
                'shift_id'          => $request->shift_id,
                'store_id'          => $request->store_id,
                'hari_dalam_minggu' => $hari,
            ]);
            $created++;
        }

        $msg = "Jadwal berhasil ditambahkan ({$created} hari).";
        if ($skipped > 0) {
            $msg .= " {$skipped} hari dilewati karena sudah ada jadwal.";
        }

        return redirect()->back()
            ->with('success', $msg)
            ->with('active_tab', 'jadwal');
    }

    public function updateJadwal(Request $request, $uuid)
    {
        $request->validate([
            'shift_id'          => 'required|uuid',
            'hari_dalam_minggu' => 'required|integer|between:1,7',
        ]);

        $jadwal = Jadwal::where('uuid', $uuid)->firstOrFail();

        // Check duplicate (excluding self)
        $exists = Jadwal::where('user_id', $jadwal->user_id)
            ->where('store_id', $jadwal->store_id)
            ->where('hari_dalam_minggu', $request->hari_dalam_minggu)
            ->where('uuid', '!=', $uuid)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Karyawan sudah memiliki jadwal di hari tersebut.')
                ->with('active_tab', 'jadwal');
        }

        $jadwal->update([
            'shift_id'          => $request->shift_id,
            'hari_dalam_minggu' => $request->hari_dalam_minggu,
        ]);

        return redirect()->back()
            ->with('success', 'Jadwal berhasil diperbarui!')
            ->with('active_tab', 'jadwal');
    }

    public function deleteJadwal($uuid)
    {
        Jadwal::where('uuid', $uuid)->delete();

        return redirect()->back()
            ->with('success', 'Jadwal karyawan berhasil dihapus!')
            ->with('active_tab', 'jadwal');
    }

    // ═══════════════════════════════════════════
    //  RIWAYAT ABSENSI — Update Status
    // ═══════════════════════════════════════════

    public function updateAbsensiStatus(Request $request, $uuid)
    {
        $request->validate([
            'status_kehadiran' => 'required|in:hadir,izin,alpha',
        ]);

        Absensi::where('uuid', $uuid)->update([
            'status_kehadiran' => $request->status_kehadiran,
        ]);

        return redirect()->back()
            ->with('success', 'Status kehadiran berhasil diperbarui!')
            ->with('active_tab', 'riwayat');
    }

    public function exportRiwayat(Request $request)
    {
        $store_id = $request->store_id ?? 'all';
        $filterBulan = $request->filter_bulan;
        $filterKaryawan = $request->filter_karyawan;
        $format = $request->format ?? 'excel';

        $query = Absensi::with(['jadwal.user.operator', 'store', 'jadwal.shift']);

        if ($store_id !== 'all') {
            $query->where('store_id', $store_id);
            $outlet_name = DB::table('store')->where('uuid', $store_id)->value('nama') ?? 'Semua Outlet';
        } else {
            $outlet_name = 'Semua Outlet';
        }

        if ($filterBulan) {
            $month = date('m', strtotime($filterBulan));
            $year = date('Y', strtotime($filterBulan));
            $query->whereMonth('tanggal_absensi', $month)->whereYear('tanggal_absensi', $year);
        }

        if ($filterKaryawan) {
            $query->whereHas('jadwal.user', function($q) use ($filterKaryawan) {
                $q->where('name', 'ilike', '%' . $filterKaryawan . '%');
            });
        }

        $riwayat = $query->orderBy('tanggal_absensi', 'desc')->get();

        $data = compact('riwayat', 'store_id', 'outlet_name', 'filterBulan', 'filterKaryawan');

        if ($format === 'excel') {
            return response(view('absensi.export_excel', $data))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="Export_Riwayat_Absensi_'.date('Ymd_His').'.xls"');
        }

        $pdf = Pdf::loadView('absensi.export_pdf', $data);
        return $pdf->download('Export_Riwayat_Absensi_'.date('Ymd_His').'.pdf');
    }
}
