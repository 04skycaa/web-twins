<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbsensiController extends Controller
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
        $active_tab = $request->input('active_tab', session('active_tab', 'penugasan'));

        $shiftQuery = Shift::with('store');
        $absensiQuery = Absensi::with('user');
        $userJadwalQuery = \App\Models\UserJadwal::with(['user', 'shift']);

        if ($user->role === 'owner') {
            $usersQuery = User::whereHas('operator', function($q) {
                $q->whereNotIn(DB::raw('LOWER(nama)'), ['owner']);
            });
        } else {
            $usersQuery = User::whereHas('operator', function($q) {
                $q->whereNotIn(DB::raw('LOWER(nama)'), ['owner', 'kepala toko']);
            });
        }

        if ($store_id !== 'all') {
            $shiftQuery->where('store_id', $store_id);
            $absensiQuery->whereHas('user', function($q) use ($store_id) {
                $q->where('store_id', $store_id);
            });
            $userJadwalQuery->whereHas('user', function($q) use ($store_id) {
                $q->where('store_id', $store_id);
            });
            $usersQuery->where('store_id', $store_id);
        }

        $shifts = $shiftQuery->get();
        $riwayat = $absensiQuery->orderBy('tanggal', 'desc')->get();
        $penugasan = $userJadwalQuery->orderBy('tanggal', 'desc')->get();
        $karyawanList = $usersQuery->get();

        return view('absensi.index', [
            'title' => 'Sistem Absensi',
            'shifts' => $shifts,
            'riwayat' => $riwayat,
            'penugasan' => $penugasan,
            'karyawanList' => $karyawanList,
            'outlets' => $outlets,
            'store_id' => $store_id,
            'active_tab' => $active_tab,
            'penugasanJson' => $penugasan->map(function($p) {
                return [
                    'shift_uuid' => $p->shift_uuid,
                    'user_name' => $p->user->name ?? '',
                    'store_id' => $p->user->store_id ?? '',
                    'tanggal' => $p->tanggal
                ];
            })
        ]);
    }

    public function storePenugasan(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'shift_uuid' => 'required',
            'tanggal' => 'nullable|string',
        ]);

        if ($request->has('is_permanent')) {
            // Hapus jadwal permanen yang lama agar tidak dobel
            \App\Models\UserJadwal::where('user_id', $request->user_id)->whereNull('tanggal')->delete();
            
            \App\Models\UserJadwal::create([
                'user_id' => $request->user_id,
                'shift_uuid' => $request->shift_uuid,
                'tanggal' => null
            ]);
            
            return redirect()->back()->with('success', 'Jadwal Permanen berhasil diaktifkan!')->with('active_tab', 'penugasan');
        } else {
            if (!$request->tanggal) {
                return redirect()->back()->withErrors(['Tanggal wajib diisi jika bukan jadwal permanen.'])->with('active_tab', 'penugasan');
            }
            
            $dates = explode(',', $request->tanggal);
            foreach ($dates as $date) {
                $date = trim($date);
                if ($date) {
                    \App\Models\UserJadwal::updateOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'tanggal' => $date
                        ],
                        [
                            'shift_uuid' => $request->shift_uuid
                        ]
                    );
                }
            }
            return redirect()->back()->with('success', 'Jadwal Khusus berhasil ditambahkan!')->with('active_tab', 'penugasan');
        }
    }

    public function updatePenugasan(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required',
            'shift_uuid' => 'required',
            'tanggal' => 'nullable|string',
        ]);

        if ($request->has('is_permanent')) {
            // Cek apakah bukan dirinya sendiri yang akan diubah, hapus permanen lain
            \App\Models\UserJadwal::where('user_id', $request->user_id)
                ->whereNull('tanggal')
                ->where('id', '!=', $id)
                ->delete();

            \App\Models\UserJadwal::where('id', $id)->update([
                'user_id' => $request->user_id,
                'shift_uuid' => $request->shift_uuid,
                'tanggal' => null
            ]);
        } else {
            if (!$request->tanggal) {
                return redirect()->back()->withErrors(['Tanggal wajib diisi jika bukan jadwal permanen.'])->with('active_tab', 'penugasan');
            }
            
            \App\Models\UserJadwal::where('id', $id)->update([
                'user_id' => $request->user_id,
                'shift_uuid' => $request->shift_uuid,
                'tanggal' => trim($request->tanggal)
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui!')->with('active_tab', 'penugasan');
    }

    public function deletePenugasan($id)
    {
        \App\Models\UserJadwal::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Jadwal karyawan berhasil dihapus!')->with('active_tab', 'penugasan');
    }

    public function storeShift(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'nama' => 'required|string',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
        ]);

        Shift::create([
            'uuid' => Str::uuid(),
            'store_id' => $request->store_id,
            'nama' => $request->nama,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ]);

        return redirect()->back()->with('success', 'Shift berhasil ditambahkan!')->with('active_tab', 'jadwal');
    }

    public function updateShift(Request $request, $uuid)
    {
        $request->validate([
            'nama' => 'required|string',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
        ]);

        Shift::where('uuid', $uuid)->update([
            'nama' => $request->nama,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ]);

        return redirect()->back()->with('success', 'Shift berhasil diperbarui!')->with('active_tab', 'jadwal');
    }

    public function deleteShift($uuid)
    {
        Shift::where('uuid', $uuid)->delete();
        return redirect()->back()->with('success', 'Shift berhasil dihapus!')->with('active_tab', 'jadwal');
    }
}
