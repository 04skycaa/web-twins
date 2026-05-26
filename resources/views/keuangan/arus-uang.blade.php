@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="fitur-container" id="arus-uang-container">
    @include('keuangan.partials.tabs', ['active' => 'arus-uang'])
    
    <div class="action-bar-container">
        <form action="{{ route('keuangan.arus-uang') }}" method="GET" id="filterForm" class="action-bar">
            <div class="left-actions-group">
                {{-- Search --}}
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="searchInput" class="search-input" value="{{ request('search') }}" placeholder="Cari keterangan, jenis, atau outlet..." onkeyup="realtimeSearch()" onkeydown="if(event.key==='Enter') event.preventDefault();">
                </div>

                {{-- Date Filter --}}
                <div class="dropdown">
                    <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 300px;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Dari</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Sampai</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <button type="submit" class="btn-action" style="width: 100%; justify-content: center;">Terapkan</button>
                        </div>
                    </div>
                </div>

                {{-- Outlet Filter --}}
                @if(auth()->user()->role === 'owner')
                <div class="dropdown">
                    <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content">
                        <a href="{{ route('keuangan.arus-uang', array_merge(request()->except('store_id'))) }}">Semua Outlet</a>
                        @foreach($outlets as $outlet)
                            <a href="{{ route('keuangan.arus-uang', array_merge(request()->all(), ['store_id' => $outlet->uuid])) }}">
                                {{ $outlet->nama }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" name="store_id" value="{{ request('store_id') }}">
                @endif
            </div>
            
            <div class="right-actions">
                <button type="button" class="btn-action" onclick="exportToExcel()">
                    <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon>
                    <span>Export Excel</span>
                </button>
            </div>
        </form>
    </div>

    <div class="main-content-box" style="background: transparent; padding: 20px; box-shadow: none;">
        {{-- Summary Cards --}}
        <div class="grid-dashboard">
            <div class="finance-card animate-up delay-1" style="min-width: 250px; flex-shrink: 0; scroll-snap-align: center;">
                <div class="icon-box bg-bersih">
                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <div class="card-label">Saldo Bersih</div>
                    <div class="card-value">Rp {{ number_format($saldo_bersih, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="finance-card animate-up delay-2" style="min-width: 250px; flex-shrink: 0; scroll-snap-align: center;">
                <div class="icon-box bg-masuk">
                    <iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <div class="card-label">Saldo Masuk</div>
                    <div class="card-value text-masuk">Rp {{ number_format($pemasukan, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="finance-card animate-up delay-3" style="min-width: 250px; flex-shrink: 0; scroll-snap-align: center;">
                <div class="icon-box bg-keluar">
                    <iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <div class="card-label">Saldo Keluar</div>
                    <div class="card-value text-keluar">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- History Section --}}
        <div class="table-container" style="background: white; padding: 24px; border-radius: 24px; border: 1px solid #f1f5f9;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Histori Transaksi</h3>
                
                <div class="filter-pills">
                    <button type="button" class="filter-pill {{ !request('type') || request('type') == 'semua' ? 'active' : '' }}" onclick="location.href='{{ route('keuangan.arus-uang', array_merge(request()->except('type'), ['type' => 'semua'])) }}'">Semua</button>
                    <button type="button" class="filter-pill {{ request('type') == 'pemasukan' ? 'active' : '' }}" onclick="location.href='{{ route('keuangan.arus-uang', array_merge(request()->except('type'), ['type' => 'pemasukan'])) }}'">Masuk</button>
                    <button type="button" class="filter-pill {{ request('type') == 'pengeluaran' ? 'active' : '' }}" onclick="location.href='{{ route('keuangan.arus-uang', array_merge(request()->except('type'), ['type' => 'pengeluaran'])) }}'">Keluar</button>
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
                        <tr class="history-row">
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }} <br> <span style="font-size: 11px; color: #94a3b8;">{{ \Carbon\Carbon::parse($item->tanggal)->format('H:i') }}</span></td>
                            <td>
                                <div style="font-weight: 600; color: #334155;">{{ $item->keterangan }}</div>
                                <div style="font-size: 11px; color: #64748b;">Oleh: {{ $item->user->name ?? $item->user->username ?? '-' }}</div>
                            </td>
                            <td>{{ $item->outlet->nama ?? '-' }}</td>
                            <td style="white-space: nowrap; text-align: center;">
                                <span class="status-badge {{ $item->jenis == 'pemasukan' ? 'badge-masuk' : 'badge-keluar' }}" style="white-space: nowrap;">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; white-space: nowrap; color: {{ $item->jenis == 'pemasukan' ? '#16a34a' : '#dc2626' }};">
                                {{ $item->jenis == 'pemasukan' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <iconify-icon icon="solar:document-text-broken" style="font-size: 48px; margin-bottom: 12px; display: block; margin-inline: auto;"></iconify-icon>
                                Belum ada riwayat transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="twins-pagination-container" id="historyPagination" style="margin-top: 24px;"></div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>
<script>
    function exportToExcel() {
        const table = document.getElementById('arusUangTable');
        if (!table) return;
        
        if (typeof XLSX === 'undefined') {
            alert('Library Excel belum dimuat. Silakan muat ulang halaman.');
            return;
        }

        const rows = table.querySelectorAll('tbody tr');
        let hiddenRows = [];
        rows.forEach(row => {
            if (row.style.display === 'none' && historyState.filtered.includes(row)) {
                row.style.display = '';
                hiddenRows.push(row);
            }
        });
        
        const wb = XLSX.utils.table_to_book(table, {sheet: "Arus Uang"});
        XLSX.writeFile(wb, `Laporan_Arus_Uang_{{ date('d_M_Y') }}.xlsx`);

        hiddenRows.forEach(row => row.style.display = 'none');
    }

    let historyState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let currentTypeFilter = new URL(window.location.href).searchParams.get('type') || 'semua';

    function renderPagination() {
        const totalRows = historyState.filtered.length;
        const totalPages = Math.ceil(totalRows / historyState.rowsPerPage) || 1;
        
        if (historyState.currentPage > totalPages) historyState.currentPage = totalPages;
        if (historyState.currentPage < 1) historyState.currentPage = 1;

        const startIndex = (historyState.currentPage - 1) * historyState.rowsPerPage;
        const endIndex = startIndex + historyState.rowsPerPage;

        historyState.filtered.forEach((row, index) => {
            row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
        });

        const container = document.getElementById('historyPagination');
        if (!container) return;

        let html = '<ul class="twins-pagination">';
        html += `<li class="twins-page-item ${historyState.currentPage === 1 ? 'disabled' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePage(${historyState.currentPage - 1})"><iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon></a></li>`;

        let startPage = Math.max(1, historyState.currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);
        
        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="twins-page-item ${i === historyState.currentPage ? 'active' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePage(${i})">${i}</a></li>`;
        }

        html += `<li class="twins-page-item ${historyState.currentPage === totalPages ? 'disabled' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePage(${historyState.currentPage + 1})"><iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon></a></li>`;
        html += '</ul>';

        container.innerHTML = html;
        container.style.display = totalPages > 1 ? 'flex' : 'none';
    }

    function changePage(newPage) {
        historyState.currentPage = newPage;
        renderPagination();
    }

    function applyHistoryFilters() {
        const searchEl = document.getElementById('searchInput');
        const input = searchEl ? searchEl.value.toLowerCase() : '';
        const rows = Array.from(document.querySelectorAll('.history-row'));
        
        let matched = [];
        
        rows.forEach(row => {
            const jenis = row.getAttribute('data-jenis');
            const text = row.textContent.toLowerCase();
            
            const matchesSearch = text.includes(input);
            const matchesType = (currentTypeFilter === 'semua') || (jenis === currentTypeFilter);
            
            if (matchesSearch && matchesType) {
                matched.push(row);
            } else {
                row.style.display = 'none';
            }
        });
        
        historyState.filtered = matched;
        historyState.currentPage = 1;
        renderPagination();
    }

    function realtimeSearch() {
        applyHistoryFilters();
    }

    document.addEventListener("DOMContentLoaded", function() {
        applyHistoryFilters();
    });

    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        const allDropdowns = document.querySelectorAll('.dropdown-content');
        
        allDropdowns.forEach(d => {
            if (d !== dropdown) d.classList.remove('show');
        });
        
        dropdown.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.btn-filter') && !event.target.closest('.dropdown-content')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    }
</script>
@endsection
