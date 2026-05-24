@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<style>
    .fitur-layout-wrapper {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-top: 20px;
    }
    .main-content-box {
        flex: 1;
        min-width: 0;
        margin-top: 0 !important;
    }
    .detail-side-panel {
        width: 280px;
        background: white;
        border: 2px solid var(--border-blue);
        border-radius: 20px;
        padding: 16px;
        position: sticky;
        top: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    @media (max-width: 768px) {
        .fitur-layout-wrapper {
            flex-direction: column-reverse;
        }
        .detail-side-panel {
            width: 100%;
            position: static;
        }
    }
    .detail-header {
        margin-bottom: 12px;
    }
    .detail-title {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .detail-store-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-item {
        display: flex;
        gap: 12px;
    }
    .info-icon {
        width: 28px;
        height: 28px;
        background: #f0f9ff;
        color: var(--primary-blue);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .info-content label {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .info-content span {
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        line-height: 1.4;
    }
    .perf-title {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
    }
    .perf-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .perf-card {
        padding: 8px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
    }
    .perf-card label {
        display: block;
        font-size: 9px;
        color: #64748b;
        margin-bottom: 2px;
    }
    .perf-card .value {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }
    .perf-card .sub-value {
        font-size: 9px;
        color: #10b981;
        font-weight: 600;
        margin-top: 2px;
        display: block;
    }
    .outlet-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .outlet-row:hover {
        background-color: #f0f9ff !important;
    }
    .outlet-row.active-row {
        background-color: #e0f2fe !important;
    }
    .detail-side-panel .status-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 6px;
    }

    /* Kinerja Styles */
    .kinerja-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }
    .kpi-card {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-value {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
    }
    .kpi-trend {
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }

    .kinerja-main-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 12px;
    }
    .chart-container-box {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
        height: 100%;
    }
    .summary-table-card {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
    }
    .summary-item-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .summary-item-card:hover {
        background: white;
        border-color: var(--primary-blue);
        transform: scale(1.01);
    }
    .summary-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-val {
        font-weight: 800;
        color: #1e293b;
        font-size: 14px;
    }
    .btn-search-trigger {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        padding: 7px 20px;
        background: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        z-index: 5;
    }

    .btn-search-trigger:hover {
        opacity: 0.9;
        transform: translateY(-50%) scale(1.05);
    }

    .btn-search-trigger:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    @media (max-width: 768px) {
        .kinerja-main-grid { grid-template-columns: 1fr; }
        .kinerja-stats-grid { grid-template-columns: 1fr; display: flex; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 10px; }
        .kpi-card { min-width: 85vw; flex-shrink: 0; scroll-snap-align: center; }
        #top-products-container { grid-template-columns: 1fr !important; display: flex !important; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 10px; }
        #top-products-container > div { min-width: 200px; flex-shrink: 0; scroll-snap-align: center; }
    }
</style>




<div class="fitur-container">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" onclick="switchTab('data')" id="pill-data" class="tab-pill {{ $active_tab == 'data' ? 'active' : '' }}">
            <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
            <span>Data Outlet</span>
        </a>
        <a href="javascript:void(0)" onclick="switchTab('kinerja')" id="pill-kinerja" class="tab-pill {{ $active_tab == 'kinerja' ? 'active' : '' }}">
            <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
            <span>Kinerja Outlet</span>
        </a>
        <a href="javascript:void(0)" onclick="switchTab('riwayat')" id="pill-riwayat" class="tab-pill {{ $active_tab == 'riwayat' ? 'active' : '' }}">
            <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
            <span>Riwayat Stok</span>
        </a>
    </div>

    <div id="view-data" class="tab-view mobile-pb" style="{{ $active_tab == 'data' ? '' : 'display: none;' }}">
        {{-- ACTION BAR --}}
        <div class="action-bar mobile-action-bar">
            <div class="left-actions-group mobile-action-bar" style="width: 100%;">
                <div class="search-wrapper mobile-search-shrink">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="outletSearch" class="search-input" placeholder="Cari nama atau alamat..." oninput="filterOutlets()">
                </div>
                <button class="btn-action" onclick="openModal('addModal')">
                    <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    <span>Tambah Outlet</span>
                </button>
            </div>
        </div>

        <div class="fitur-layout-wrapper">
            {{-- MAIN BOX --}}
            <div class="main-content-box">
                {{-- DESKTOP TABLE --}}
                <div class="table-container outlet-table-desktop">
                    <table class="fitur-table" style="white-space: nowrap;">
                        <thead>
                            <tr>
                                <th>NAMA OUTLET</th>
                                <th>ALAMAT</th>
                                <th>NO. TELP</th>
                                <th>JAM BUKA</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outlets as $index => $outlet)
                            <tr class="outlet-row {{ $index === 0 ? 'active-row' : '' }}" 
                                data-name="{{ strtolower($outlet->nama) }}" 
                                data-address="{{ strtolower($outlet->alamat) }}"
                                data-outlet='@json($outlet)'>
                                <td style="font-weight: 600;">{{ $outlet->nama }}</td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $outlet->alamat ?? '-' }}</td>
                                <td>{{ $outlet->notelp ?? '-' }}</td>
                                <td>
                                    <span class="status-badge" style="background: rgba(14, 165, 233, 0.1); color: var(--accent-purple); border: 1px solid rgba(14, 165, 233, 0.2);">
                                        {{ $outlet->jam_buka ?? '08.00 - 23.59' }}
                                    </span>
                                </td>
                                <td>
                                    @if($outlet->status_aktif)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-inactive">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: var(--primary-blue);" data-item='@json($outlet)' onclick="event.stopPropagation(); openEditModal(JSON.parse(this.dataset.item))" title="Edit Outlet">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: {{ $outlet->status_aktif ? '#ef4444' : '#10b981' }};" onclick="event.stopPropagation(); toggleStatus('{{ $outlet->uuid }}', {{ $outlet->status_aktif ? 'true' : 'false' }})" title="{{ $outlet->status_aktif ? 'Nonaktifkan Outlet' : 'Aktifkan Outlet' }}">
                                            <iconify-icon icon="{{ $outlet->status_aktif ? 'solar:shop-2-bold-duotone' : 'solar:shop-bold-duotone' }}"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: #D9534F; border-color: #ffcccc;" onclick="event.stopPropagation(); openDeleteModal('{{ $outlet->uuid }}')" title="Hapus Outlet">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #999; padding: 40px;">Belum ada data outlet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD GRID --}}
                <div class="outlet-card-grid">
                    @forelse($outlets as $index => $outlet)
                    <div class="outlet-card outlet-row {{ $index === 0 ? 'active-row' : '' }}"
                        data-name="{{ strtolower($outlet->nama) }}"
                        data-address="{{ strtolower($outlet->alamat) }}"
                        data-outlet='@json($outlet)'>

                        <div class="outlet-card-header">
                            <div class="outlet-card-icon">
                                <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                            </div>
                            <div class="outlet-card-info">
                                <div class="outlet-card-name">{{ $outlet->nama }}</div>
                                <div class="outlet-card-address">{{ $outlet->alamat ?? '-' }}</div>
                            </div>
                            <div class="outlet-card-status">
                                @if($outlet->status_aktif)
                                    <span class="status-badge status-active" style="font-size: 10px; padding: 2px 8px;">Aktif</span>
                                @else
                                    <span class="status-badge status-inactive" style="font-size: 10px; padding: 2px 8px;">Nonaktif</span>
                                @endif
                            </div>
                        </div>

                        <div class="outlet-card-body">
                            <div class="outlet-card-row">
                                <div class="outlet-card-label">
                                    <iconify-icon icon="solar:phone-bold-duotone" style="font-size: 14px; color: #0081C9;"></iconify-icon>
                                    No. Telp
                                </div>
                                <span class="outlet-card-value">{{ $outlet->notelp ?? '-' }}</span>
                            </div>
                            <div class="outlet-card-row">
                                <div class="outlet-card-label">
                                    <iconify-icon icon="solar:clock-circle-bold-duotone" style="font-size: 14px; color: #0081C9;"></iconify-icon>
                                    Jam Buka
                                </div>
                                <span class="status-badge" style="background: rgba(14,165,233,0.1); color: #0ea5e9; border: 1px solid rgba(14,165,233,0.2); font-size: 10px; padding: 2px 8px;">{{ $outlet->jam_buka ?? '08.00 - 23.59' }}</span>
                            </div>
                        </div>

                        <div class="outlet-card-footer">
                            <button type="button" class="outlet-card-btn btn-outlet-edit"
                                data-item='@json($outlet)'
                                onclick="event.stopPropagation(); openEditModal(JSON.parse(this.dataset.item))">
                                <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                Edit
                            </button>
                            <button type="button" class="outlet-card-btn {{ $outlet->status_aktif ? 'btn-outlet-deactivate' : 'btn-outlet-activate' }}"
                                onclick="event.stopPropagation(); toggleStatus('{{ $outlet->uuid }}', {{ $outlet->status_aktif ? 'true' : 'false' }})">
                                <iconify-icon icon="{{ $outlet->status_aktif ? 'solar:shop-2-bold-duotone' : 'solar:shop-bold-duotone' }}"></iconify-icon>
                                {{ $outlet->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            <button type="button" class="outlet-card-btn btn-outlet-delete"
                                onclick="event.stopPropagation(); openDeleteModal('{{ $outlet->uuid }}')">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                Hapus
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="outlet-card-empty">
                        <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 48px; color: #cbd5e1;"></iconify-icon>
                        <p>Belum ada data outlet</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- DETAIL SIDE PANEL --}}
            <div class="detail-side-panel">
                <div id="sideDetailContent">
                    @if(count($outlets ?? []) > 0)
                        @php $first = $outlets[0]; @endphp
                        <div class="detail-header">
                            <div class="detail-title">Detail Outlet</div>
                            <div class="detail-store-name">
                                <span id="side_nama">{{ $first->nama }}</span>
                                <span id="side_status">
                                    @if($first->status_aktif)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-inactive">Nonaktif</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-list">
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Alamat</label>
                                    <span id="side_alamat">{{ $first->alamat ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:user-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Kepala Toko</label>
                                    <span id="side_kepala">{{ $first->users->where('role', 'kepala_toko')->first()->username ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:phone-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>No. Telepon</label>
                                    <span id="side_notelp">{{ $first->notelp ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:letter-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Email</label>
                                    <span id="side_email">{{ $first->users->where('role', 'kepala_toko')->first()->email ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Jam Operasional</label>
                                    <span id="side_jam">{{ $first->jam_buka ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="perf-title">Ringkasan Performa Outlet</div>
                        <div class="perf-grid">
                            <div class="perf-card">
                                <label>Omzet</label>
                                <span class="value" id="side_omzet"><iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon></span>
                                <span class="sub-value">Gabungan POS & Online</span>
                            </div>
                            <div class="perf-card">
                                <label>Transaksi</label>
                                <span class="value" id="side_transaksi"><iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon></span>
                                <span class="sub-value">Gabungan POS & Online</span>
                            </div>
                            <div class="perf-card">
                                <label>Produk Terlaris</label>
                                <span class="value" id="side_terlaris"><iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon></span>
                                <span style="font-size: 10px; color: #64748b;" id="side_terlaris_qty">Memuat...</span>
                            </div>
                            <div class="perf-card">
                                <label>Stok Menipis</label>
                                <span class="value" id="side_stok"><iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon></span>
                                <a href="{{ route('products.request') }}" style="font-size: 10px; color: var(--primary-blue); text-decoration: none; font-weight: 600;">Lihat Detail ></a>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; color: #94a3b8; padding: 40px 0;">
                            <iconify-icon icon="solar:shop-linear" style="font-size: 48px; margin-bottom: 12px;"></iconify-icon>
                            <p>Pilih outlet untuk melihat detail</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- VIEW KINERJA --}}
    <div id="view-kinerja" class="tab-view mobile-pb" style="{{ $active_tab == 'kinerja' ? '' : 'display: none;' }}">
        <!-- Include Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <div class="kinerja-header-container">
            <div>
                <h2 class="kinerja-title">Analytics Kinerja Outlet</h2>
                <p class="kinerja-subtitle">Pantau performa finansial dan operasional secara real-time.</p>
            </div>
            <div class="kinerja-filters">
                <select id="k_outlet_filter" class="k-input" onchange="loadKinerjaDashboard()">
                    <option value="all">Semua Outlet</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->uuid }}">{{ $outlet->nama }}</option>
                    @endforeach
                </select>
                <div class="dropdown" style="position: relative;">
                    <button type="button" class="btn-filter" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; padding: 0; background: white; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s;" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px; color: var(--primary-blue);"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 300px; right: 0; left: auto; top: 48px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px; font-weight: 600;">Dari Tanggal</label>
                                <input type="date" id="k_start_date" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px; font-weight: 600;">Sampai Tanggal</label>
                                <input type="date" id="k_end_date" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; box-sizing: border-box;">
                            </div>
                            <button type="button" class="btn-action" style="width: 100%; justify-content: center; padding: 10px; background: var(--primary-blue); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;" onclick="loadKinerjaDashboard(); document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));">
                                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon> Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Top Cards -->
        <div class="k-stats-row">
            <div class="k-stat-card">
                <div class="k-icon-wrapper k-bg-blue"><iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon></div>
                <div class="k-stat-info">
                    <label>Omset Penjualan</label>
                    <div class="k-val" id="k_val_omset"><div class="skeleton-text"></div></div>
                </div>
            </div>
            <div class="k-stat-card">
                <div class="k-icon-wrapper k-bg-yellow"><iconify-icon icon="solar:box-bold-duotone"></iconify-icon></div>
                <div class="k-stat-info">
                    <label>Laba Kotor</label>
                    <div class="k-val" id="k_val_labakotor"><div class="skeleton-text"></div></div>
                </div>
            </div>
            <div class="k-stat-card">
                <div class="k-icon-wrapper k-bg-green"><iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon></div>
                <div class="k-stat-info">
                    <label>Laba Bersih</label>
                    <div class="k-val" id="k_val_lababersih"><div class="skeleton-text"></div></div>
                </div>
            </div>
        </div>

        <div class="k-main-grid">
            <!-- Left Column: Charts -->
            <div class="k-col-left">
                <!-- 5. Grafik Penjualan -->
                <div class="k-card">
                    <h3 class="k-card-title">Trend Penjualan</h3>
                    <div class="k-chart-container">
                        <canvas id="k_line_chart"></canvas>
                    </div>
                </div>

                <!-- 1 & 2. Arus Kas dan Ringkasan Performa -->
                <div class="k-grid-2">
                    <div class="k-card">
                        <h3 class="k-card-title">Komposisi Arus Kas</h3>
                        <div style="height: 200px; position:relative;">
                            <canvas id="k_donut_chart"></canvas>
                        </div>
                    </div>
                    <div class="k-card">
                        <h3 class="k-card-title">Ringkasan Performa</h3>
                        <div class="k-summary-list">
                            <div class="k-summary-item">
                                <span>Total Transaksi</span>
                                <strong id="k_sum_trx"><div class="skeleton-text" style="width:40px;"></div></strong>
                            </div>
                            <div class="k-summary-item">
                                <span>Rata-Rata Transaksi</span>
                                <strong id="k_sum_avg"><div class="skeleton-text" style="width:80px;"></div></strong>
                            </div>
                            <div class="k-summary-item">
                                <span>Total Item Terjual</span>
                                <strong id="k_sum_items"><div class="skeleton-text" style="width:100px;"></div></strong>
                            </div>
                            <div class="k-summary-item">
                                <span>Total Pengeluaran</span>
                                <strong id="k_sum_expenses"><div class="skeleton-text" style="width:100px;"></div></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- removed table -->
            </div>
            <div class="k-col-right">
                <!-- 4. Produk Terlaris -->
                <div class="k-card">
                    <h3 class="k-card-title">Top 5 Produk Terlaris</h3>
                    <div class="k-progress-list" id="k_top_products">
                        <div class="skeleton-text" style="height:40px; margin-bottom:10px;"></div>
                        <div class="skeleton-text" style="height:40px; margin-bottom:10px;"></div>
                        <div class="skeleton-text" style="height:40px; margin-bottom:10px;"></div>
                    </div>
                </div>

                <!-- 6. Outlet Perlu Perhatian -->
                <div class="k-card" style="background:#f8fafc;">
                    <h3 class="k-card-title" style="color:#dc2626;">Perlu Perhatian!</h3>
                    <div class="k-alert-list" id="k_alerts">
                        <div class="skeleton-text" style="height:50px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Performa Outlet Table (Full Width) -->
        <div class="k-card" style="margin-top: 20px;">
            <h3 class="k-card-title">Performa Outlet</h3>
            <div class="table-container">
                <table class="fitur-table" id="k_table_outlet" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>Nama Outlet</th>
                            <th>Omset</th>
                            <th>Transaksi</th>
                            <th>Laba Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="4"><div class="skeleton-text"></div></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="k-pagination" id="k_table_pagination"></div>
        </div>



        <!-- Dashboard JavaScript -->
        <script>
            let kLineChartInstance = null;
            let kDonutChartInstance = null;

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
            }

            function getKinerjaFilters() {
                return {
                    store_id: document.getElementById('k_outlet_filter').value,
                    start_date: document.getElementById('k_start_date').value,
                    end_date: document.getElementById('k_end_date').value
                };
            }

            async function fetchKinerjaAPI(endpoint, params = {}) {
                const query = new URLSearchParams({...getKinerjaFilters(), ...params}).toString();
                const res = await fetch(`/outlet/api/kinerja/${endpoint}?${query}`);
                return res.json();
            }

            async function loadKinerjaDashboard() {
                // Skeleton UI toggle
                document.querySelectorAll('.k-val').forEach(el => el.innerHTML = '<div class="skeleton-text"></div>');
                document.getElementById('k_sum_trx').innerHTML = '<div class="skeleton-text"></div>';
                
                // Fetch Async Paralel
                Promise.all([
                    fetchKinerjaAPI('statistik-utama'),
                    fetchKinerjaAPI('arus-kas'),
                    fetchKinerjaAPI('ringkasan'),
                    fetchKinerjaAPI('grafik'),
                    fetchKinerjaAPI('terlaris'),
                    fetchKinerjaAPI('perhatian'),
                    loadKinerjaTable(1)
                ]).then(([stat, arus, ringkasan, grafik, terlaris, perhatian]) => {
                    // Update Stat Cards
                    document.getElementById('k_val_omset').innerText = formatRupiah(stat.omset);
                    document.getElementById('k_val_labakotor').innerText = formatRupiah(stat.laba_kotor);
                    document.getElementById('k_val_lababersih').innerText = formatRupiah(stat.laba_bersih);

                    // Update Ringkasan
                    document.getElementById('k_sum_trx').innerText = ringkasan.total_transaksi + ' TRX';
                    document.getElementById('k_sum_avg').innerText = formatRupiah(ringkasan.rata_rata_transaksi);
                    document.getElementById('k_sum_items').innerText = ringkasan.total_item_terjual + ' Pcs';
                    document.getElementById('k_sum_expenses').innerText = formatRupiah(ringkasan.total_pengeluaran);

                    // Update Charts
                    renderKinerjaCharts(grafik, arus);

                    // Update Produk Terlaris
                    renderTopProducts(terlaris);

                    // Update Alerts
                    renderAlerts(perhatian);
                }).catch(err => {
                    console.error("Gagal memuat dashboard: ", err);
                    document.querySelectorAll('.k-val').forEach(el => el.innerHTML = '<div style="color:red; font-size:14px;">Error</div>');
                });
            }

            function renderKinerjaCharts(grafik, arus) {
                // Line Chart
                const ctxLine = document.getElementById('k_line_chart').getContext('2d');
                if (kLineChartInstance) kLineChartInstance.destroy();
                kLineChartInstance = new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: grafik.labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: grafik.data,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                // Donut Chart
                const ctxDonut = document.getElementById('k_donut_chart').getContext('2d');
                if (kDonutChartInstance) kDonutChartInstance.destroy();
                kDonutChartInstance = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: ['Penjualan', 'Pembelian Stok', 'Biaya Operasional'],
                        datasets: [{
                            data: [arus.pemasukan_penjualan, arus.pembelian_stok, arus.biaya_operasional],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
                });
            }

            function renderTopProducts(products) {
                const container = document.getElementById('k_top_products');
                if (products.length === 0) {
                    container.innerHTML = '<div style="color:#94a3b8;font-size:12px;">Belum ada data</div>';
                    return;
                }
                container.innerHTML = products.map(p => `
                    <div class="k-prog-item">
                        <div class="k-prog-header">
                            <span class="k-prog-name">${p.nama}</span>
                            <span class="k-prog-val">${p.qty}x</span>
                        </div>
                        <div class="k-prog-track">
                            <div class="k-prog-fill" style="width: ${p.percentage}%"></div>
                        </div>
                    </div>
                `).join('');
            }

            function renderAlerts(alerts) {
                const container = document.getElementById('k_alerts');
                container.innerHTML = alerts.map(a => `
                    <div class="k-alert k-alert-${a.warna}">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                        <span>${a.pesan}</span>
                    </div>
                `).join('');
            }

            async function loadKinerjaTable(page) {
                const res = await fetchKinerjaAPI('performa', {page});
                const tbody = document.querySelector('#k_table_outlet tbody');
                
                if (res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#94a3b8;">Tidak ada data outlet</td></tr>';
                    return;
                }

                tbody.innerHTML = res.data.map(o => `
                    <tr>
                        <td style="font-weight:700; color:#1e293b;">${o.nama_outlet}</td>
                        <td style="font-weight:600; color:#3b82f6;">${formatRupiah(o.omset)}</td>
                        <td><span class="status-badge" style="background:#f0f9ff;color:#0ea5e9;">${o.jumlah_transaksi} TRX</span></td>
                        <td style="font-weight:700; color:${o.laba_bersih >= 0 ? '#10b981' : '#ef4444'};">${formatRupiah(o.laba_bersih)}</td>
                    </tr>
                `).join('');

                // Pagination logic
                let paginationHtml = '';
                if(res.last_page > 1) {
                    for(let i=1; i<=res.last_page; i++){
                        paginationHtml += `<button class="k-page-btn ${i===res.current_page?'active':''}" onclick="loadKinerjaTable(${i})">${i}</button>`;
                    }
                }
                document.getElementById('k_table_pagination').innerHTML = paginationHtml;
            }

            // Run when tab activated
            function initKinerjaTab() {
                loadKinerjaDashboard();
            }
            
            document.addEventListener('DOMContentLoaded', () => {
                if ('{{ $active_tab }}' === 'kinerja') initKinerjaTab();
            });
        </script>
    </div>

    {{-- MODAL DETAIL PRODUK --}}
    <div id="productDetailModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content" style="background: white; width: 320px; border-radius: 24px; padding: 24px; position: relative; border: 2px solid var(--border-blue); box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
            <button onclick="closeProductDetails()" style="position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;">
                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
            </button>
            <div style="text-align: center;">
                <img id="modal-product-img" src="" style="width: 120px; height: 120px; border-radius: 20px; object-fit: cover; margin-bottom: 16px; border: 3px solid #f8fafc; box-shadow: 0 8px 16px rgba(0,0,0,0.05);">
                <h3 id="modal-product-name" style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">Nama Produk</h3>
                <span style="font-size: 12px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 4px 12px; border-radius: 50px;">Produk Terlaris</span>
                
                <div style="margin-top: 24px; display: grid; gap: 12px;">
                    <div style="background: #f0fdf4; padding: 12px; border-radius: 16px; border: 1px solid #dcfce7;">
                        <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;">Total Terjual</div>
                        <div id="modal-product-qty" style="font-size: 24px; font-weight: 800; color: #16a34a;">0</div>
                        <div style="font-size: 10px; color: #16a34a; opacity: 0.8;">Bulan ini</div>
                    </div>
                </div>
                
                <button onclick="closeProductDetails()" class="btn-action" style="width: 100%; margin-top: 20px; justify-content: center;">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>


    {{-- VIEW RIWAYAT --}}
    <div id="view-riwayat" class="tab-view mobile-pb" style="{{ $active_tab == 'riwayat' ? '' : 'display: none;' }}">
        <!-- Header Section (Standard Layout) -->
        <div class="action-bar mobile-action-bar">
            <div class="left-actions-group mobile-action-bar" style="width: 100%;">
                <!-- Search Box (Standard) -->
                <div class="search-wrapper mobile-search-shrink">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="stock-search" class="search-input" oninput="debounceSearch()" placeholder="Cari produk atau barcode...">
                </div>

                <!-- Outlet Filter -->
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Outlet" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="setStockOutlet('all')" class="active-dropdown-item">Semua Outlet</a>
                        @foreach($outlets as $outlet)
                            <a href="javascript:void(0)" onclick="setStockOutlet('{{ $outlet->uuid }}')">{{ $outlet->nama }}</a>
                        @endforeach
                    </div>
                    <input type="hidden" id="stock-outlet-filter" value="all">
                </div>

                <!-- Date Range Filter -->
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Rentang Waktu" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 20px; width: 300px; left: 0; right: auto;">
                        <div style="font-size: 12px; font-weight: 800; color: var(--primary-blue); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Rentang Waktu</div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">DARI TANGGAL</label>
                            <input type="date" id="stock-start-date" class="form-control" onchange="applyStockFilters()">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">SAMPAI TANGGAL</label>
                            <input type="date" id="stock-end-date" class="form-control" onchange="applyStockFilters()">
                        </div>
                        <button type="button" class="btn-action" style="width: 100%; justify-content: center;" onclick="applyStockFilters()">
                            <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                <!-- Reset -->
                <button onclick="resetStockFilters()" class="btn-filter" title="Reset Filter">
                    <iconify-icon icon="solar:restart-bold-duotone" style="font-size: 24px;"></iconify-icon>
                </button>
                
                <!-- Extract moved here -->
                <div class="dropdown">
                    <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content" style="right: 0; left: auto;">
                        <a href="javascript:void(0)" onclick="exportStock('excel')">
                            <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                            Excel (.xlsx)
                        </a>
                        <a href="javascript:void(0)" onclick="exportStock('pdf')">
                            <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                            PDF Document
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Standard Layout) -->
        <div class="main-content-box">
            <div id="stock-history-wrapper">
                @fragment('stock-history-table')
                <div class="table-container">
                    <table class="fitur-table" style="white-space: nowrap;">
                        <thead>
                            <tr>
                                <th style="width: 150px;">WAKTU</th>
                                <th>OUTLET</th>
                                <th>PRODUK</th>
                                <th style="width: 100px; text-align: center;">MUTASI</th>
                                <th style="padding-left: 20px;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody id="stock-history-table-body">
                        @foreach($stockHistory as $item)
                        <tr class="stock-row" data-outlet="{{ $item->store_id }}" data-date="{{ $item->created_at->format('Y-m-d') }}">
                            <td data-label="WAKTU" style="color: #64748b; font-size: 12px; white-space: nowrap;">
                                {{ $item->created_at->format('d/m/Y') }}
                                <div style="font-size: 10px; opacity: 0.7;">{{ $item->created_at->format('H:i:s') }}</div>
                            </td>
                            <td data-label="OUTLET" style="font-weight: 700; color: var(--primary-blue);">
                                {{ $item->store->nama ?? '-' }}
                            </td>
                            <td data-label="PRODUK">
                                <div class="product-info">
                                    <img src="{{ $item->product->resolved_image_url ?? asset('images/placeholder-product.png') }}" class="product-img">
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-width: 150px; max-width: 250px; line-height: 1.4;">{{ $item->product->nama_produk ?? '-' }}</div>
                                        <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">{{ $item->product->barcode ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="MUTASI" style="text-align: center;">
                                <div style="font-size: 16px; font-weight: 900; color: {{ $item->jmlh > 0 ? '#16a34a' : '#ef4444' }}; background: {{ $item->jmlh > 0 ? '#f0fdf4' : '#fef2f2' }}; padding: 8px; border-radius: 10px; border: 1px solid {{ $item->jmlh > 0 ? '#dcfce7' : '#fee2e2' }};">
                                    {{ $item->jmlh > 0 ? '+' : '' }}{{ $item->jmlh }}
                                </div>
                            </td>
                            <td data-label="KETERANGAN" style="padding-left: 20px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @php
                                        $icon = 'solar:info-circle-bold-duotone';
                                        $color = '#5ab2e8';
                                        $label = strtolower($item->keterangan);
                                        if(str_contains($label, 'penjualan')) { $icon = 'solar:cart-large-minimalistic-bold-duotone'; $color = '#3b82f6'; }
                                        elseif(str_contains($label, 'restock')) { $icon = 'solar:box-bold-duotone'; $color = '#10b981'; }
                                        elseif(str_contains($label, 'opname')) { $icon = 'solar:clipboard-check-bold-duotone'; $color = '#f59e0b'; }
                                        elseif(str_contains($label, 'transfer')) { $icon = 'solar:transfer-horizontal-bold-duotone'; $color = '#8b5cf6'; }
                                    @endphp
                                    <iconify-icon icon="{{ $icon }}" style="font-size: 20px; color: {{ $color }}; flex-shrink: 0;"></iconify-icon>
                                    <span style="font-weight: 600; color: #475569; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-width: 150px; max-width: 300px; line-height: 1.4;">{{ $item->keterangan }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="twins-pagination-container" style="margin-top: 24px; padding-bottom: 24px;">
                    {{ $stockHistory->onEachSide(1)->appends(request()->query())->links('vendor.pagination.twins') }}
                </div>
                @endfragment
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Tambah Outlet Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('outlet.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: TWINS Bakery Pusat" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Jl. Raya No. 123..."></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" class="form-control" placeholder="08123456789">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" class="form-control" placeholder="Contoh: 08.00 - 22.00" value="08.00 - 23.59">
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Edit Outlet</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" id="edit_alamat" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" id="edit_notelp" class="form-control">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" id="edit_jam_buka" class="form-control">
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View -->
<div id="viewModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Detail Outlet</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">NAMA OUTLET</label>
                <div id="view_nama" style="font-weight: 600; color: #334155; font-size: 16px;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">ALAMAT</label>
                <div id="view_alamat" style="font-weight: 500; color: #334155;">-</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">NO. TELP</label>
                    <div id="view_notelp" style="font-weight: 600; color: #334155;">-</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">JAM OPERASIONAL</label>
                    <div id="view_jam_buka" style="font-weight: 600; color: #334155;">-</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">RATING</label>
                    <div id="view_rating" style="font-weight: 700; color: #f59e0b; display: flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="solar:star-bold"></iconify-icon>
                        <span>-</span>
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">STATUS</label>
                    <div id="view_status">-</div>
                </div>
            </div>
        </div>
        <div style="padding: 0 20px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    let currentTab = '{{ $active_tab }}';
    window.stockHistoryLoaded = (currentTab === 'riwayat');

    function switchTab(tabId) {
        currentTab = tabId;
        
        // Reset pills
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        // Hide all views
        document.querySelectorAll('.tab-view').forEach(v => v.style.display = 'none');
        
        // Show active view
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.style.display = 'block';

        // Trigger kinerja data load when switching to kinerja tab
        if (tabId === 'kinerja' && typeof loadKinerjaDashboard === 'function') {
            setTimeout(() => loadKinerjaDashboard(), 100);
        }

        // Trigger stock load when switching to riwayat tab
        if (tabId === 'riwayat') {
            const tableBody = document.getElementById('stock-history-table-body');
            if (tableBody && tableBody.querySelectorAll('.stock-row').length === 0) {
                setTimeout(() => {
                    if (typeof applyStockFilters === 'function') applyStockFilters();
                }, 100);
            }
        }

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('active_tab', tabId);
        window.history.pushState({}, '', url);
    }

    async function selectOutlet(row, data) {
        // Remove active class from all rows
        document.querySelectorAll('.outlet-row').forEach(r => r.classList.remove('active-row'));
        // Add active class to clicked row
        row.classList.add('active-row');

        // Loading states
        document.getElementById('side_nama').innerText = data.nama;
        document.getElementById('side_alamat').innerText = data.alamat || '-';
        document.getElementById('side_notelp').innerText = data.notelp || '-';
        document.getElementById('side_jam').innerText = data.jam_buka || '-';
        document.getElementById('side_omzet').innerHTML = '<iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon>';
        document.getElementById('side_transaksi').innerHTML = '<iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon>';
        document.getElementById('side_terlaris').innerHTML = '<iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon>';
        document.getElementById('side_terlaris_qty').innerText = '';
        document.getElementById('side_stok').innerHTML = '<iconify-icon icon="solar:spinner-linear" class="spin"></iconify-icon>';
        
        // Update status badge in side panel
        const statusEl = document.getElementById('side_status');
        if (data.status_aktif) {
            statusEl.innerHTML = '<span class="status-badge status-active">Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive">Nonaktif</span>';
        }

        try {
            const response = await fetch(`/outlet/${data.uuid}/stats`);
            if (!response.ok) throw new Error('Network response was not ok');
            const stats = await response.json();

            document.getElementById('side_kepala').innerText = stats.kepala;
            document.getElementById('side_email').innerText = stats.email;
            
            document.getElementById('side_omzet').innerText = 'Rp ' + parseInt(stats.omzet).toLocaleString('id-ID');
            document.getElementById('side_transaksi').innerText = stats.total_transaksi.toLocaleString('id-ID');
            
            document.getElementById('side_terlaris').innerText = stats.produk_terlaris;
            document.getElementById('side_terlaris_qty').innerText = stats.terlaris_qty > 0 ? `${stats.terlaris_qty} pcs terjual` : 'Belum ada penjualan';
            
            document.getElementById('side_stok').innerText = `${stats.stok_menipis} Produk`;
            
        } catch (error) {
            console.error('Error fetching outlet stats:', error);
            document.getElementById('side_omzet').innerText = 'Gagal memuat';
            document.getElementById('side_transaksi').innerText = 'Gagal memuat';
            document.getElementById('side_terlaris').innerText = '-';
            document.getElementById('side_stok').innerText = '-';
        }
    }

    function openViewModal(data) {
        document.getElementById('view_nama').innerText = data.nama;
        document.getElementById('view_alamat').innerText = data.alamat || '-';
        document.getElementById('view_notelp').innerText = data.notelp || '-';
        document.getElementById('view_jam_buka').innerText = data.jam_buka || '-';
        document.getElementById('view_rating').querySelector('span').innerText = parseFloat(data.rating || 0).toFixed(1);
        
        const statusEl = document.getElementById('view_status');
        if (data.status_aktif) {
            statusEl.innerHTML = '<span class="status-badge status-active">Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive">Nonaktif</span>';
        }
        
        openModal('viewModal');
    }

    function openEditModal(data) {
        document.getElementById('editForm').action = `/outlet/${data.uuid}`;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_alamat').value = data.alamat || '';
        document.getElementById('edit_notelp').value = data.notelp || '';
        document.getElementById('edit_jam_buka').value = data.jam_buka || '';
        openModal('editModal');
    }

    function toggleStatus(id, isAktif) {
        const action = isAktif ? 'Nonaktifkan' : 'Aktifkan';
        Swal.fire({
            title: `${action} Outlet?`,
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} outlet ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isAktif ? '#ef4444' : '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/outlet/${id}/toggle-status`;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function openDeleteModal(id) {
        Swal.fire({
            title: 'Hapus Outlet?',
            text: "Data outlet dan relasi terkait akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/outlet/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function filterOutlets() {
        const search = document.getElementById('outletSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.outlet-row');
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const address = row.dataset.address;
            if (name.includes(search) || address.includes(search)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Handle Row Clicks — DOM elements already rendered above this script
    document.querySelectorAll('.outlet-row').forEach(row => {
        row.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.outlet);
            selectOutlet(this, data);
        });
    });

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Oops...', text: "{{ session('error') }}" });
    @endif
</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>


    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        const isOpen = dropdown.classList.contains('show');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        
        if (!isOpen) {
            dropdown.classList.add('show');
        }
    }

    // Close dropdowns when clicking outside
    window.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    });

    function setStockOutlet(uuid) {
        document.getElementById('stock-outlet-filter').value = uuid;
        
        // Update active class in dropdown
        const items = document.querySelectorAll('#view-riwayat .dropdown-content a');
        items.forEach(item => {
            if (item.getAttribute('onclick') && item.getAttribute('onclick').includes(uuid)) {
                item.classList.add('active-dropdown-item');
            } else {
                item.classList.remove('active-dropdown-item');
            }
        });

        applyStockFilters();
    }

    let searchTimeout;
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyStockFilters();
        }, 500);
    }

    function applyStockFilters() {
        const search = document.getElementById('stock-search').value;
        const outlet = document.getElementById('stock-outlet-filter').value;
        const startDate = document.getElementById('stock-start-date').value;
        const endDate = document.getElementById('stock-end-date').value;

        // Build query params
        const params = new URLSearchParams();
        params.append('active_tab', 'riwayat');
        if (search) params.append('search', search);
        if (outlet && outlet !== 'all') params.append('store_id', outlet);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        const wrapper = document.getElementById('stock-history-wrapper');
        
        // Visual feedback (dimming)
        wrapper.style.opacity = '0.5';
        wrapper.style.pointerEvents = 'none';

        fetch('{{ route("outlet.index") }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            wrapper.innerHTML = html;
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        })
        .catch(err => {
            console.error('Search failed:', err);
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        });
    }

    function resetStockFilters() {
        document.getElementById('stock-search').value = '';
        document.getElementById('stock-outlet-filter').value = 'all';
        document.getElementById('stock-start-date').value = '';
        document.getElementById('stock-end-date').value = '';
        
        // Reset active state in outlet dropdown
        const items = document.querySelectorAll('#view-riwayat .dropdown-content a');
        items.forEach(item => {
            if (item.getAttribute('onclick') && item.getAttribute('onclick').includes('all')) {
                item.classList.add('active-dropdown-item');
            } else {
                item.classList.remove('active-dropdown-item');
            }
        });

        applyStockFilters();
    }

    function exportStock(format) {
        const search = document.getElementById('stock-search').value;
        const outlet = document.getElementById('stock-outlet-filter').value;
        const startDate = document.getElementById('stock-start-date').value;
        const endDate = document.getElementById('stock-end-date').value;

        let url = format === 'excel' ? '{{ route("products.export.excel") }}' : '{{ route("products.export.pdf") }}';
        
        // Build query params
        const params = new URLSearchParams();
        params.append('active_tab', 'riwayat');
        if (search) params.append('search', search);
        if (outlet && outlet !== 'all') params.append('store_id', outlet);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        window.open(url + '?' + params.toString(), '_blank');
    }

    // AJAX Pagination handler
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('#stock-history-wrapper .twins-pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.href;
            const wrapper = document.getElementById('stock-history-wrapper');
            
            wrapper.style.opacity = '0.5';
            wrapper.style.pointerEvents = 'none';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                wrapper.innerHTML = html;
                wrapper.style.opacity = '1';
                wrapper.style.pointerEvents = 'auto';
                // Scroll to top of table
                document.querySelector('.main-content-box').scrollTop = 0;
            })
            .catch(err => {
                console.error('Pagination load failed:', err);
                wrapper.style.opacity = '1';
                wrapper.style.pointerEvents = 'auto';
            });
        }
    });

    // Initial kinerja data load if starting on kinerja tab
    if ('{{ $active_tab }}' === 'kinerja') {
        if (typeof loadKinerjaDashboard === 'function') loadKinerjaDashboard();
    }

    // Initial data outlet load
    document.addEventListener('DOMContentLoaded', () => {
        if ('{{ $active_tab }}' === 'data' || '{{ $active_tab }}' === '') {
            const firstRow = document.querySelector('.outlet-row.active-row') || document.querySelector('.outlet-row');
            if (firstRow) {
                const data = JSON.parse(firstRow.dataset.outlet);
                selectOutlet(firstRow, data);
            }
        }
    });
</script>
@endpush
@endsection

