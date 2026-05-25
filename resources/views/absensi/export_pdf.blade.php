<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Riwayat Absensi</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0081C9; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #0081C9; }
        .info { margin-bottom: 20px; line-height: 1.6; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th { background-color: #EEF7FF; color: #0081C9; padding: 10px; border: 1px solid #5EB7EB; text-align: left; }
        td { padding: 8px; border: 1px solid #eee; }
        tr:nth-child(even) { background-color: #fafafa; }
        
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; display: inline-block; }
        .badge-hadir { background: #dcfce7; color: #166534; }
        .badge-izin { background: #ffedd5; color: #9a3412; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }
        h4 { color: #0081C9; border-bottom: 1px dashed #5EB7EB; padding-bottom: 5px; margin-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">TWINS SYSTEM</div>
        <div style="font-size: 16px; margin-top: 5px;">LAPORAN RIWAYAT ABSENSI</div>
    </div>

    <div class="info">
        <strong>Tanggal Cetak:</strong> {{ date('d F Y') }}<br>
        <strong>Outlet:</strong> {{ $outlet_name }}<br>
        <strong>Filter Bulan:</strong> {{ $filterBulan ? \Carbon\Carbon::parse($filterBulan)->translatedFormat('F Y') : 'Semua Bulan' }}<br>
        <strong>Filter Karyawan:</strong> {{ $filterKaryawan ?: 'Semua Karyawan' }}<br>
        <strong>Dicetak Oleh:</strong> {{ Auth::user()->name }}
    </div>

    <h4>Daftar Riwayat Absensi</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Outlet</th>
                <th>Shift</th>
                <th>Check-in</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat ?? [] as $i => $r)
                @php
                    $st = strtolower($r->status_kehadiran ?? '');
                    $cls = 'badge-hadir';
                    if ($st == 'izin') $cls = 'badge-izin';
                    if ($st == 'alpha') $cls = 'badge-alpha';
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r->tanggal_absensi ? \Carbon\Carbon::parse($r->tanggal_absensi)->format('d/m/Y') : '-' }}</td>
                    <td>
                        <strong>{{ $r->jadwal->user->name ?? '-' }}</strong><br>
                        <small style="color: #666;">{{ $r->jadwal->user->operator->nama ?? 'Karyawan' }}</small>
                    </td>
                    <td>{{ $r->store->nama ?? '-' }}</td>
                    <td>
                        @if($r->jadwal && $r->jadwal->shift)
                            {{ $r->jadwal->shift->nama }}<br>
                            <small style="color: #666;">{{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_selesai)->format('H:i') }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $r->waktu_check_in ? \Carbon\Carbon::parse($r->waktu_check_in)->format('H:i') : '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $cls }}">{{ ucfirst($r->status_kehadiran ?? '-') }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada data riwayat absensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Point of Sale Toko Bahan Kue Twins pada {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
