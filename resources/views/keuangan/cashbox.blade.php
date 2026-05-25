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

        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>Nama Metode</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cashbox">
                        @forelse($cashboxes as $cb)
                            <tr>
                                <td style="font-weight: 600;">{{ $cb->nama_metode }}</td>
                                <td>
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="openEditCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')" title="Edit">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')" title="Hapus">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="text-align: center; padding: 30px; color: #888;">Belum ada data Cashbox.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION ARUS UANG --}}
    <div id="view-arus-uang" class="view-section {{ $active_tab === 'arus-uang' ? 'active' : '' }}">
        <div class="action-bar flex-wrap mobile-action-bar" style="margin-bottom: 20px;">
            <div class="left-actions-group flex-wrap">
                <form action="{{ route('keuangan.index') }}" method="GET" id="filterForm" style="display: flex; gap: 8px; flex: 1;" class="mobile-action-bar">
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
                                <button type="submit" class="btn-action" style="width: 100%; justify-content: center;">Terapkan</button>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'owner')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="javascript:void(0)" onclick="setStore('all')">Semua Outlet</a>
                            @foreach($outlets as $outlet)
                                <a href="javascript:void(0)" onclick="setStore('{{ $outlet->uuid }}')">{{ $outlet->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="store_id" id="filter_store_id" value="{{ $store_id }}">
                    @endif
                </form>
            </div>
            
            <div class="right-actions">
                <button type="button" class="btn-action" style="background: #0081C9;" onclick="openModal('modalTransferSaldo')">
                    <iconify-icon icon="solar:card-transfer-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    <span>Pemindahan Saldo</span>
                </button>
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
                                <td><span class="status-badge {{ $item->jenis == 'pemasukan' ? 'badge-masuk' : 'badge-keluar' }}">{{ $item->jenis }}</span></td>
                                <td style="text-align: right; font-weight: 700; color: {{ $item->jenis == 'pemasukan' ? '#16a34a' : '#dc2626' }};">
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
        document.getElementById('filterForm').submit();
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
        
        if (typeof XLSX === 'undefined') return;
        const wb = XLSX.utils.table_to_book(table, {sheet: title});
        XLSX.writeFile(wb, `${title.replace(/\s+/g, '_')}.xlsx`);
    }

    function exportToPDF() {
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
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>\${title}</title>
                <style>
                    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; padding: 30px; color: #1e293b; background: #fff; }
                    .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; }
                    .header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #0081C9; }
                    .header .meta { text-align: right; font-size: 12px; color: #64748b; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #e2e8f0; padding: 12px 10px; text-align: left; font-size: 13px; }
                    th { background-color: #f8fafc; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
                    tr:nth-child(even) { background-color: #f8fafc; }
                    .price-text { font-weight: 600; font-family: monospace; }
                    .pemasukan-text { color: #2E7D32; }
                    .pengeluaran-text { color: #C62828; }
                    @media print {
                        body { padding: 0; }
                        @page { margin: 1.5cm; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>\${title}</h1>
                    <div class="meta">
                        <div>Sistem POS & Keuangan TWINS</div>
                        <div>Dicetak pada: \${new Date().toLocaleString('id-ID')}</div>
                    </div>
                </div>
                \${table.outerHTML}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
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
        
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: errorMessage
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: 'Gagal menghubungi server. Silakan coba lagi.'
            });
        }
    }
</script>
@endsection
