<!-- TAB: REKAP ABSENSI (DB Aggregation) -->
<div id="view-rekap" style="display: none;">

    @php
        $totH = $rekap->sum('total_hadir');
        $totI = $rekap->sum('total_izin');
        $totA = $rekap->sum('total_alpha');
        $totR = $rekap->sum('total_record');
    @endphp

    <div class="rekap-card">
        <div class="rekap-stat">
            <div class="number" style="color: #2E7D32;">{{ $totH }}</div>
            <div class="label">Total Hadir</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #E65100;">{{ $totI }}</div>
            <div class="label">Total Izin</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #C62828;">{{ $totA }}</div>
            <div class="label">Total Alpha</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #0081C9;">{{ $totR }}</div>
            <div class="label">Total Record</div>
        </div>
    </div>

    <div class="table-container">
        <table class="fitur-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>KARYAWAN</th>
                    <th class="text-center" style="color:#2E7D32;">HADIR</th>
                    <th class="text-center" style="color:#E65100;">IZIN</th>
                    <th class="text-center" style="color:#C62828;">ALPHA</th>
                    <th class="text-center">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $idx => $r)
                    <tr class="searchable-row">
                        <td>{{ $rekap->firstItem() + $idx }}</td>
                        <td style="font-weight: 600;">{{ $r->username }}</td>
                        <td class="text-center">
                            <span class="status-badge status-hadir">{{ $r->total_hadir }}</span>
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-izin">{{ $r->total_izin }}</span>
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-alpha">{{ $r->total_alpha }}</span>
                        </td>
                        <td class="text-center" style="font-weight:700;">{{ $r->total_record }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Belum ada data rekap untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rekap->hasPages())
        <div class="pagination-container">
            {{ $rekap->onEachSide(1)->appends(request()->except('page'))->appends(['active_tab' => 'rekap'])->links('vendor.pagination.twins') }}
        </div>
    @endif
</div>
