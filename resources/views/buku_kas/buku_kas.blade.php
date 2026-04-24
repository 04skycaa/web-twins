@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .tab-pill, .btn-action, .chip, .close-modal, .btn-filter {
        user-select: none; /* ANTI BLOK TEKS */
    }

    .status-badge {
        padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
    }
    .status-lunas { background: #E8F5E9; color: #2E7D32; }
    .status-belum { background: #FFF3E0; color: #E65100; }
    
    .chips-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip {
        background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px;
        font-size: 0.8rem; cursor: pointer; transition: 0.2s; border: none; font-weight: 500;
    }
    .chip:hover { background: #e2e8f0; }
    
    .empty-state { text-align: center; padding: 40px; color: #999; }
</style>

<div class="fitur-container" id="bukukas-app">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('pengeluaran')" id="pill-pengeluaran">
            <iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>
            <span>Pengeluaran</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('pemasukan')" id="pill-pemasukan">
            <iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>
            <span>Pemasukan</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('hutang')" id="pill-hutang">
            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
            <span>Hutang</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('piutang')" id="pill-piutang">
            <iconify-icon icon="solar:hand-money-bold-duotone"></iconify-icon>
            <span>Piutang</span>
        </a>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div style="display: contents;">
            <div class="left-actions-group">
                <div class="search-wrapper" style="display: flex; gap: 8px; align-items: center; width: 100%; border: none;">
                    <div style="position: relative; flex: 1; display: flex; align-items: center;">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon" style="position: absolute; left: 10px;"></iconify-icon>
                        <input type="text" id="globalSearch" class="search-input" style="width: 100%; pl-4" placeholder="Cari keterangan..." onkeyup="filterTable()">
                    </div>
                    <div style="position: relative;">
                        <input type="date" id="dateFilter" onchange="filterTable()" style="opacity: 0; position: absolute; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer;" title="Filter Tanggal">
                        <button type="button" class="btn-filter" style="width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0; pointer-events: none;">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        </button>
                    </div>
                </div>

                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div style="display: inline-flex; align-items: center; background: #fff; padding: 5px 10px; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <iconify-icon icon="solar:shop-bold-duotone" style="color: var(--primary-blue); font-size: 18px; margin-right: 5px;"></iconify-icon>
                        <form id="storeForm" method="GET" action="{{ route('keuangan.transaksi') }}">
                            <input type="hidden" name="active_tab" id="storeFormActiveTab" value="">
                            <select name="store_id" style="background: transparent; border: none; font-weight: 600; cursor: pointer; color: var(--primary-blue); outline: none; font-size: 14px;" onchange="document.getElementById('storeFormActiveTab').value = currentTab; document.getElementById('storeForm').submit()">
                                @if(Auth::user()->role === 'owner')
                                    <option value="all" {{ $store_id === 'all' ? 'selected' : '' }}>Semua Outlet</option>
                                @endif
                                @foreach($outlets as $o)
                                    <option value="{{ $o->uuid }}" {{ $store_id == $o->uuid ? 'selected' : '' }}>
                                        {{ $o->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif
            </div>

            <div class="right-actions">
                <button type="button" class="btn-action" id="btnAddMain" onclick="openCurrentModal()">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span id="txtAddMain">Tambah Pengeluaran</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="alertSuccess" style="background: #E8F5E9; color: #2E7D32; padding: 10px 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; border-left: 4px solid #4CAF50; transition: opacity 0.5s ease;">
            <iconify-icon icon="solar:check-circle-bold-duotone" style="font-size: 18px;"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #FFEBEE; color: #C62828; padding: 10px 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #F44336;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formGlobalDeleteCf" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="formGlobalDeleteDebt" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- MAIN BOX --}}
    <div class="main-content-box">
        <div class="table-container">
            
            <!-- VIEW PENGELUARAN -->
            <div id="view-pengeluaran">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>KETERANGAN</th>
                            <th>NOMINAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pengeluaran">
                        @forelse($pengeluaran as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                            <td><strong>{{ $p->keterangan }}</strong></td>
                            <td class="price-text" style="color: #C62828;">- Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-state">Belum ada data pengeluaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW PEMASUKAN -->
            <div id="view-pemasukan" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>KETERANGAN</th>
                            <th>NOMINAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pemasukan">
                        @forelse($pemasukan as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                            <td><strong>{{ $p->keterangan }}</strong></td>
                            <td class="price-text" style="color: #2E7D32;">+ Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-state">Belum ada data pemasukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW HUTANG -->
            <div id="view-hutang" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>SUPPLIER</th>
                            <th>TOTAL HUTANG</th>
                            <th>SISA TAGIHAN</th>
                            <th>STATUS</th>
                            <th>JATUH TEMPO</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-hutang">
                        @forelse($hutang as $h)
                        <tr>
                            <td><strong>{{ $h->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($h->sisa, 0, ',', '.') }}</td>
                            <td>
                                <span class="status-badge {{ $h->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">
                                    {{ $h->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($h->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($h) }}, {{ json_encode($h->contact) }}, {{ json_encode($h->detailDebts) }})" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($h) }}, {{ json_encode($h->contact) }})" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $h->uuid }}', 'Hutang')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data hutang supplier.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW PIUTANG -->
            <div id="view-piutang" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>CUSTOMER</th>
                            <th>TOTAL PIUTANG</th>
                            <th>SISA TAGIHAN</th>
                            <th>STATUS</th>
                            <th>JATUH TEMPO</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-piutang">
                        @forelse($piutang as $p)
                        <tr>
                            <td><strong>{{ $p->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($p->sisa, 0, ',', '.') }}</td>
                            <td>
                                <span class="status-badge {{ $p->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">
                                    {{ $p->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($p) }}, {{ json_encode($p->contact) }}, {{ json_encode($p->detailDebts) }})" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($p) }}, {{ json_encode($p->contact) }})" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $p->uuid }}', 'Piutang')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data piutang customer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Modal Detail Pemasukan / Pengeluaran (CashFlow) -->
<div id="modalDetailCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 380px; padding: 30px;">
        <div style="text-align: center;">
            <div id="cfIcon" style="width: 60px; height: 60px; border-radius: 30px; display: inline-flex; justify-content: center; align-items: center; font-size: 30px; margin-bottom: 10px; background: #E8F5E9; color: #2E7D32;">
                <!-- Filled by JS -->
            </div>
            <h4 id="cfTitle" style="margin: 0; color: #333; font-size: 16px; font-weight: 700;">Detail Pemasukan</h4>
            <h2 id="cfNominal" style="margin: 10px 0 25px 0; font-size: 28px; color: #1e293b;">Rp 0</h2>
        </div>
        
        <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:document-text-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Keterangan</div>
                    <div id="cfKeterangan" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:clock-circle-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Tanggal & Waktu</div>
                    <div id="cfTanggal" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:shop-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Toko</div>
                    <div id="cfToko" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:user-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Karyawan</div>
                    <div id="cfKaryawan" style="font-weight: 600;">-</div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 35px;">
            <button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailCashFlow')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div id="modalPengeluaran" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Pengeluaran</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPengeluaran')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST">
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="jenis" value="Pengeluaran">
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label>
                <input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Nominal (Rp) *</label>
                <input type="number" name="nominal" class="form-control" placeholder="0" required>
            </div>
            
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="ketPengeluaran" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan pengeluaran..."></textarea>
                <div style="font-size: 11px; color: #888; margin-top: 10px;">Saran:</div>
                <div class="chips-container">
                    @foreach(['Gaji', 'Sewa Tempat', 'Listrik', 'Air', 'Bensin', 'Bahan Baku', 'Lain-lain'] as $sar)
                        <button type="button" class="chip" onclick="document.getElementById('ketPengeluaran').value = '{{ $sar }}'">{{ $sar }}</button>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPengeluaran')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Pemasukan -->
<div id="modalPemasukan" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Pemasukan</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPemasukan')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST">
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="jenis" value="Pemasukan">
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label>
                <input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Nominal (Rp) *</label>
                <input type="number" name="nominal" class="form-control" placeholder="0" required>
            </div>
            
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="ketPemasukan" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan pemasukan..."></textarea>
                <div style="font-size: 11px; color: #888; margin-top: 10px;">Saran:</div>
                <div class="chips-container">
                    @foreach(['Tip/Upah', 'Bonus', 'Pengembalian', 'Koreksi Kas', 'Lain-lain'] as $sar)
                        <button type="button" class="chip" onclick="document.getElementById('ketPemasukan').value = '{{ $sar }}'">{{ $sar }}</button>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPemasukan')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pemasukan / Pengeluaran (CashFlow) -->
<div id="modalEditCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 id="editCfTitle">Edit Transaksi</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditCashFlow')">&times;</button>
        </div>
        <form id="formEditCashFlow" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label>
                <input type="date" name="tanggal" id="editCfTanggalInput" class="form-control" style="border:none; padding:5px 0" required>
            </div>

            <div class="form-group">
                <label>Nominal (Rp) *</label>
                <input type="number" name="nominal" id="editCfNominalInput" class="form-control" placeholder="0" required>
            </div>
            
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="editCfKeteranganInput" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalEditCashFlow')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Hutang Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalHutang')">&times;</button>
        </div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST">
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="tipe" value="Hutang">
            
            <div class="form-group">
                <label>Supplier / Kontak *</label>
                <div style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 8px; padding-left: 10px; background: #fff;">
                    <iconify-icon icon="solar:user-bold-duotone" style="color: var(--primary-blue); font-size: 18px; margin-right: 5px;"></iconify-icon>
                    <select name="kontak_nama" class="form-control" style="border: none; outline: none; margin: 0; padding-left: 0; background: transparent;" required>
                        <option value="">Pilih supplier...</option>
                        @forelse($suppliers as $supplier)
                            <option value="{{ $supplier->nama }}">{{ $supplier->nama }}</option>
                        @empty
                            <option value="" disabled>Belum ada data supplier</option>
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Total Nilai Hutang (Rp) *</label>
                <input type="number" name="nominal" class="form-control" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Opsi: Uang Muka / DP (Rp)</label>
                <input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP">
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalHutang')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalPiutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Piutang Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPiutang')">&times;</button>
        </div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST">
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="tipe" value="Piutang">
            
            <div class="form-group">
                <label>Customer / Kontak *</label>
                <div style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 8px; padding-left: 10px; background: #fff;">
                    <iconify-icon icon="solar:user-bold-duotone" style="color: var(--primary-blue); font-size: 18px; margin-right: 5px;"></iconify-icon>
                    <select name="kontak_nama" class="form-control" style="border: none; outline: none; margin: 0; padding-left: 0; background: transparent;" required>
                        <option value="">Pilih customer...</option>
                        @forelse($customers as $customer)
                            <option value="{{ $customer->nama }}">{{ $customer->nama }}</option>
                        @empty
                            <option value="" disabled>Belum ada data customer</option>
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Total Nilai Piutang (Rp) *</label>
                <input type="number" name="nominal" class="form-control" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Opsi: Uang Muka / DP (Rp)</label>
                <input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP">
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPiutang')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Dept -->
<div id="modalDetailDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="debtDetailTitle">Detail Tagihan</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalDetailDebt')">&times;</button>
        </div>
        
        <div style="background: #f8fbff; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #dbeafe;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="font-weight: 600; color: #333;" id="debtContactName"><iconify-icon icon="solar:user-bold-duotone" style="color: var(--primary-blue)"></iconify-icon> Nama</div>
                <div id="debtStatus" class="status-badge">Status</div>
            </div>
            <div style="font-size: 12px; color: #666; margin-bottom: 15px;" id="debtDueDate">
                <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon> Jatuh tempo: -
            </div>
            
            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #bfdbfe; padding-bottom: 10px; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 11px; color: #666;">Total Tagihan</div>
                    <div style="font-weight: 700; font-size: 16px; color: #333;" id="debtTotal">Rp 0</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #666;">Sisa Tagihan</div>
                    <div style="font-weight: 700; font-size: 18px; color: var(--primary-blue);" id="debtSisa">Rp 0</div>
                </div>
            </div>
            
            <form id="formPayDebt" method="POST">
                @csrf
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="number" name="bayar" class="form-control" style="flex: 1; margin: 0;" placeholder="Nominal cicilan..." required>
                    <button type="submit" class="btn-action" style="white-space: nowrap; background: #2E7D32;">Topup / Bayar</button>
                </div>
            </form>
        </div>

        <h4 style="font-size: 13px; margin-bottom: 10px; color: #333; font-weight: 600;">Log Pembayaran</h4>
        <div id="debtHistoryList" style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;"></div>

        <div style="display: flex; justify-content: center; margin-top: 25px;">
            <button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailDebt')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Edit Debt -->
