@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-hadir {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .status-izin {
            background: #FFF3E0;
            color: #E65100;
        }

        .status-alpha {
            background: #FFEBEE;
            color: #C62828;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .rekap-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .rekap-stat {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 16px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .rekap-stat .number {
            font-size: 2rem;
            font-weight: 700;
        }

        .rekap-stat .label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 4px;
        }

        .hari-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .hari-chip {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            background: #E3F2FD;
            color: #1565C0;
        }

        .inline-select {
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
            cursor: pointer;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1.5px solid #ddd;
            font-size: 13px;
            outline: none;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: var(--primary-blue);
        }

        .tab-pill,
        .btn-action,
        .close-modal,
        .btn-filter {
            user-select: none;
        }
    </style>

    <div class="fitur-container" id="absensi-app">
        {{-- PILL TABS --}}
        <div class="tab-navigation overflow-x-auto whitespace-nowrap justify-start pb-2">
            <a href="#" class="tab-pill {{ ($active_tab ?? 'shift') === 'shift' ? 'active' : '' }}" onclick="switchTab('shift')" id="pill-shift">
                <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                <span>Master Shift</span>
            </a>
            <a href="#" class="tab-pill {{ ($active_tab ?? 'shift') === 'jadwal' ? 'active' : '' }}" onclick="switchTab('jadwal')" id="pill-jadwal">
                <iconify-icon icon="solar:calendar-add-bold-duotone"></iconify-icon>
                <span>Jadwal Karyawan</span>
            </a>
            <a href="#" class="tab-pill {{ ($active_tab ?? 'shift') === 'riwayat' ? 'active' : '' }}" onclick="switchTab('riwayat')" id="pill-riwayat">
                <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                <span>Riwayat Absensi</span>
            </a>
            <a href="#" class="tab-pill {{ ($active_tab ?? 'shift') === 'rekap' ? 'active' : '' }}" onclick="switchTab('rekap')" id="pill-rekap">
                <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                <span>Rekap Absensi</span>
            </a>
        </div>

        {{-- ACTION BAR --}}
        <header class="action-bar mb-4 bg-transparent p-0" style="justify-content: space-between; border: none; box-shadow: none; flex-wrap: wrap; gap: 15px;">
            <div id="headerLeftActions" style="display: flex; gap: 12px; align-items: center; flex: 1; min-width: 280px; flex-wrap: wrap;">
                
                {{-- 1. Search Bar --}}
                <div class="search-wrapper" style="min-width: 250px;">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="masukan nama/hari" oninput="debounceSearch()" style="width: 100%;">
                </div>

                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <form id="globalFilterForm" method="GET" action="{{ route('absensi.index') }}" style="display:none;">
                        <input type="hidden" name="active_tab" id="filterActiveTab" value="{{ $active_tab }}">
                        <input type="hidden" name="store_id" id="filterStoreId" value="{{ $store_id }}">
                        <input type="hidden" name="shift_id" id="filterShiftId" value="{{ $shift_id }}">
                    </form>

                    <div id="jadwalFilters" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        {{-- 2. Dropdown Toko --}}
                        <div class="dropdown">
                            <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                                <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                            </button>
                            <div class="dropdown-content">
                                <a href="javascript:void(0)" onclick="applyGlobalFilter('store', 'all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                                @foreach($outlets as $o)
                                    <a href="javascript:void(0)" onclick="applyGlobalFilter('store', '{{ $o->uuid }}')" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                                @endforeach
                            </div>
                        </div>

                        {{-- 3. Dropdown Shift --}}
                        <div class="dropdown">
                            <button type="button" class="btn-filter" title="Filter Shift" onclick="toggleDropdown(event)">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" style="font-size: 24px;" class="{{ $shift_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                            </button>
                            <div class="dropdown-content">
                                <a href="javascript:void(0)" onclick="applyGlobalFilter('shift', 'all')" class="{{ $shift_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Shift</a>
                                @foreach($shifts as $s)
                                    <a href="javascript:void(0)" onclick="applyGlobalFilter('shift', '{{ $s->uuid }}')" class="{{ $shift_id == $s->uuid ? 'active-dropdown-item' : '' }}">{{ $s->nama }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="right-actions" style="display: flex; gap: 12px;">
                {{-- 4. Add Button --}}
                <button type="button" class="btn-action" id="btnAddMain" onclick="openCurrentModal()">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span id="txtAddMain">Tambah</span>
                </button>
            </div>
        </header>

        <form id="formGlobalDelete" method="POST" style="display: none;">@csrf @method('DELETE')</form>

        {{-- RIWAYAT ACTION BAR (Outside Table Box) --}}
        <div id="riwayatActionBar" class="action-bar mb-4" style="display: none; background:transparent; padding:0; border:none; box-shadow:none; flex-wrap: wrap;">
            <div style="display: flex; gap: 12px; align-items: center; flex: 1; flex-wrap: wrap;">
                <div class="search-wrapper" style="min-width: 250px;">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="filter_karyawan_riwayat" class="search-input" value="{{ request('filter_karyawan', $filterKaryawan ?? '') }}" placeholder="Cari nama karyawan..." oninput="debounceSearch()" style="width: 100%;">
                </div>
                
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Bulan" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" class="{{ request('filter_bulan', $filterBulan ?? '') ? 'text-primary-blue' : '' }}"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 220px;">
                        <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Pilih Bulan</label>
                        <input type="month" id="filter_bulan_riwayat" value="{{ request('filter_bulan', $filterBulan ?? '') }}" class="form-control" style="width: 100%; padding: 8px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 13px; outline: none;" onchange="applyPjaxRiwayat()">
                    </div>
                </div>
                
                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id !== 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <input type="hidden" id="store_id_riwayat" value="{{ $store_id }}">
                            @if(Auth::user()->role === 'owner')
                                <a href="javascript:void(0)" onclick="setStoreRiwayat('all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                            @endif
                            @foreach($outlets as $o)
                                <a href="javascript:void(0)" onclick="setStoreRiwayat('{{ $o->uuid }}')" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <input type="hidden" id="store_id_riwayat" value="{{ $store_id }}">
                @endif

                @php
                    $hasRiwayatFilter = request('filter_karyawan') || request('filter_bulan') || ($store_id !== 'all' && Auth::user()->role === 'owner');
                @endphp
                <button id="resetRiwayatBtn" class="btn-action" style="background:#f1f5f9; color:#64748b; display: {{ $hasRiwayatFilter ? 'flex' : 'none' }};" onclick="resetPjaxRiwayat()">
                    <iconify-icon icon="solar:refresh-bold-duotone"></iconify-icon> Reset
                </button>
            </div>
            
            <div class="dropdown" style="margin-left: auto;">
                <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                    <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                    <span>Extract</span>
                </button>
                <div class="dropdown-content" style="right: 0; left: auto;">
                    <a href="javascript:void(0)" onclick="openRiwayatExport('excel')">
                        <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                        Excel
                    </a>
                    <a href="javascript:void(0)" onclick="openRiwayatExport('pdf')">
                        <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                        PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- REKAP ACTION BAR (Outside Table Box) --}}
        <div id="rekapActionBar" class="action-bar mb-4" style="display: none; background:transparent; padding:0; border:none; box-shadow:none; flex-wrap: wrap;">
            <div style="display: flex; gap: 12px; align-items: center; flex: 1; flex-wrap: wrap;">
                <div class="search-wrapper" style="min-width: 250px;">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="filter_karyawan_rekap" class="search-input" value="{{ request('filter_karyawan', $filterKaryawan ?? '') }}" placeholder="Cari nama karyawan..." oninput="debounceSearch()" style="width: 100%;">
                </div>
                
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Bulan" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" class="{{ request('rekap_bulan', $rekapBulan ?? '') ? 'text-primary-blue' : '' }}"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 220px;">
                        <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Pilih Periode</label>
                        <input type="month" id="filter_bulan_rekap" value="{{ request('rekap_bulan', $rekapBulan ?? '') }}" class="form-control" style="width: 100%; padding: 8px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 13px; outline: none;" onchange="applyPjaxRekap()">
                    </div>
                </div>
                
                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id !== 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <input type="hidden" id="store_id_rekap" value="{{ $store_id }}">
                            @if(Auth::user()->role === 'owner')
                                <a href="javascript:void(0)" onclick="setStoreRekap('all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                            @endif
                            @foreach($outlets as $o)
                                <a href="javascript:void(0)" onclick="setStoreRekap('{{ $o->uuid }}')" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <input type="hidden" id="store_id_rekap" value="{{ $store_id }}">
                @endif

                @php
                    $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                    $isRekapBulanFiltered = request('rekap_bulan') && request('rekap_bulan') !== $currentMonth;
                    $hasRekapFilter = request('filter_karyawan') || $isRekapBulanFiltered || ($store_id !== 'all' && Auth::user()->role === 'owner');
                @endphp
                <button id="resetRekapBtn" class="btn-action" style="background:#f1f5f9; color:#64748b; display: {{ $hasRekapFilter ? 'flex' : 'none' }};" onclick="resetPjaxRekap()">
                    <iconify-icon icon="solar:refresh-bold-duotone"></iconify-icon> Reset
                </button>
            </div>
        </div>

        {{-- MAIN BOX --}}
        <div class="main-content-box mobile-pb">
            @include('absensi._tab_shift')
            @include('absensi._tab_jadwal')
            @include('absensi._tab_riwayat')
            @include('absensi._tab_rekap')
        </div>
    </div>

    {{-- MODALS --}}
    @include('absensi._modal_shift')
    @include('absensi._modal_jadwal')

    <script>
        async function applyGlobalFilter(type, value) {
            document.getElementById('filterActiveTab').value = currentTab;
            if (type === 'store') document.getElementById('filterStoreId').value = value;
            if (type === 'shift') document.getElementById('filterShiftId').value = value;
            
            // Close dropdowns
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            
            // Update active dropdown UI immediately
            if (type === 'store') {
                document.querySelectorAll('a[onclick^="applyGlobalFilter(\'store\'"]').forEach(a => a.classList.remove('active-dropdown-item'));
                const active = document.querySelector(`a[onclick="applyGlobalFilter('store', '${value}')"]`);
                if(active) active.classList.add('active-dropdown-item');
                
                const icon = document.querySelector('button[title="Filter Toko"] iconify-icon');
                if(icon) {
                    if (value !== 'all') icon.classList.add('text-primary-blue');
                    else icon.classList.remove('text-primary-blue');
                }
            }
            if (type === 'shift') {
                document.querySelectorAll('a[onclick^="applyGlobalFilter(\'shift\'"]').forEach(a => a.classList.remove('active-dropdown-item'));
                const active = document.querySelector(`a[onclick="applyGlobalFilter('shift', '${value}')"]`);
                if(active) active.classList.add('active-dropdown-item');
                
                const icon = document.querySelector('button[title="Filter Shift"] iconify-icon');
                if(icon) {
                    if (value !== 'all') icon.classList.add('text-primary-blue');
                    else icon.classList.remove('text-primary-blue');
                }
            }

            // AJAX PJAX fetch
            const form = document.getElementById('globalFilterForm');
            const url = new URL(form.action);
            new FormData(form).forEach((v, k) => url.searchParams.append(k, v));

            try {
                const res = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await res.text();
                
                // Parse HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Replace tabs content
                ['shift', 'jadwal', 'riwayat', 'rekap'].forEach(t => {
                    const view = document.getElementById('view-' + t);
                    const newView = doc.getElementById('view-' + t);
                    if (view && newView) {
                        view.innerHTML = newView.innerHTML;
                    }
                });

                // Update URL without reload
                window.history.pushState({}, '', url.toString());
            } catch(e) {
                console.error('Filter AJAX error, falling back to reload', e);
                form.submit();
            }
        }

        function toggleDropdown(event) {
            event.stopPropagation();
            const dd = event.currentTarget.nextElementSibling;
            document.querySelectorAll('.dropdown-content').forEach(el => {
                if (el !== dd) el.classList.remove('show');
            });
            dd.classList.toggle('show');
        }

        window.addEventListener('click', e => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            }
        });

        let currentTab = '{{ $active_tab ?? "shift" }}';

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
            document.getElementById('pill-' + tab).classList.add('active');

            ['shift', 'jadwal', 'riwayat', 'rekap'].forEach(t => {
                const v = document.getElementById('view-' + t);
                if (v) v.style.display = (t === tab) ? 'block' : 'none';
            });

            // Handle Header Actions Visibility
            const leftActions = document.getElementById('headerLeftActions');
            const jadwalFilters = document.getElementById('jadwalFilters');
            const searchInput = document.getElementById('globalSearch');
            const btnAdd = document.getElementById('btnAddMain');
            const txtAdd = document.getElementById('txtAddMain');
            const actionBar = document.querySelector('.action-bar:not(#riwayatActionBar):not(#rekapActionBar)');
            const riwayatActionBar = document.getElementById('riwayatActionBar');
            const rekapActionBar = document.getElementById('rekapActionBar');

            const showSearch = (tab === 'shift' || tab === 'jadwal');
            const showJadwalFilters = (tab === 'jadwal');
            const showAddButton = (tab === 'shift' || tab === 'jadwal');

            if (leftActions) leftActions.style.display = showSearch ? 'flex' : 'none';
            if (jadwalFilters) jadwalFilters.style.display = showJadwalFilters ? 'flex' : 'none';
            if (riwayatActionBar) riwayatActionBar.style.display = (tab === 'riwayat') ? 'flex' : 'none';
            if (rekapActionBar) rekapActionBar.style.display = (tab === 'rekap') ? 'flex' : 'none';
            
            if (searchInput) {
                if (tab === 'shift') {
                    searchInput.placeholder = 'Cari nama shift...';
                } else {
                    searchInput.placeholder = 'Cari nama karyawan/hari...';
                }
            }

            if (btnAdd) {
                btnAdd.style.display = showAddButton ? 'flex' : 'none';
                if (tab === 'shift') txtAdd.innerText = 'Tambah Shift';
                if (tab === 'jadwal') txtAdd.innerText = 'Tambah Jadwal';
            }

            // Hide main action bar entirely if nothing to show
            if (actionBar) {
                actionBar.style.display = (showSearch || showAddButton) ? 'flex' : 'none';
            }

            if (tab === 'shift' || tab === 'jadwal') filterTable();
        }

        function openCurrentModal() {
            if (currentTab === 'shift') openModal('modalAddShift');
            else if (currentTab === 'jadwal') openModal('modalAddJadwal');
        }

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        // --- CLIENT-SIDE PAGINATION & SEARCH LOGIC ---
        const absensiState = {
            shift: { currentPage: 1, itemsPerPage: 10, filteredItems: [], allRows: [] },
            jadwal: { currentPage: 1, itemsPerPage: 10, filteredItems: [], allRows: [] },
            riwayat: { currentPage: 1, itemsPerPage: 10, filteredItems: [], allRows: [] },
            rekap: { currentPage: 1, itemsPerPage: 10, filteredItems: [], allRows: [] }
        };
        
        let searchTimeout = null;
        function debounceSearch() {
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                absensiState[currentTab].currentPage = 1;
                applyAbsensiFilters();
            }, 300);
        }
        
        function initAbsensiRows(tab) {
            const section = document.getElementById('view-' + tab);
            if (!section) return;
            const rows = Array.from(section.querySelectorAll('tbody tr.searchable-row'));
            absensiState[tab].allRows = rows;
            absensiState[tab].currentPage = 1;
        }

        function applyAbsensiFilters() {
            const tabs = ['shift', 'jadwal', 'riwayat', 'rekap'];
            
            tabs.forEach(tab => {
                const section = document.getElementById('view-' + tab);
                if (!section) return;
                
                if (absensiState[tab].allRows.length === 0 && section.querySelectorAll('tbody tr.searchable-row').length > 0) {
                    initAbsensiRows(tab);
                }
                
                let query = '';
                if (tab === 'shift' || tab === 'jadwal') {
                    query = (document.getElementById('globalSearch')?.value || '').toLowerCase().trim();
                } else if (tab === 'riwayat') {
                    query = (document.getElementById('filter_karyawan_riwayat')?.value || '').toLowerCase().trim();
                } else if (tab === 'rekap') {
                    query = (document.getElementById('filter_karyawan_rekap')?.value || '').toLowerCase().trim();
                }
                
                const state = absensiState[tab];
                state.filteredItems = [];
                
                state.allRows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (query === '' || text.includes(query)) {
                        state.filteredItems.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const tbody = section.querySelector('tbody');
                if (tbody) {
                    let emptyRow = tbody.querySelector('.js-empty-row');
                    if (!emptyRow) {
                        const colCount = section.querySelectorAll('thead th').length || 1;
                        emptyRow = document.createElement('tr');
                        emptyRow.className = 'js-empty-row';
                        emptyRow.innerHTML = `<td colspan="${colCount}" class="empty-state text-center py-4" style="color: #64748b;">Data tidak ditemukan.</td>`;
                        tbody.appendChild(emptyRow);
                    }
                    
                    if (state.allRows.length > 0) {
                        emptyRow.style.display = state.filteredItems.length === 0 ? '' : 'none';
                        const serverEmpty = tbody.querySelector('.empty-state:not(.js-empty-row > td)');
                        if (serverEmpty) {
                            const tr = serverEmpty.closest('tr');
                            if (tr && tr !== emptyRow) tr.style.display = 'none';
                        }
                    } else {
                        emptyRow.style.display = 'none';
                    }
                }
                
                const container = document.getElementById(tab + '-pagination');
                if (!container) {
                    state.filteredItems.forEach(row => row.style.display = '');
                    return;
                }
                
                const totalItems = state.filteredItems.length;
                const totalPages = Math.ceil(totalItems / state.itemsPerPage) || 1;
                if (state.currentPage > totalPages) state.currentPage = totalPages;
                
                const startIndex = (state.currentPage - 1) * state.itemsPerPage;
                const endIndex = startIndex + state.itemsPerPage;
                
                state.filteredItems.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                renderAbsensiPagination(tab, totalPages);
            });
        }

        function renderAbsensiPagination(tab, totalPages) {
            const container = document.getElementById(tab + '-pagination');
            if (!container) return;
            const state = absensiState[tab];
            let html = '';
            if (totalPages > 1) {
                html += `<button type="button" class="k-page-btn" ${state.currentPage === 1 ? 'disabled' : ''} onclick="changeAbsensiPage('${tab}', ${state.currentPage - 1})"><iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon></button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= state.currentPage - 1 && i <= state.currentPage + 1)) {
                        html += `<button type="button" class="k-page-btn ${i === state.currentPage ? 'active' : ''}" onclick="changeAbsensiPage('${tab}', ${i})">${i}</button>`;
                    } else if (i === state.currentPage - 2 || i === state.currentPage + 2) {
                        html += `<span class="k-page-dots">...</span>`;
                    }
                }
                html += `<button type="button" class="k-page-btn" ${state.currentPage === totalPages ? 'disabled' : ''} onclick="changeAbsensiPage('${tab}', ${state.currentPage + 1})"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>`;
            }
            container.innerHTML = html;
        }

        function changeAbsensiPage(tab, page) {
            absensiState[tab].currentPage = page;
            applyAbsensiFilters();
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            applyAbsensiFilters();
        });

        let riwayatTimeout = null;
        function debouncePjaxRiwayat() {
            clearTimeout(riwayatTimeout);
            riwayatTimeout = setTimeout(() => applyPjaxRiwayat(), 500);
        }

        async function applyPjaxRiwayat(reset = false) {
            const searchInput = document.getElementById('filter_karyawan_riwayat');
            const bulanInput = document.getElementById('filter_bulan_riwayat');
            const storeInput = document.getElementById('store_id_riwayat');
            const resetBtn = document.getElementById('resetRiwayatBtn');

            if (reset) {
                if(searchInput) searchInput.value = '';
                if(bulanInput) bulanInput.value = '';
                if(storeInput) storeInput.value = 'all'; 
            }

            const search = searchInput ? searchInput.value : '';
            const bulan = bulanInput ? bulanInput.value : '';
            const store = storeInput ? storeInput.value : '{{ $store_id }}';
            
            // Update UI for icon buttons
            const bulanIcon = document.querySelector('#riwayatActionBar button[title="Filter Bulan"] iconify-icon');
            if (bulanIcon) bulanIcon.className = bulan ? 'text-primary-blue' : '';
            
            const tokoIcon = document.querySelector('#riwayatActionBar button[title="Filter Toko"] iconify-icon');
            if (tokoIcon) tokoIcon.className = (store !== 'all') ? 'text-primary-blue' : '';

            // Update dropdown active state
            document.querySelectorAll('#riwayatActionBar .dropdown-content a').forEach(a => {
                if(a.getAttribute('onclick')?.includes('setStoreRiwayat')) a.classList.remove('active-dropdown-item');
            });
            const activeStore = document.querySelector(`#riwayatActionBar .dropdown-content a[onclick="setStoreRiwayat('${store}')"]`);
            if (activeStore) activeStore.classList.add('active-dropdown-item');
            
            // Close dropdowns
            document.querySelectorAll('#riwayatActionBar .dropdown-content').forEach(d => d.classList.remove('show'));

            if (resetBtn) {
                resetBtn.style.display = (search || bulan || (store !== 'all')) ? 'flex' : 'none';
            }

            const url = new URL('{{ route('absensi.index') }}');
            url.searchParams.set('active_tab', 'riwayat');
            url.searchParams.set('filter_karyawan', search);
            url.searchParams.set('filter_bulan', bulan);
            url.searchParams.set('store_id', store);

            try {
                const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const view = document.getElementById('view-riwayat');
                const newView = doc.getElementById('view-riwayat');
                if (view && newView) {
                    view.innerHTML = newView.innerHTML;
                    initAbsensiRows('riwayat');
                    applyAbsensiFilters();
                }
                
                window.history.pushState({}, '', url.toString());
            } catch(e) {
                window.location.href = url.toString();
            }
        }

        function resetPjaxRiwayat() { applyPjaxRiwayat(true); }
        
        function setStoreRiwayat(val) {
            const input = document.getElementById('store_id_riwayat');
            if (input) {
                input.value = val;
                applyPjaxRiwayat();
            }
        }

        let rekapTimeout = null;
        function debouncePjaxRekap() {
            clearTimeout(rekapTimeout);
            rekapTimeout = setTimeout(() => applyPjaxRekap(), 500);
        }

        async function applyPjaxRekap(reset = false) {
            const searchInput = document.getElementById('filter_karyawan_rekap');
            const bulanInput = document.getElementById('filter_bulan_rekap');
            const storeInput = document.getElementById('store_id_rekap');
            const resetBtn = document.getElementById('resetRekapBtn');

            const currentMonth = new Date().toLocaleString("sv-SE", { timeZone: "Asia/Jakarta" }).slice(0, 7); // YYYY-MM
            if (reset) {
                if(searchInput) searchInput.value = '';
                if(bulanInput) bulanInput.value = currentMonth;
                if(storeInput) storeInput.value = 'all'; 
            }

            const search = searchInput ? searchInput.value : '';
            const bulan = bulanInput ? bulanInput.value : currentMonth;
            const store = storeInput ? storeInput.value : '{{ $store_id }}';
            
            // Update UI for icon buttons
            const isBulanFiltered = bulan !== currentMonth;
            const bulanIcon = document.querySelector('#rekapActionBar button[title="Filter Bulan"] iconify-icon');
            if (bulanIcon) bulanIcon.className = isBulanFiltered ? 'text-primary-blue' : '';
            
            const tokoIcon = document.querySelector('#rekapActionBar button[title="Filter Toko"] iconify-icon');
            if (tokoIcon) tokoIcon.className = (store !== 'all') ? 'text-primary-blue' : '';
            
            // Update dropdown active state
            document.querySelectorAll('#rekapActionBar .dropdown-content a').forEach(a => {
                if(a.getAttribute('onclick')?.includes('setStoreRekap')) a.classList.remove('active-dropdown-item');
            });
            const activeStore = document.querySelector(`#rekapActionBar .dropdown-content a[onclick="setStoreRekap('${store}')"]`);
            if (activeStore) activeStore.classList.add('active-dropdown-item');
            
            // Close dropdowns
            document.querySelectorAll('#rekapActionBar .dropdown-content').forEach(d => d.classList.remove('show'));

            if (resetBtn) {
                resetBtn.style.display = (search || isBulanFiltered || (store !== 'all')) ? 'flex' : 'none';
            }

            const url = new URL('{{ route('absensi.index') }}');
            url.searchParams.set('active_tab', 'rekap');
            url.searchParams.set('filter_karyawan', search);
            url.searchParams.set('rekap_bulan', bulan);
            url.searchParams.set('store_id', store);

            try {
                const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const view = document.getElementById('view-rekap');
                const newView = doc.getElementById('view-rekap');
                if (view && newView) {
                    view.innerHTML = newView.innerHTML;
                    initAbsensiRows('rekap');
                    applyAbsensiFilters();
                }
                
                window.history.pushState({}, '', url.toString());
            } catch(e) {
                window.location.href = url.toString();
            }
        }

        function resetPjaxRekap() { applyPjaxRekap(true); }
        
        function setStoreRekap(val) {
            const input = document.getElementById('store_id_rekap');
            if (input) {
                input.value = val;
                applyPjaxRekap();
            }
        }        function globalDelete(url, label) {
            Swal.fire({
                title: `Hapus ${label}?`, text: 'Data ini akan dihapus permanen!', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
            }).then(r => {
                if (r.isConfirmed) {
                    let f = document.getElementById('formGlobalDelete');
                    f.action = url; f.submit();
                }
            });
        }

        function updateAbsensiStatus(uuid) {
            const sel = document.getElementById('status-' + uuid);
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/absensi/riwayat/' + uuid + '/status';
            form.innerHTML = '@csrf @method("PUT") <input type="hidden" name="status_kehadiran" value="' + sel.value + '">';
            document.body.appendChild(form);
            form.submit();
        }

        function openRiwayatExport(format) {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route("absensi.riwayat.export") }}';
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            form.appendChild(formatInput);

            const store = document.querySelector('select[name="store_id"]')?.value || '{{ $store_id }}';
            const storeInput = document.createElement('input');
            storeInput.type = 'hidden';
            storeInput.name = 'store_id';
            storeInput.value = store;
            form.appendChild(storeInput);

            const bulan = document.querySelector('input[name="filter_bulan"]')?.value || '';
            const bulanInput = document.createElement('input');
            bulanInput.type = 'hidden';
            bulanInput.name = 'filter_bulan';
            bulanInput.value = bulan;
            form.appendChild(bulanInput);

            const karyawan = document.querySelector('input[name="filter_karyawan"]')?.value || '';
            const karyawanInput = document.createElement('input');
            karyawanInput.type = 'hidden';
            karyawanInput.name = 'filter_karyawan';
            karyawanInput.value = karyawan;
            form.appendChild(karyawanInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        switchTab(currentTab);

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}', showConfirmButton: false, timer: 2000 });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session("error") }}' });
        @endif
        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan!', html: '<ul style="text-align:left">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>' });
        @endif
    </script>
@endsection