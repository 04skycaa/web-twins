@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
@endpush

@section('content')
@php $active_tab = request('tab', 'cashbox'); @endphp
<div class="fitur-container">
    @include('keuangan.partials.tabs')

    {{-- SECTION CASHBOX --}}
    <div id="view-cashbox" class="view-section {{ $active_tab === 'cashbox' ? 'active' : '' }}">
        <div class="action-bar mobile-action-bar" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="search-wrapper mobile-search-shrink" style="max-width: 400px; width: 100%; flex: 1;">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" id="cashboxSearch" class="search-input" placeholder="Cari nama cashbox..." onkeyup="filterCashbox()">
            </div>
            <div class="right-actions">
                <button onclick="openModal('modalAddCashbox')" class="btn-action">
                    <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    <span>Tambah Cashbox</span>
                </button>
            </div>
        </div>

        @include('keuangan.partials.table_cashbox')
    </div>

    {{-- SECTION ARUS UANG --}}
    <div id="view-arus-uang" class="view-section {{ $active_tab === 'arus-uang' ? 'active' : '' }}">
        <div class="action-bar flex-wrap mobile-action-bar" style="margin-bottom: 20px;">
            <div class="left-actions-group flex-wrap">
                <form action="{{ route('keuangan.index') }}" method="GET" id="filterForm" style="display: flex; gap: 8px; flex: 1;" class="mobile-action-bar" onsubmit="submitArusUangFilter(event)">
                    <input type="hidden" name="tab" value="arus-uang">
                    <div class="search-wrapper mobile-search-shrink">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                        <input type="text" id="searchInput" class="search-input" value="{{ request('search') }}" placeholder="Cari keterangan, jenis, atau outlet..." onkeyup="realtimeSearch()" onkeydown="if(event.key==='Enter') event.preventDefault();">
                    </div>

                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content" style="padding: 15px; width: 300px;">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div>
                                    <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Dari</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                                </div>
                                <div>
                                    <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Sampai</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="resetArusUangDateFilter()">Reset</button>
                                    <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'owner')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Outlet">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 20px;" class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="javascript:void(0)" onclick="setStore('all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                            @foreach($outlets as $outlet)
                                <a href="javascript:void(0)" onclick="setStore('{{ $outlet->uuid }}')" class="{{ $store_id == $outlet->uuid ? 'active-dropdown-item' : '' }}">{{ $outlet->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="store_id" id="filter_store_id" value="{{ $store_id }}">
                    @endif
                </form>
            </div>
            
            <div class="right-actions">

                <div class="dropdown">
                    <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content" style="right: 0; left: auto;">
                        <a href="javascript:void(0)" onclick="exportToExcel()"><iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px; font-size: 16px;"></iconify-icon> Excel</a>
                        <a href="javascript:void(0)" onclick="exportToPDF()"><iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px; font-size: 16px;"></iconify-icon> PDF</a>
                    </div>
                </div>
            </div>
        </div>

        @include('keuangan.partials.table_arus_uang')
    </div>
</div>

{{-- MODALS --}}
<div id="modalAddCashbox" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Cashbox Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalAddCashbox')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashbox.store') }}" method="POST">
            @csrf
            <div class="modal-body-scroll">
                <div class="form-group">
                    <label>Nama Cashbox / Metode Pembayaran</label>
                    <input type="text" name="nama_metode" class="form-control" placeholder="Contoh: Cash, QRIS, dll" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalAddCashbox')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditCashbox" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Edit Cashbox</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditCashbox')">&times;</button>
        </div>
        <form id="formEditCashbox" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body-scroll">
                <div class="form-group">
                    <label>Nama Cashbox / Metode Pembayaran</label>
                    <input type="text" name="nama_metode" id="edit_nama_metode" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEditCashbox')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<form id="formDeleteCashbox" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div id="modalTransferSaldo" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Pemindahan Saldo Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalTransferSaldo')">&times;</button>
        </div>
        <form id="formTransferSaldo" onsubmit="submitTransferSaldo(event)">
            @csrf
            <div class="modal-body-scroll">
                @if(auth()->user()->role === 'owner')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Pilih Outlet *</label>
                    <select name="store_id" class="form-control" required>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->uuid }}" {{ $store_id == $outlet->uuid ? 'selected' : '' }}>{{ $outlet->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                @endif

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Dari Akun (Asal) *</label>
                    <select name="from_cashbox_id" class="form-control" required>
                        <option value="">-- Pilih Akun Asal --</option>
                        @foreach($cashboxes as $cb)
                            <option value="{{ $cb->uuid }}">
                                {{ $cb->nama_metode }} (Saldo: Rp {{ number_format($cb->saldo, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Ke Akun (Tujuan) *</label>
                    <select name="to_cashbox_id" class="form-control" required>
                        <option value="">-- Pilih Akun Tujuan --</option>
                        @foreach($cashboxes as $cb)
                            <option value="{{ $cb->uuid }}">{{ $cb->nama_metode }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Nominal Transfer *</label>
                    <div class="nominal-wrapper">
                        <input type="number" name="nominal" class="form-control" placeholder="0" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Tanggal *</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" placeholder="Contoh: Setor Tunai, dll" style="min-height: 80px;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalTransferSaldo')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button>
                <button type="submit" id="btnSubmitTransfer" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Proses Pemindahan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer crossorigin="anonymous"></script>
<script>
    let currentTab = '{{ request('tab', 'cashbox') }}';

    // Tab-pill & sections sudah dirender di atas script ini — langsung init
    switchTab(currentTab, true);

    function switchTab(tabId, isInitial = false) {
        currentTab = tabId;
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.classList.add('active');

        // Sync semua hidden input[name=tab] agar filter form tidak reset tab
        document.querySelectorAll('input[name="tab"]').forEach(input => input.value = tabId);

        // Update URL without reload
        if (!isInitial) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openEditCashbox(uuid, nama) {
        const form = document.getElementById('formEditCashbox');
        form.action = `/keuangan/cashbox/${uuid}`;
        document.getElementById('edit_nama_metode').value = nama;
        openModal('modalEditCashbox');
    }

    function deleteCashbox(uuid, nama) {
        Swal.fire({
            title: 'Hapus Cashbox?',
            text: `Hapus "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formDeleteCashbox');
                form.action = `/keuangan/cashbox/${uuid}`;
                form.submit();
            }
        });
    }

    function filterCashbox() {
        const searchText = document.getElementById('cashboxSearch').value.toLowerCase();
        document.querySelectorAll('#tbody-cashbox tr').forEach(row => {
            if (row.cells.length < 2) return;
            row.style.display = row.cells[0].innerText.toLowerCase().includes(searchText) ? '' : 'none';
        });
    }

    function setStore(id) {
        document.getElementById('filter_store_id').value = id;
        
        // Update visually instantly
        document.querySelectorAll('#filterForm .dropdown-content a').forEach(a => a.classList.remove('active-dropdown-item'));
        const activeLink = document.querySelector(`#filterForm .dropdown-content a[onclick="setStore('${id}')"]`);
        if (activeLink) activeLink.classList.add('active-dropdown-item');
        
        const icon = document.querySelector('#filterForm .btn-filter iconify-icon[icon="solar:shop-bold-duotone"]');
        if (icon) {
            if (id !== 'all') icon.classList.add('text-primary-blue');
            else icon.classList.remove('text-primary-blue');
        }

        submitArusUangFilter();
    }

    function resetArusUangDateFilter() {
        const form = document.getElementById('filterForm');
        if(form) {
            form.querySelector('input[name="start_date"]').value = '';
            form.querySelector('input[name="end_date"]').value = '';
            submitArusUangFilter();
        }
    }

    async function submitArusUangFilter(event) {
        if (event) event.preventDefault();
        
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) params.set(key, value);
        }
        
        // Search input is not part of formData
        const searchVal = document.getElementById('searchInput').value;
        if (searchVal) params.set('search', searchVal);

        const url = `${form.action}?${params.toString()}`;
        
        // Update URL
        window.history.pushState({ tab: 'arus-uang' }, '', url);
        
        // Show loading state
        const targetContainer = document.getElementById('view-arus-uang').querySelector('.main-content-box');
        
        // Close dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            
            if (targetContainer) {
                targetContainer.innerHTML = html;
                
                // Re-apply history filters for the JS-based search/pemasukan/pengeluaran sub-filters
                if (typeof applyHistoryFilters === 'function') {
                    applyHistoryFilters();
                }
            }
        } catch (error) {
            console.error('AJAX Fetch Error:', error);
            window.location.href = url; // Fallback
        }
    }

    let historyState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let currentTypeFilter = new URL(window.location.href).searchParams.get('type') || 'semua';

    function filterHistoryType(type, btn) {
        document.querySelectorAll('.filter-pills .filter-pill').forEach(pill => pill.classList.remove('active'));
        if(btn) btn.classList.add('active');
        currentTypeFilter = type;
        applyHistoryFilters();
    }

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
        document.querySelectorAll('.dropdown-content').forEach(d => { if (d !== dropdown) d.classList.remove('show'); });
        dropdown.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.btn-filter') && !event.target.closest('.dropdown-content')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    }

    function exportToExcel() {
        const activeSection = document.querySelector('.view-section.active');
        if (!activeSection) return;
        const table = activeSection.querySelector('table');
        if (!table) return;
        
        let title = 'Data Keuangan';
        const headerText = activeSection.querySelector('h3, h4');
        if (headerText) {
            title = headerText.innerText;
        } else {
            const id = activeSection.getAttribute('id');
            if (id) {
                title = id.replace('view-', '').replace('-', ' ').replace(/\b\w/g, c => c.toUpperCase());
            }
        }
        
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Library Excel belum dimuat. Silakan muat ulang halaman.', 'error');
            return;
        }

        // Clone table to only include filtered rows
        const clone = table.cloneNode(true);
        const tbody = clone.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (activeSection.id === 'view-arus-uang') {
            const totalBersihStr = '{{ number_format($saldo_bersih, 0, ",", ".") }}';
            const totalPemasukanStr = '{{ number_format($pemasukan, 0, ",", ".") }}';
            const totalPengeluaranStr = '{{ number_format($pengeluaran, 0, ",", ".") }}';

            historyState.filtered.forEach(row => {
                const clonedRow = row.cloneNode(true);
                clonedRow.style.display = '';
                tbody.appendChild(clonedRow);
            });

            const colCount = table.rows[0].cells.length;

            const trSpacer = document.createElement('tr');
            trSpacer.innerHTML = `<td colspan="${colCount}"></td>`;
            tbody.appendChild(trSpacer);

            const tr1 = document.createElement('tr');
            tr1.innerHTML = `<td colspan="${colCount - 1}" style="text-align: right; font-weight: bold;">Total Saldo Masuk</td><td style="font-weight: bold; color: #16a34a;">Rp ${totalPemasukanStr}</td>`;
            tbody.appendChild(tr1);
            
            const tr2 = document.createElement('tr');
            tr2.innerHTML = `<td colspan="${colCount - 1}" style="text-align: right; font-weight: bold;">Total Saldo Keluar</td><td style="font-weight: bold; color: #dc2626;">Rp ${totalPengeluaranStr}</td>`;
            tbody.appendChild(tr2);

            const tr3 = document.createElement('tr');
            tr3.innerHTML = `<td colspan="${colCount - 1}" style="text-align: right; font-weight: bold;">Total Saldo Bersih</td><td style="font-weight: bold;">Rp ${totalBersihStr}</td>`;
            tbody.appendChild(tr3);
        } else {
            // For cashbox table, just get visible rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    tbody.appendChild(row.cloneNode(true));
                }
            });
        }
        
        const wb = XLSX.utils.table_to_book(clone, {sheet: title});
        XLSX.writeFile(wb, `${title.replace(/\s+/g, '_')}.xlsx`);
    }

    function exportToPDF() {
        const activeSection = document.querySelector('.view-section.active');
        if (!activeSection) return;
        const table = activeSection.querySelector('table');
        if (!table) return;
        
        if (typeof html2pdf === 'undefined') {
            Swal.fire('Error', 'Library PDF belum dimuat. Silakan tunggu sebentar atau muat ulang halaman.', 'error');
            return;
        }
        
        let title = 'Data Keuangan';
        const headerText = activeSection.querySelector('h3, h4');
        if (headerText) {
            title = headerText.innerText;
        } else {
            const id = activeSection.getAttribute('id');
            if (id) {
                title = id.replace('view-', '').replace('-', ' ').replace(/\b\w/g, c => c.toUpperCase());
            }
        }
        
        // Clone table to only include filtered rows
        const clone = table.cloneNode(true);
        const tbody = clone.querySelector('tbody');
        tbody.innerHTML = '';
        
        let summaryHtml = '';

        if (activeSection.id === 'view-arus-uang') {
            const totalBersihStr = '{{ number_format($saldo_bersih, 0, ",", ".") }}';
            const totalPemasukanStr = '{{ number_format($pemasukan, 0, ",", ".") }}';
            const totalPengeluaranStr = '{{ number_format($pengeluaran, 0, ",", ".") }}';

            historyState.filtered.forEach(row => {
                const clonedRow = row.cloneNode(true);
                clonedRow.style.display = '';
                tbody.appendChild(clonedRow);
            });

            summaryHtml = `
                <div style="display: flex; justify-content: space-between; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Saldo Masuk</div>
                        <div style="font-size: 16px; font-weight: 700; color: #16a34a;">Rp ${totalPemasukanStr}</div>
                    </div>
                    <div style="flex: 1; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Saldo Keluar</div>
                        <div style="font-size: 16px; font-weight: 700; color: #dc2626;">Rp ${totalPengeluaranStr}</div>
                    </div>
                    <div style="flex: 1; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Total Bersih</div>
                        <div style="font-size: 16px; font-weight: 700; color: #0f172a;">Rp ${totalBersihStr}</div>
                    </div>
                </div>
            `;
        } else {
            // For cashbox table, just get visible rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    tbody.appendChild(row.cloneNode(true));
                }
            });
        }
        
        const htmlContent = `
            <div style="width: 1050px; background: #fff; padding: 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">
                <style>
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #e2e8f0; padding: 12px 10px; text-align: left; font-size: 13px; color: #334155; }
                    th { background-color: #f8fafc; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
                    tr:nth-child(even) { background-color: #f8fafc; }
                    .status-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; white-space: nowrap; }
                    .badge-masuk { background: #dcfce7; color: #16a34a; }
                    .badge-keluar { background: #fee2e2; color: #dc2626; }
                </style>
                <div style="margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #0081C9;">${title}</h1>
                    <div style="text-align: right; font-size: 12px; color: #64748b;">
                        <div>Sistem POS & Keuangan TWINS</div>
                        <div>Dicetak pada: ${new Date().toLocaleString('id-ID')}</div>
                    </div>
                </div>
                ${summaryHtml}
                ${clone.outerHTML}
            </div>
        `;

        Swal.fire({
            title: 'Memproses PDF...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const opt = {
            margin:       0.4,
            filename:     `${title.replace(/\s+/g, '_')}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        
        html2pdf().set(opt).from(htmlContent).save().then(() => {
            Swal.close();
        }).catch(err => {
            Swal.fire('Error', 'Gagal membuat PDF', 'error');
        });
    }

    function filterTransfer() {
        const input = document.getElementById('transferSearch').value.toLowerCase();
        document.querySelectorAll('.transfer-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    async function submitTransferSaldo(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = btn.innerHTML;
        
        btn.innerHTML = `<iconify-icon icon="eos-icons:loading" style="font-size: 1.2em; vertical-align: middle; margin-right: 5px;"></iconify-icon> Memproses...`;
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';

        try {
            const formData = new FormData(form);
            const response = await fetch("{{ route('keuangan.transfer.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message || 'Pemindahan saldo berhasil dicatat!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('keuangan.index', ['tab' => 'arus-uang']) }}";
                });
            } else {
                let errorMessage = data.message || 'Gagal memproses pemindahan saldo.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('<br>');
                }
                btn.innerHTML = originalBtnHtml;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: errorMessage
                });
            }
        } catch (error) {
            btn.innerHTML = originalBtnHtml;
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: 'Gagal menghubungi server. Silakan coba lagi.'
            });
        }
    }
</script>
@endsection