<div id="modalEditDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Edit Tagihan</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditDebt')">&times;</button>
        </div>
        <form id="formEditDebtAction" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Nama / Kontak *</label>
                <input type="text" id="editDebtKontak" name="kontak_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Total Nilai (Rp) *</label>
                <input type="number" id="editDebtNominal" name="nominal" class="form-control" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" id="editDebtJatuhTempo" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalEditDebt')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentTab = '{{ request('active_tab', session('active_tab', 'pengeluaran')) }}';

    // Auto-hide success alert
    window.addEventListener('DOMContentLoaded', () => {
        let alertObj = document.getElementById('alertSuccess');
        if (alertObj) {
            setTimeout(() => {
                alertObj.style.opacity = '0';
                setTimeout(() => alertObj.style.display = 'none', 500);
            }, 3000);
        }
        
        // init default tab based on session
        switchTab(currentTab);
    });

    function switchTab(tabId) {
        currentTab = tabId;
        
        // Reset pills
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        // Hide all views
        document.getElementById('view-pengeluaran').style.display = 'none';
        document.getElementById('view-pemasukan').style.display = 'none';
        document.getElementById('view-hutang').style.display = 'none';
        document.getElementById('view-piutang').style.display = 'none';
        
        // Show active view
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.style.display = 'block';

        // Update search placeholder and modal texts
        const searchInput = document.getElementById('globalSearch');
        const txtAdd = document.getElementById('txtAddMain');
        
        if(tabId == 'pengeluaran') {
            searchInput.placeholder = 'Cari keterangan...';
            txtAdd.innerText = 'Tambah Pengeluaran';
        } else if(tabId == 'pemasukan') {
            searchInput.placeholder = 'Cari keterangan...';
            txtAdd.innerText = 'Tambah Pemasukan';
        } else if(tabId == 'hutang') {
            searchInput.placeholder = 'Cari supplier...';
            txtAdd.innerText = 'Tambah Hutang';
        } else if(tabId == 'piutang') {
            searchInput.placeholder = 'Cari customer...';
            txtAdd.innerText = 'Tambah Piutang';
        }
    }

    function openCurrentModal() {
        if(currentTab == 'pengeluaran') openModal('modalPengeluaran');
        else if(currentTab == 'pemasukan') openModal('modalPemasukan');
        else if(currentTab == 'hutang') openModal('modalHutang');
        else if(currentTab == 'piutang') openModal('modalPiutang');
    }

    function openModal(id) { 
        document.getElementById(id).style.display = 'flex'; 
    }
    
    function closeModal(id) { 
        document.getElementById(id).style.display = 'none'; 
    }

    function filterTable() {
        const searchText = document.getElementById('globalSearch').value.toLowerCase();
        const dateRaw = document.getElementById('dateFilter').value; // format YYYY-MM-DD
        let dateQuery = "";
        
        if (dateRaw) {
            const parts = dateRaw.split('-');
            dateQuery = `${parts[2]}/${parts[1]}/${parts[0]}`; // Turns 2026-04-21 into 21/04/2026
        }

        const rows = document.querySelectorAll(`#tbody-${currentTab} tr`);
        rows.forEach(row => {
            if(row.querySelector('.empty-state')) return;
            const textMatch = row.innerText.toLowerCase().includes(searchText);
            const dateMatch = dateQuery === "" || row.innerText.includes(dateQuery);
            row.style.display = (textMatch && dateMatch) ? '' : 'none';
        });
    }

    // CASHFLOW LOGIC
    function viewCashFlowDetail(cf) {
        const isPemasukan = cf.jenis.toLowerCase() === 'pemasukan';
        const iconDiv = document.getElementById('cfIcon');
        
        if (isPemasukan) {
            iconDiv.style.background = '#E8F5E9';
            iconDiv.style.color = '#2E7D32';
            iconDiv.innerHTML = '<iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>';
        } else {
            iconDiv.style.background = '#FFEBEE';
            iconDiv.style.color = '#C62828';
            iconDiv.innerHTML = '<iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>';
        }

        const jenisName = cf.jenis.charAt(0).toUpperCase() + cf.jenis.slice(1).toLowerCase();
        document.getElementById('cfTitle').innerText = 'Detail ' + jenisName;
        
        const formatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        document.getElementById('cfNominal').innerText = 'Rp ' + formatter.format(cf.nominal);
        document.getElementById('cfKeterangan').innerText = cf.keterangan || '-';
        
        let dt = new Date(cf.tanggal);
        document.getElementById('cfTanggal').innerText = dt.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
        document.getElementById('cfToko').innerText = (cf.outlet && cf.outlet.nama) ? cf.outlet.nama : '-';
        document.getElementById('cfKaryawan').innerText = (cf.user && cf.user.name) ? cf.user.name : '-';
        
        openModal('modalDetailCashFlow');
    }

    function openEditCashFlow(cf) {
        const jenisName = cf.jenis.charAt(0).toUpperCase() + cf.jenis.slice(1).toLowerCase();
        document.getElementById('editCfTitle').innerText = 'Edit ' + jenisName;
        document.getElementById('formEditCashFlow').action = '/buku-kas/cashflow/' + cf.uuid;
        
        let d = new Date(cf.tanggal);
        let tzOffset = d.getTimezoneOffset() * 60000;
        let localISOTime = (new Date(d.getTime() - tzOffset)).toISOString().slice(0, 10);
        
        document.getElementById('editCfTanggalInput').value = localISOTime;
        document.getElementById('editCfNominalInput').value = cf.nominal;
        document.getElementById('editCfKeteranganInput').value = cf.keterangan;
        
        openModal('modalEditCashFlow');
    }

    function deleteCf(id, typeName) {
        Swal.fire({
            title: `Hapus ${typeName}?`,
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('formGlobalDeleteCf');
                form.action = '/buku-kas/cashflow/' + id;
                form.submit();
            }
        });
    }

    // DEBT LOGIC
    function viewDebtDetail(debt, contact, details) {
        document.getElementById('debtDetailTitle').innerText = 'Detail ' + debt.tipe;
        document.getElementById('debtContactName').innerHTML = contact ? contact.nama : '-';
        document.getElementById('debtDueDate').innerText = 'Jatuh tempo: ' + debt.jatuh_tempo;
        
        const formatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        document.getElementById('debtTotal').innerText = 'Rp ' + formatter.format(debt.nominal);
        document.getElementById('debtSisa').innerText = 'Rp ' + formatter.format(debt.sisa);
        
        const badge = document.getElementById('debtStatus');
        if (debt.sisa <= 0) {
            badge.innerText = 'Lunas';
            badge.className = 'status-badge status-lunas';
            document.getElementById('formPayDebt').style.display = 'none';
        } else {
            badge.innerText = 'Belum Lunas';
            badge.className = 'status-badge status-belum';
            document.getElementById('formPayDebt').style.display = 'flex';
        }

        document.getElementById('formPayDebt').action = '/buku-kas/debt/' + debt.uuid + '/pay';

        const list = document.getElementById('debtHistoryList');
        list.innerHTML = '';
        if (details.length === 0) {
            list.innerHTML = '<div style="font-size:12px; color:#888; text-align:center; padding: 10px;">Belum ada riwayat pembayaran</div>';
        } else {
            details.forEach((d, idx) => {
                list.innerHTML += `
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <div style="background: #e0f2fe; color: #0ea5e9; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <iconify-icon icon="solar:money-bag-bold-duotone" style="font-size: 1.1rem;"></iconify-icon>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 13px; color: #333;">Bayar: Rp ${formatter.format(d.bayar)}</div>
                            <div style="font-size: 11px; color: #666;">Sebelum: Rp ${formatter.format(d.sebelum)} &rarr; Sisa: Rp ${formatter.format(d.sisa)}</div>
                        </div>
                    </div>
                </div>`;
            });
        }
        openModal('modalDetailDebt');
    }

    function openEditDebt(debt, contact) {
        document.getElementById('formEditDebtAction').action = '/buku-kas/debt/' + debt.uuid;
        document.getElementById('editDebtKontak').value = contact ? contact.nama : '';
        document.getElementById('editDebtNominal').value = debt.nominal;
        document.getElementById('editDebtJatuhTempo').value = debt.jatuh_tempo;
        openModal('modalEditDebt');
    }

    function deleteDebt(id, typeName) {
        Swal.fire({
            title: `Hapus ${typeName}?`,
            text: "Data ini beserta log cicilannya akan terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('formGlobalDeleteDebt');
                form.action = '/buku-kas/debt/' + id;
                form.submit();
            }
        });
    }

</script>
@endsection
