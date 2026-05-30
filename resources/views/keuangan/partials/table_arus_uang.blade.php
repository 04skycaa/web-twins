        <div class="main-content-box" style="background: transparent; padding: 20px; box-shadow: none;">
            <div class="grid-dashboard" style="padding-bottom: 10px;">
                <div class="finance-card">
                    <div class="icon-box bg-bersih"><iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Bersih</div>
                        <div class="card-value">Rp {{ number_format($saldo_bersih, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="finance-card">
                    <div class="icon-box bg-masuk"><iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Masuk</div>
                        <div class="card-value text-masuk">Rp {{ number_format($pemasukan, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="finance-card">
                    <div class="icon-box bg-keluar"><iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Keluar</div>
                        <div class="card-value text-keluar">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="table-container" style="background: white; padding: 24px; border-radius: 24px; border: 1px solid #f1f5f9;">
                <div class="mobile-action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; flex-shrink: 0;">Histori Transaksi</h3>
                    <div class="filter-pills mobile-inline-flex" style="flex-shrink: 0;">
                        <button type="button" class="filter-pill active" onclick="filterHistoryType('semua', this)">Semua</button>
                        <button type="button" class="filter-pill" onclick="filterHistoryType('pemasukan', this)">Masuk</button>
                        <button type="button" class="filter-pill" onclick="filterHistoryType('pengeluaran', this)">Keluar</button>
                    </div>
                </div>

                <table class="fitur-table" id="arusUangTable">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>KETERANGAN</th>
                            <th>OUTLET</th>
                            <th>JENIS</th>
                            <th style="text-align: right;">NOMINAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                            <tr class="history-row" data-jenis="{{ $item->jenis }}">
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }} <br> <span style="font-size: 11px; color: #94a3b8;">{{ \Carbon\Carbon::parse($item->tanggal)->format('H:i') }}</span></td>
                                <td>
                                    <div style="font-weight: 600; color: #334155;">{{ $item->keterangan }}</div>
                                    <div style="font-size: 11px; color: #64748b;">Oleh: {{ $item->user->name ?? $item->user->username ?? '-' }}</div>
                                </td>
                                <td>{{ $item->outlet->nama ?? '-' }}</td>
                                <td style="white-space: nowrap; text-align: center;"><span class="status-badge {{ $item->jenis == 'pemasukan' ? 'badge-masuk' : 'badge-keluar' }}" style="white-space: nowrap;">{{ $item->jenis }}</span></td>
                                <td style="text-align: right; font-weight: 700; white-space: nowrap; color: {{ $item->jenis == 'pemasukan' ? '#16a34a' : '#dc2626' }};">
                                    {{ $item->jenis == 'pemasukan' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada riwayat transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="twins-pagination-container" id="historyPagination" style="margin-top: 24px;"></div>
            </div>
        </div>
