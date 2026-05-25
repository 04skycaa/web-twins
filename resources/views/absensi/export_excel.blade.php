<table>
    <tr><th colspan="7" style="font-size: 18px; font-weight: bold; text-align: center;">LAPORAN RIWAYAT ABSENSI - TWINS</th></tr>
    <tr><th colspan="7" style="text-align: center; color: #666;">Outlet: {{ $outlet_name }}</th></tr>
    <tr><th colspan="7" style="text-align: center; color: #666;">Bulan: {{ $filterBulan ? \Carbon\Carbon::parse($filterBulan)->translatedFormat('F Y') : 'Semua Bulan' }}</th></tr>
    <tr><th colspan="7" style="text-align: center; color: #666;">Karyawan: {{ $filterKaryawan ?: 'Semua Karyawan' }}</th></tr>
    <tr><th colspan="7"></th></tr>
    
    <tr><th colspan="7" style="background: #E8F5E9; font-weight: bold; border: 1px solid #000;">DAFTAR RIWAYAT ABSENSI</th></tr>
    <tr style="background: #f1f5f9;">
        <th style="border: 1px solid #000; font-weight: bold; text-align: center;">No</th>
        <th style="border: 1px solid #000; font-weight: bold;">Tanggal</th>
        <th style="border: 1px solid #000; font-weight: bold;">Karyawan</th>
        <th style="border: 1px solid #000; font-weight: bold;">Posisi</th>
        <th style="border: 1px solid #000; font-weight: bold;">Outlet</th>
        <th style="border: 1px solid #000; font-weight: bold;">Shift (Masuk - Pulang)</th>
        <th style="border: 1px solid #000; font-weight: bold;">Jam Check-in</th>
        <th style="border: 1px solid #000; font-weight: bold; text-align: center;">Status</th>
    </tr>
    @foreach($riwayat ?? [] as $i => $r)
    <tr>
        <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
        <td style="border: 1px solid #000;">{{ $r->tanggal_absensi ? \Carbon\Carbon::parse($r->tanggal_absensi)->format('d/m/Y') : '-' }}</td>
        <td style="border: 1px solid #000;">{{ $r->jadwal->user->name ?? '-' }}</td>
        <td style="border: 1px solid #000;">{{ $r->jadwal->user->operator->nama ?? 'Karyawan' }}</td>
        <td style="border: 1px solid #000;">{{ $r->store->nama ?? '-' }}</td>
        <td style="border: 1px solid #000;">
            @if($r->jadwal && $r->jadwal->shift)
                {{ $r->jadwal->shift->nama }} ({{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_selesai)->format('H:i') }})
            @else
                -
            @endif
        </td>
        <td style="border: 1px solid #000;">{{ $r->waktu_check_in ? \Carbon\Carbon::parse($r->waktu_check_in)->format('H:i') : '-' }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ ucfirst($r->status_kehadiran ?? '-') }}</td>
    </tr>
    @endforeach
</table>
