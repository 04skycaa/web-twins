<!-- TAB: RIWAYAT ABSENSI (Server-side paginated) -->
<div id="view-riwayat" style="display: none;">
    <div class="table-container">
        <table class="fitur-table">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>KARYAWAN</th>
                    <th>OUTLET</th>
                    <th>SHIFT</th>
                    <th>CHECK-IN</th>
                    <th class="text-center">STATUS</th>
                    <th style="width:120px;text-align:center;">UBAH STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                    <tr class="searchable-row">
                        <td>{{ $r->tanggal_absensi ? \Carbon\Carbon::parse($r->tanggal_absensi)->format('d/m/Y') : '-' }}</td>
                        <td style="font-weight: 600;">
                            {{ $r->jadwal->user->name ?? '-' }}<br>
                            <small style="font-weight:normal;color:#666;">{{ $r->jadwal->user->operator->nama ?? 'Karyawan' }}</small>
                        </td>
                        <td>{{ $r->store->nama ?? '-' }}</td>
                        <td>
                            @if($r->jadwal && $r->jadwal->shift)
                                <span style="font-weight:600;color:#0081C9;">{{ $r->jadwal->shift->nama }}</span><br>
                                <small style="color:#666;">{{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->jadwal->shift->waktu_selesai)->format('H:i') }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $r->waktu_check_in ? \Carbon\Carbon::parse($r->waktu_check_in)->format('H:i') : '-' }}</td>
                        <td class="text-center">
                            @php
                                $st = strtolower($r->status_kehadiran ?? '');
                                $cls = 'status-hadir';
                                if ($st == 'izin') $cls = 'status-izin';
                                if ($st == 'alpha') $cls = 'status-alpha';
                            @endphp
                            <span class="status-badge {{ $cls }}">{{ ucfirst($r->status_kehadiran ?? '-') }}</span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex;gap:4px;align-items:center;justify-content:center;">
                                <select id="status-{{ $r->uuid }}" class="inline-select">
                                    <option value="hadir" {{ $st=='hadir'?'selected':'' }}>Hadir</option>
                                    <option value="izin" {{ $st=='izin'?'selected':'' }}>Izin</option>
                                    <option value="alpha" {{ $st=='alpha'?'selected':'' }}>Alpha</option>
                                </select>
                                <button class="btn-filter" style="width:28px;height:28px;border-radius:6px;font-size:14px;" onclick="updateAbsensiStatus('{{ $r->uuid }}')" title="Simpan">
                                    <iconify-icon icon="solar:check-circle-bold-duotone" style="color:#2E7D32;"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">Belum ada riwayat absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="riwayat-pagination-container" style="padding: 20px 0; border-top: 1px solid #f1f5f9;">
        <div id="riwayat-pagination" class="k-pagination" style="justify-content: center;"></div>
    </div>
</div>
