@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
@endpush

@section('content')
@php $active_tab = request('tab', 'riwayat'); @endphp
<div class="fitur-container" id="transaksi-app">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab === 'riwayat' ? 'active' : '' }}" onclick="switchTab('riwayat')" id="pill-riwayat">
            <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
            <span>Riwayat Transaksi</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab === 'diskon' ? 'active' : '' }}" onclick="switchTab('diskon')" id="pill-diskon">
            <iconify-icon icon="solar:sale-bold-duotone"></iconify-icon>
            <span>Manajemen Diskon</span>
        </a>
    </div>



    @if($errors->any())
    <div style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <iconify-icon icon="solar:danger-bold-duotone" style="font-size: 24px;"></iconify-icon>
        <div>
            <div style="font-weight: 700; font-size: 14px;">Terjadi Kesalahan!</div>
            <ul style="margin: 5px 0 0 20px; font-size: 13px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- SECTION RIWAYAT --}}
    <div id="view-riwayat" class="view-section {{ $active_tab === 'riwayat' ? 'active' : '' }}">
        <div class="action-bar">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" class="search-input" id="riwayatSearch" placeholder="Cari kode atau pelanggan..." onkeyup="filterTable('riwayat')">
                </div>
            </div>
            <div class="right-actions">
                <button class="btn-action" onclick="window.location.reload()">
                    <iconify-icon icon="solar:refresh-bold-duotone"></iconify-icon>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table" id="riwayatTable">
                    <thead>
                        <tr>
                            <th style="white-space: nowrap;">KODE ORDER</th>
                            <th style="white-space: nowrap;">TANGGAL</th>
                            <th style="white-space: nowrap;">PELANGGAN</th>
                            <th style="text-align: center; white-space: nowrap;">ITEMS</th>
                            <th style="text-align: right; white-space: nowrap;">TOTAL</th>
                            <th style="text-align: right; white-space: nowrap;">DISKON</th>
                            <th style="text-align: center; white-space: nowrap;">STATUS</th>
                            <th style="text-align: center; white-space: nowrap;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $trx)
                        <tr>
                            <td style="padding-right: 20px; vertical-align: middle;">
                                @php
                                    $parts = explode('-', $trx['id']);
                                    $topPart = $trx['id'];
                                    $bottomPart = null;
                                    if (count($parts) >= 3) {
                                        $topPart = '#' . $parts[0] . '-' . $parts[1];
                                        $bottomPart = $parts[2];
                                    } elseif (count($parts) == 2) {
                                        $topPart = '#' . $parts[0];
                                        $bottomPart = $parts[1];
                                    } else {
                                        $topPart = '#' . $trx['id'];
                                    }
                                @endphp
                                <div style="line-height: 1.2;">
                                    <span style="font-weight: 700; color: var(--primary-blue); font-size: 11px; display: block; white-space: nowrap;">{{ $topPart }}</span>
                                    @if($bottomPart)
                                        <span style="font-size: 9.5px; color: #94a3b8; display: block; margin-top: 2px; font-family: monospace; white-space: nowrap;">{{ $bottomPart }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-size: 12px; color: #64748b; white-space: nowrap; padding-right: 20px;">{{ $trx['tanggal'] }}</td>
                            <td style="white-space: nowrap; padding-right: 20px; text-transform: capitalize;"><strong>{{ strtolower($trx['pelanggan']) }}</strong></td>
                            <td style="text-align: center; white-space: nowrap; padding-right: 20px;"><span class="category-badge cat-poster" style="background: #f1f5f9; color: #475569; white-space: nowrap; font-size: 11px; padding: 4px 10px;">{{ $trx['qty'] }} items</span></td>
                            <td style="font-weight: 700; color: #1e293b; white-space: nowrap; text-align: right; padding-right: 20px;">{{ $trx['total'] }}</td>
                            <td style="color: #ef4444; font-size: 12px; white-space: nowrap; text-align: right; padding-right: 20px;">{{ $trx['diskon'] }}</td>
                            <td style="text-align: center; white-space: nowrap; padding-right: 20px;">
                                @php
                                    $statusClass = 'status-inactive';
                                    $statusLabel = $trx['status'];
                                    $statusLower = strtolower($trx['status']);
                                    if (in_array($statusLower, ['settlement', 'capture', 'paid', 'success'])) {
                                        $statusClass = 'status-active';
                                    } elseif (in_array($statusLower, ['pending'])) {
                                        $statusClass = 'stat-proses';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}" style="white-space: nowrap; font-size: 11px; padding: 4px 12px;">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button class="btn-filter-small" 
                                    style="color: #0081C9; width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; padding: 0; line-height: 1;" 
                                    data-trx='{{ json_encode($trx) }}'
                                    onclick="openViewModalTransaksi(this)">
                                    <iconify-icon icon="solar:eye-bold-duotone" style="font-size: 15px;"></iconify-icon>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align: center; padding: 50px; color: #94a3b8;">Belum ada data transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentOrders->hasPages())
<div class="pagination-container" style="margin-top: 24px; display:flex; justify-content:center; align-items:center; gap:8px;">
    @php
        $total = $paymentOrders->lastPage();
        $current = $paymentOrders->currentPage();
        $group = intdiv($current - 1, 3);
        $start = $group * 3 + 1;
        $end = min($start + 2, $total);
        $prevGroup = $start - 3;
        $nextGroup = $start + 3;
    @endphp
    @if($prevGroup >= 1)
        <a class="page-item" href="{{ $paymentOrders->url($prevGroup) }}" rel="prev"><span class="page-link">&lt;</span></a>
    @endif
    @for($i = $start; $i <= $end; $i++)
        <a class="page-item {{ $i == $current ? 'active' : '' }}" href="{{ $paymentOrders->url($i) }}"><span class="page-link">{{ $i }}</span></a>
    @endfor
    @if($nextGroup <= $total)
        <a class="page-item" href="{{ $paymentOrders->url($nextGroup) }}" rel="next"><span class="page-link">&gt;</span></a>
    @endif
</div>
@endif
        </div>
    </div>

    {{-- SECTION DISKON --}}
    <div id="view-diskon" class="view-section {{ $active_tab === 'diskon' ? 'active' : '' }}">
        <div class="sub-tab-navigation" style="margin-bottom: 20px;">
            <button class="sub-tab-pill active" onclick="filterCategory('all', this)">
                <iconify-icon icon="solar:layers-bold-duotone"></iconify-icon>
                Semua
            </button>
            <button class="sub-tab-pill" onclick="filterCategory('promo', this)">
                <iconify-icon icon="solar:gallery-bold-duotone"></iconify-icon>
                Poster
            </button>
            <button class="sub-tab-pill" onclick="filterCategory('diskon', this)">
                <iconify-icon icon="solar:ticket-sale-bold-duotone"></iconify-icon>
                Diskon
            </button>
            <button class="sub-tab-pill" onclick="filterCategory('voucer', this)">
                <iconify-icon icon="solar:wad-of-money-bold-duotone"></iconify-icon>
                Voucer
            </button>
        </div>

        <div class="action-bar">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" class="search-input" id="promoSearch" placeholder="Cari nama atau kode..." onkeyup="searchPromo()">
                </div>
            </div>
            <div class="right-actions">
                <button class="btn-action" onclick="openModal('addModalDiskon')">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span>Tambah Baru</span>
                </button>
            </div>
        </div>

        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table" id="promoTable">
                    <thead>
                        <tr>
                            <th style="width: 35%; white-space: nowrap;">NAMA</th>
                            <th style="text-align: center; white-space: nowrap;">KODE</th>
                            <th style="text-align: center; white-space: nowrap;">KATEGORI</th>
                            <th style="text-align: center; white-space: nowrap;">MULAI</th>
                            <th style="text-align: center; white-space: nowrap;">SELESAI</th>
                            <th style="text-align: center; white-space: nowrap;">NILAI</th>
                            <th style="text-align: center; white-space: nowrap;">STATUS</th>
                            <th style="text-align: center; white-space: nowrap;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diskons as $diskon)
                        <tr class="promo-row" data-category="{{ strtolower($diskon->tipe) }}">
                            <td style="padding: 10px 16px;">
                                <div class="promo-info-cell">
                                    <div class="promo-thumb">
                                        @if(strtolower($diskon->tipe) == 'promo' && $diskon->image_banner)
                                            <img src="{{ \App\Http\Controllers\LandingController::resolveImageUrl($diskon->image_banner) }}" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            @php
                                                $icon = 'solar:tag-bold-duotone';
                                                if(strtolower($diskon->tipe) == 'voucer') $icon = 'solar:ticket-sale-bold-duotone';
                                                if(strtolower($diskon->tipe) == 'diskon') $icon = 'solar:wad-of-money-bold-duotone';
                                            @endphp
                                            <iconify-icon icon="{{ $icon }}"></iconify-icon>
                                        @endif
                                    </div>
                                    <div class="promo-details">
                                        <span class="name">{{ $diskon->nama_promo }}</span>
                                        <span class="sub" style="white-space: nowrap;">
                                            <iconify-icon icon="solar:box-minimalistic-linear" style="vertical-align: middle;"></iconify-icon> 
                                            {{ $diskon->products->count() }} Produk &bull; 
                                            <iconify-icon icon="solar:shop-linear" style="vertical-align: middle;"></iconify-icon> 
                                            {{ $diskon->stores->count() }} Toko
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 10px 16px; text-align: center; white-space: nowrap;"><code class="promo-code" style="font-size: 11px; padding: 2px 6px; white-space: nowrap;">{{ $diskon->kode_promo ?? '-' }}</code></td>
                            <td style="padding: 10px 16px; text-align: center; white-space: nowrap;">
                                <span class="category-badge cat-{{ strtolower($diskon->tipe) == 'promo' ? 'poster' : strtolower($diskon->tipe) }}" style="font-size: 10px; padding: 3px 10px; white-space: nowrap;">
                                    {{ $diskon->tipe == 'promo' ? 'Poster' : $diskon->tipe }}
                                </span>
                            </td>
                            <td style="padding: 10px 12px; font-size: 11px; color: #64748b; text-align: center; white-space: nowrap;">{{ \Carbon\Carbon::parse($diskon->tanggal_mulai)->format('d/m/Y') }}</td>
                            <td style="padding: 10px 12px; font-size: 11px; color: #64748b; text-align: center; white-space: nowrap;">{{ \Carbon\Carbon::parse($diskon->tanggal_selesai)->format('d/m/Y') }}</td>
                            <td class="price-text" style="padding: 10px 12px; text-align: center; white-space: nowrap;">
                                @php
                                    $specialDiscount = $diskon->products->whereNotNull('pivot.nilai_diskon')->first();
                                    $effectiveValue = $specialDiscount ? $specialDiscount->pivot->nilai_diskon : $diskon->nilai;
                                    $isCustom = $specialDiscount ? true : false;
                                @endphp
                                <div style="font-weight: 700; font-size: 13px; color: #0081C9; white-space: nowrap;">{{ (int)$effectiveValue }}%</div>
                                @if($isCustom)
                                    <div style="font-size: 8px; color: #ef4444; font-weight: 600; white-space: nowrap;">(CUSTOM)</div>
                                @endif
                            </td>
                            <td style="padding: 10px 12px; text-align: center; white-space: nowrap;">
                                <span class="status-badge {{ $diskon->status ? 'status-active' : 'status-inactive' }}" style="font-size: 10px; padding: 2px 8px; white-space: nowrap;">
                                    {{ $diskon->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td style="padding: 10px 12px; text-align: center; white-space: nowrap;">
                                <div style="display: flex; gap: 4px; justify-content: center; white-space: nowrap;">
                                    <button class="btn-filter-small" 
                                        style="width: 26px; height: 26px; color: #0081C9; display: inline-flex; align-items: center; justify-content: center; padding: 0; line-height: 1;" 
                                        data-diskon='{{ json_encode($diskon) }}'
                                        data-products-list='{{ json_encode($diskon->products->pluck("nama_produk")) }}'
                                        data-stores-list='{{ json_encode($diskon->stores->pluck("nama")) }}'
                                        onclick="openViewModalDiskon(this)">
                                        <iconify-icon icon="solar:eye-bold-duotone" style="font-size: 13px;"></iconify-icon>
                                    </button>
                                    <button class="btn-filter-small" 
                                        style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; padding: 0; line-height: 1;" 
                                        data-diskon='{{ json_encode($diskon) }}' 
                                        data-products='{{ json_encode($diskon->products->pluck("uuid")) }}'
                                        data-stores='{{ json_encode($diskon->stores->pluck("uuid")) }}'
                                        onclick="openEditModalDiskon(this)">
                                        <iconify-icon icon="solar:pen-2-bold-duotone" style="font-size: 13px;"></iconify-icon>
                                    </button>
                                    <button class="btn-filter-small btn-danger-soft" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; padding: 0; line-height: 1;" onclick="openDeleteModalDiskon('{{ $diskon->uuid }}')">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" style="font-size: 13px;"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align: center; padding: 50px; color: #94a3b8;">Belum ada data diskon.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($diskons->hasPages())
                <div class="pagination-container" style="margin-top: 24px;">
                    {{ $diskons->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODALS COPIED FROM DISKON.BLADE.PHP --}}
<!-- Modal Tambah -->
<div id="addModalDiskon" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0; color: #0081C9;">Tambah Promo</h3>
            <button class="close-modal" onclick="closeModal('addModalDiskon')">&times;</button>
        </div>
        <form action="{{ route('transaksi.diskon.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-vertical">
                <div class="banner-center-wrapper" id="add_banner_wrapper">
                    <label style="font-size: 11px; margin-bottom: 8px; color: #64748b;">FORMAT BANNER 4x2 (LANDSCAPE)</label>
                    <div class="banner-preview-4x2" onclick="document.getElementById('add_image_input').click()">
                        <div id="add_banner_preview_content">
                            <iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 32px; color: #cbd5e1;"></iconify-icon>
                        </div>
                    </div>
                    <input type="file" name="image_banner" id="add_image_input" style="display: none;" onchange="previewImage(this, 'add_banner_preview_content')" required>
                </div>

                <div class="form-group">
                    <label>Tipe</label>
                    <select name="tipe" id="add_promo_tipe" class="form-control" required onchange="handleCategoryChange(this.value, 'add_banner_wrapper', 'add_kode_wrapper', 'add')">
                        <option value="promo">Promo (Visual Poster)</option>
                        <option value="Voucer">Voucher (Kode Checkout)</option>
                        <option value="Diskon">Diskon (Potongan Langsung)</option>
                    </select>
                </div>

                <div class="form-group" id="add_kode_wrapper" style="display: none;">
                    <label>Kode Voucher</label>
                    <input type="text" name="kode_promo" class="form-control" placeholder="Contoh: BAKE2024">
                </div>

                <div class="form-group">
                    <label>Nama Promo</label>
                    <input type="text" name="nama_promo" class="form-control" required placeholder="Contoh: Promo Manis Lebaran">
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="S&K atau detail promo..."></textarea>
                </div>

                <div class="form-group">
                    <label>Pilih Produk</label>
                    <div class="dropdown-checkbox-wrapper">
                        <div class="dropdown-checkbox-btn" onclick="toggleDropdown('add_product_dropdown', this)">
                            <div class="selected-pills" id="add_product_pills">
                                <span style="color: #94a3b8;">Pilih Produk...</span>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="arrow-icon"></iconify-icon>
                        </div>
                        <div class="dropdown-checkbox-content" id="add_product_dropdown">
                            <div class="dropdown-search">
                                <input type="text" placeholder="Cari produk..." onkeyup="filterDropdown(this, 'add_product_list')">
                            </div>
                            <div class="dropdown-list" id="add_product_list">
                                @foreach($products as $product)
                                <label class="dropdown-item">
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->uuid }}" onchange="handleDropdownCheck(this, 'product', 'add')">
                                    <div class="item-info">
                                        <span class="item-name">{{ $product->nama_produk }}</span>
                                        <span class="item-sub">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Pilih Cabang</label>
                    <div class="dropdown-checkbox-wrapper">
                        <div class="dropdown-checkbox-btn" onclick="toggleDropdown('add_store_dropdown', this)">
                            <div class="selected-pills" id="add_store_pills">
                                <span style="color: #94a3b8;">Pilih Cabang...</span>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="arrow-icon"></iconify-icon>
                        </div>
                        <div class="dropdown-checkbox-content" id="add_store_dropdown">
                            <div class="dropdown-list" id="add_store_list">
                                @foreach($outlets as $outlet)
                                <label class="dropdown-item">
                                    <input type="checkbox" name="store_ids[]" value="{{ $outlet->uuid }}" onchange="handleDropdownCheck(this, 'store', 'add')">
                                    <div class="item-info"><span class="item-name">{{ $outlet->nama }}</span></div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-flex">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" required>
                    </div>
                </div>

                <div class="form-row-flex">
                    <div class="form-group">
                        <label>Diskon (%)</label>
                        <input type="number" name="nilai" class="form-control" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="switch-container" style="border: none; padding: 0; background: transparent;">
                            <label class="switch">
                                <input type="checkbox" name="status" value="Aktif" checked onchange="this.parentElement.nextElementSibling.innerText = this.checked ? 'AKTIF' : 'NONAKTIF';">
                                <span class="slider"></span>
                            </label>
                            <span class="switch-label" style="color: #0081C9; margin-left: 10px;">AKTIF</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group-footer">
                <button type="button" class="btn-action" style="background: #ef4444;" onclick="closeModal('addModalDiskon')">Batal</button>
                <button type="submit" class="btn-action" onclick="setLoading(this)">Simpan Promo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModalDiskon" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0; color: #0081C9;">Edit Promo</h3>
            <button class="close-modal" onclick="closeModal('editModalDiskon')">&times;</button>
        </div>
        <form id="editDiskonForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body-vertical">
                <div class="banner-center-wrapper" id="edit_banner_wrapper">
                    <div class="banner-preview-4x2" onclick="this.nextElementSibling.click()">
                        <div id="edit_banner_preview_content"></div>
                    </div>
                    <input type="file" name="image_banner" style="display: none;" onchange="previewImage(this, 'edit_banner_preview_content')">
                </div>

                <div class="form-group">
                    <label>Tipe</label>
                    <select name="tipe" id="edit_promo_tipe" class="form-control" required onchange="handleCategoryChange(this.value, 'edit_banner_wrapper', 'edit_kode_wrapper', 'edit')">
                        <option value="promo">Promo</option>
                        <option value="Voucer">Voucher</option>
                        <option value="Diskon">Diskon</option>
                    </select>
                </div>

                <div class="form-group" id="edit_kode_wrapper" style="display: none;">
                    <label>Kode Voucher</label>
                    <input type="text" name="kode_promo" id="edit_kode" class="form-control">
                </div>

                <div class="form-group">
                    <label>Nama Promo</label>
                    <input type="text" name="nama_promo" id="edit_nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Pilih Produk</label>
                    <div class="dropdown-checkbox-wrapper">
                        <div class="dropdown-checkbox-btn" onclick="toggleDropdown('edit_product_dropdown', this)">
                            <div class="selected-pills" id="edit_product_pills">
                                <span style="color: #94a3b8;">Pilih Produk...</span>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="arrow-icon"></iconify-icon>
                        </div>
                        <div class="dropdown-checkbox-content" id="edit_product_dropdown">
                            <div class="dropdown-search">
                                <input type="text" placeholder="Cari produk..." onkeyup="filterDropdown(this, 'edit_product_list')">
                            </div>
                            <div class="dropdown-list" id="edit_product_list">
                                @foreach($products as $product)
                                <label class="dropdown-item">
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->uuid }}" onchange="handleDropdownCheck(this, 'product', 'edit')">
                                    <div class="item-info"><span class="item-name">{{ $product->nama_produk }}</span></div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Pilih Cabang</label>
                    <div class="dropdown-checkbox-wrapper">
                        <div class="dropdown-checkbox-btn" onclick="toggleDropdown('edit_store_dropdown', this)">
                            <div class="selected-pills" id="edit_store_pills">
                                <span style="color: #94a3b8;">Pilih Cabang...</span>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="arrow-icon"></iconify-icon>
                        </div>
                        <div class="dropdown-checkbox-content" id="edit_store_dropdown">
                            <div class="dropdown-list" id="edit_store_list">
                                @foreach($outlets as $outlet)
                                <label class="dropdown-item">
                                    <input type="checkbox" name="store_ids[]" value="{{ $outlet->uuid }}" onchange="handleDropdownCheck(this, 'store', 'edit')">
                                    <div class="item-info"><span class="item-name">{{ $outlet->nama }}</span></div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-flex">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="edit_tgl_mulai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="edit_tgl_selesai" class="form-control" required>
                    </div>
                </div>

                <div class="form-row-flex">
                    <div class="form-group">
                        <label>Nilai (%)</label>
                        <input type="number" name="nilai" id="edit_nilai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="switch-container" style="border: none; padding: 0; background: transparent;">
                            <label class="switch">
                                <input type="checkbox" name="status" id="edit_status_toggle" value="Aktif" onchange="this.parentElement.nextElementSibling.innerText = this.checked ? 'AKTIF' : 'NONAKTIF';">
                                <span class="slider"></span>
                            </label>
                            <span class="switch-label" style="color: #0081C9; margin-left: 10px;">AKTIF</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group-footer">
                <button type="button" class="btn-action" style="background: #ef4444;" onclick="closeModal('editModalDiskon')">Batal</button>
                <button type="submit" class="btn-action" onclick="setLoading(this)">Update Promo</button>
            </div>
        </form>
    </div>
</div>

<!-- View Detail Modal -->
<div id="viewModalDiskon" class="modal-overlay" onclick="if(event.target === this) closeModal('viewModalDiskon')">
    <div class="modal-content" style="max-width: 500px; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="flex-shrink: 0;">
            <h3 id="view_title">Rincian Promo</h3>
            <button class="close-modal" onclick="closeModal('viewModalDiskon')">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y: auto; flex: 1; padding-right: 5px;">
            <div id="view_banner_container" style="display: none; margin-bottom: 20px;">
                <div class="banner-preview-4x2" style="max-width: 100%; pointer-events: none; border-style: solid;">
                    <img id="view_banner_img" src="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tipe Promo</div>
                    <div id="view_tipe" style="font-weight: 700; color: #0081C9; font-size: 14px;">-</div>
                </div>
                <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Nilai</div>
                    <div id="view_nilai" style="font-weight: 700; color: #0081C9; font-size: 14px;">-</div>
                </div>
            </div>

            <div class="form-group" id="view_kode_wrapper" style="display: none; margin-bottom: 20px;">
                <label>Kode Promo / Voucher</label>
                <div style="background: #f1f5f9; padding: 10px 15px; border-radius: 10px; font-family: monospace; font-weight: 700; border: 1px dashed #cbd5e1; color: #0081C9;" id="view_kode">-</div>
            </div>

            <div class="form-row-flex" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label>Mulai</label>
                    <div style="background: #fff; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 13px;" id="view_tgl_mulai">-</div>
                </div>
                <div class="form-group">
                    <label>Berakhir</label>
                    <div style="background: #fff; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 13px;" id="view_tgl_selesai">-</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: #f0fdf4; padding: 12px; border-radius: 12px; border: 1px solid #dcfce7; display: flex; align-items: center; gap: 10px;">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" style="font-size: 24px; color: #16a34a;"></iconify-icon>
                    <div>
                        <div style="font-size: 10px; color: #166534;">Produk</div>
                        <div id="view_products_count" style="font-weight: 700; color: #166534;">0</div>
                    </div>
                </div>
                <div style="background: #eff6ff; padding: 12px; border-radius: 12px; border: 1px solid #dbeafe; display: flex; align-items: center; gap: 10px;">
                    <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px; color: #1d4ed8;"></iconify-icon>
                    <div>
                        <div style="font-size: 10px; color: #1e40af;">Outlet</div>
                        <div id="view_stores_count" style="font-weight: 700; color: #1e40af;">0</div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #64748b;">Daftar Produk</label>
                <div id="view_products_list" style="display: flex; flex-wrap: wrap; gap: 5px; max-height: 100px; overflow-y: auto; padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <!-- List items injected by JS -->
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 11px; color: #64748b;">Daftar Cabang</label>
                <div id="view_stores_list" style="display: flex; flex-wrap: wrap; gap: 5px; max-height: 100px; overflow-y: auto; padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <!-- List items injected by JS -->
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <div id="view_status" style="display: inline-block;">-</div>
            </div>
        </div>
        <div class="btn-group-footer" style="padding-top: 15px; display: flex; justify-content: center; border-top: 1px solid #f1f5f9; margin-top: 15px; flex-shrink: 0;">
            <button type="button" class="btn-action" style="padding: 10px 40px; justify-content: center;" onclick="closeModal('viewModalDiskon')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div id="viewModalTransaksi" class="modal-overlay" onclick="if(event.target === this) closeModal('viewModalTransaksi')">
    <div class="modal-content" style="max-width: 600px; padding: 25px; border-radius: 24px;">
        <div class="modal-header" style="margin-bottom: 15px;">
            <h3 style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 18px; color: var(--primary-blue);">
                <iconify-icon icon="solar:document-text-bold-duotone" style="font-size: 24px;"></iconify-icon>
                Detail Transaksi
            </h3>
            <button class="close-modal" onclick="closeModal('viewModalTransaksi')">&times;</button>
        </div>

        <div class="modal-body" style="max-height: 480px; overflow-y: auto; padding-right: 5px;">
            <!-- Order Header Info -->
            <div style="background: #f8fafc; padding: 15px; border-radius: 16px; border: 1.5px solid #e2e8f0; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Kode Order</div>
                    <div id="detail_order_code" style="font-weight: 800; color: var(--primary-blue); font-size: 14px;">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Tanggal</div>
                    <div id="detail_date" style="font-weight: 700; color: #334155; font-size: 13px;">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Pelanggan</div>
                    <div id="detail_customer" style="font-weight: 700; color: #334155; font-size: 13px; text-transform: capitalize;">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Status</div>
                    <div id="detail_status" style="margin-top: 2px;">-</div>
                </div>
            </div>

            <!-- Delivery Info -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 12px; font-weight: 800; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="solar:delivery-bold-duotone" style="font-size: 18px;"></iconify-icon>
                    Informasi Pengiriman
                </h4>
                <div style="background: #fff; padding: 15px; border-radius: 16px; border: 1.5px solid #e2e8f0;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">Nomor Handphone</div>
                    <div id="detail_phone" style="font-weight: 700; color: #334155; font-size: 13px; margin-bottom: 10px;">-</div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">Alamat Pengiriman</div>
                    <div id="detail_address" style="font-size: 12px; color: #475569; font-weight: 500; line-height: 1.4;">-</div>
                </div>
            </div>

            <!-- Product Items List -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 12px; font-weight: 800; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="solar:box-bold-duotone" style="font-size: 18px;"></iconify-icon>
                    Daftar Produk Terbeli
                </h4>
                <div id="detail_items_list" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Item template injected by JS -->
                </div>
            </div>

            <!-- Pricing Summary -->
            <div style="background: #f8fafc; padding: 15px; border-radius: 16px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                    <span>Subtotal Produk</span>
                    <span id="detail_subtotal" style="font-weight: 600; color: #334155;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                    <span>Ongkos Kirim</span>
                    <span id="detail_shipping" style="font-weight: 600; color: #334155;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                    <span>Total Diskon</span>
                    <span id="detail_discount" style="font-weight: 600; color: #ef4444;">-</span>
                </div>
                <div style="height: 1px; background: #e2e8f0; margin: 5px 0;"></div>
                <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 800; color: #334155;">
                    <span>Total Pembayaran</span>
                    <span id="detail_total" style="color: var(--primary-blue);">-</span>
                </div>
            </div>
        </div>

        <div class="btn-group-footer" style="padding-top: 15px; display: flex; justify-content: center; border-top: 1px solid #f1f5f9; margin-top: 15px;">
            <button type="button" class="btn-action" style="padding: 10px 40px; justify-content: center;" onclick="closeModal('viewModalTransaksi')">Tutup</button>
        </div>
    </div>
</div>

<script>
    let currentTab = '{{ request('tab', 'riwayat') }}';

    // Tab-pill & sections sudah dirender di atas script ini — langsung init
    switchTab(currentTab, true);

    @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#0081C9',
            timer: 3500,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Gagal!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    @endif

    function openViewModalTransaksi(btn) {
        const trx = JSON.parse(btn.getAttribute('data-trx'));
        
        document.getElementById('detail_order_code').innerText = '#' + trx.id;
        document.getElementById('detail_date').innerText = trx.tanggal;
        document.getElementById('detail_customer').innerText = trx.pelanggan;
        document.getElementById('detail_phone').innerText = trx.phone || '-';
        document.getElementById('detail_address').innerText = trx.address || '-';
        
        // Status Badge
        let statusClass = 'status-inactive';
        let statusLower = trx.status.toLowerCase();
        if (['settlement', 'capture', 'paid', 'success'].includes(statusLower)) {
            statusClass = 'status-active';
        } else if (['pending'].includes(statusLower)) {
            statusClass = 'stat-proses';
        }
        
        document.getElementById('detail_status').innerHTML = `
            <span class="status-badge ${statusClass}" style="white-space: nowrap; font-size: 11px; padding: 4px 12px;">
                ${trx.status}
            </span>
        `;
        
        // Pricing
        document.getElementById('detail_subtotal').innerText = trx.subtotal;
        document.getElementById('detail_shipping').innerText = trx.shipping;
        document.getElementById('detail_discount').innerText = trx.diskon;
        document.getElementById('detail_total').innerText = trx.total;
        
        // Render Items List
        const itemsList = document.getElementById('detail_items_list');
        itemsList.innerHTML = '';
        
        trx.items_data.forEach(item => {
            const itemRow = document.createElement('div');
            itemRow.style.cssText = 'display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; gap: 12px; transition: all 0.2s;';
            itemRow.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="${item.image}" alt="${item.product_name}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1.5px solid #f1f5f9; background: #f8fafc;">
                    <div style="text-align: left;">
                        <div style="font-weight: 700; font-size: 12.5px; color: #334155; line-height: 1.3;">${item.product_name}</div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                            <span style="font-weight: 600; color: var(--primary-blue);">${item.qty}x</span> ${item.price}
                        </div>
                    </div>
                </div>
                <div style="font-weight: 700; font-size: 13px; color: #334155; white-space: nowrap;">
                    ${item.subtotal}
                </div>
            `;
            itemsList.appendChild(itemRow);
        });
        
        openModal('viewModalTransaksi');
    }

    function switchTab(tabId, isInitial = false) {
        currentTab = tabId;
        
        // Reset pills
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        // Hide all views
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        
        // Show active view
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.classList.add('active');

        // Sync semua hidden input[name=tab] agar filter form tidak reset tab
        document.querySelectorAll('input[name="tab"]').forEach(input => input.value = tabId);

        // Update URL without reload (optional but good for UX)
        if (!isInitial) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }
    }

    function toggleDropdown(id, btn) {
        const content = document.getElementById(id);
        const isOpen = content.classList.contains('show');
        document.querySelectorAll('.dropdown-checkbox-content').forEach(el => el.classList.remove('show'));
        if (!isOpen) { content.classList.add('show'); btn.classList.add('active'); }
    }

    function handleDropdownCheck(checkbox, type, mode) {
        const promoType = document.getElementById(`${mode}_promo_tipe`).value.toLowerCase();
        if (type === 'product' && promoType === 'diskon' && checkbox.checked) {
            checkbox.closest('.dropdown-list').querySelectorAll('input[type="checkbox"]').forEach(cb => { if (cb !== checkbox) cb.checked = false; });
        }
        updatePills(checkbox.closest('.dropdown-checkbox-wrapper'), mode, type);
    }

    function updatePills(wrapper, mode, type) {
        const pillsContainer = wrapper.querySelector('.selected-pills');
        const checked = wrapper.querySelectorAll('input[type="checkbox"]:checked');
        if (checked.length === 0) { pillsContainer.innerHTML = `<span style="color: #94a3b8;">Pilih...</span>`; return; }
        pillsContainer.innerHTML = '';
        checked.forEach(cb => {
            const name = cb.closest('.dropdown-item').querySelector('.item-name').innerText;
            const pill = document.createElement('div'); pill.className = 'selection-pill';
            pill.innerHTML = `${name} <iconify-icon icon="solar:close-circle-bold" class="remove-pill" onclick="event.stopPropagation(); removeSelection('${cb.value}', '${wrapper.querySelector('.dropdown-checkbox-content').id}')"></iconify-icon>`;
            pillsContainer.appendChild(pill);
        });
    }

    function removeSelection(value, dropdownId) {
        const checkbox = document.querySelector(`#${dropdownId} input[value="${value}"]`);
        if (checkbox) { checkbox.checked = false; checkbox.dispatchEvent(new Event('change')); }
    }

    function filterDropdown(input, listId) {
        const filter = input.value.toLowerCase();
        const items = document.getElementById(listId).getElementsByClassName('dropdown-item');
        for (let i = 0; i < items.length; i++) {
            const text = items[i].innerText.toLowerCase();
            items[i].style.display = text.includes(filter) ? 'flex' : 'none';
        }
    }

    function handleCategoryChange(value, bannerWrapperId, kodeWrapperId, mode) {
        const val = value.toLowerCase();
        const bannerWrapper = document.getElementById(bannerWrapperId);
        const imgInput = bannerWrapper.querySelector('input[type="file"]');
        
        bannerWrapper.style.display = val === 'promo' ? 'flex' : 'none';
        document.getElementById(kodeWrapperId).style.display = val === 'voucer' ? 'block' : 'none';
        
        // Toggle required only for Add mode
        if (mode === 'add') {
            if (val === 'promo') {
                imgInput.setAttribute('required', 'required');
            } else {
                imgInput.removeAttribute('required');
            }
        }
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function previewImage(input, targetId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(targetId).innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function filterCategory(category, el) {
        document.querySelectorAll('.sub-tab-pill').forEach(pill => pill.classList.remove('active'));
        el.classList.add('active');
        const rows = document.querySelectorAll('.promo-row');
        rows.forEach(row => {
            const rowCat = row.getAttribute('data-category').toLowerCase();
            row.style.display = (category === 'all' || rowCat === category.toLowerCase()) ? '' : 'none';
        });
    }

    function openViewModalDiskon(el) {
        const data = JSON.parse(el.getAttribute('data-diskon'));
        const products = JSON.parse(el.getAttribute('data-products-list') || '[]');
        const stores = JSON.parse(el.getAttribute('data-stores-list') || '[]');

        document.getElementById('view_title').innerText = data.nama_promo;
        document.getElementById('view_tipe').innerText = (data.tipe === 'promo' ? 'POSTER' : data.tipe.toUpperCase());
        document.getElementById('view_nilai').innerText = parseInt(data.nilai) + '%';
        document.getElementById('view_tgl_mulai').innerText = data.tanggal_mulai ? data.tanggal_mulai.split(' ')[0] : '-';
        document.getElementById('view_tgl_selesai').innerText = data.tanggal_selesai ? data.tanggal_selesai.split(' ')[0] : '-';
        document.getElementById('view_products_count').innerText = products.length;
        document.getElementById('view_stores_count').innerText = stores.length;

        const prodList = document.getElementById('view_products_list');
        prodList.innerHTML = products.map(p => `<span style="background:#fff; border:1px solid #e2e8f0; padding:2px 8px; border-radius:6px; font-size:11px; color:#475569;">${p}</span>`).join('');
        if(products.length === 0) prodList.innerHTML = '<span style="color:#94a3b8; font-size:11px; font-style:italic;">Semua Produk</span>';

        const storeList = document.getElementById('view_stores_list');
        storeList.innerHTML = stores.map(s => `<span style="background:#fff; border:1px solid #e2e8f0; padding:2px 8px; border-radius:6px; font-size:11px; color:#475569;">${s}</span>`).join('');
        if(stores.length === 0) storeList.innerHTML = '<span style="color:#94a3b8; font-size:11px; font-style:italic;">Semua Cabang</span>';
        
        const statusEl = document.getElementById('view_status');
        if (data.status == 1 || data.status === 'Aktif') {
            statusEl.innerHTML = '<span class="status-badge status-active" style="font-size: 10px; padding: 2px 10px;">AKTIF</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive" style="font-size: 10px; padding: 2px 10px;">NONAKTIF</span>';
        }

        const kodeWrapper = document.getElementById('view_kode_wrapper');
        if (data.tipe.toLowerCase() === 'voucer' && data.kode_promo) {
            kodeWrapper.style.display = 'block';
            document.getElementById('view_kode').innerText = data.kode_promo;
        } else {
            kodeWrapper.style.display = 'none';
        }

        const bannerWrapper = document.getElementById('view_banner_container');
        const bannerImg = document.getElementById('view_banner_img');
        if (data.tipe.toLowerCase() === 'promo' && data.image_banner) {
            bannerWrapper.style.display = 'block';
            const imgUrl = data.image_banner.startsWith('http') ? data.image_banner : `/storage/${data.image_banner.replace('/storage/', '')}`;
            bannerImg.src = imgUrl;
        } else {
            bannerWrapper.style.display = 'none';
        }

        openModal('viewModalDiskon');
    }

    function searchPromo() {
        const input = document.getElementById('promoSearch').value.toLowerCase();
        document.querySelectorAll('.promo-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    function filterTable(view) {
        const input = document.getElementById(view + 'Search').value.toLowerCase();
        const rows = document.querySelectorAll('#' + view + 'Table tbody tr');
        rows.forEach(row => {
            if(row.querySelector('td[colspan]')) return;
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    function openEditModalDiskon(el) {
        const data = JSON.parse(el.getAttribute('data-diskon'));
        const productUuids = JSON.parse(el.getAttribute('data-products') || '[]');
        const storeUuids = JSON.parse(el.getAttribute('data-stores') || '[]');

        document.getElementById('editDiskonForm').action = `/transaksi/diskon/${data.uuid}`;
        document.getElementById('edit_nama').value = data.nama_promo;
        document.getElementById('edit_promo_tipe').value = data.tipe;
        document.getElementById('edit_nilai').value = parseInt(data.nilai);
        document.getElementById('edit_kode').value = data.kode_promo || '';
        document.getElementById('edit_tgl_mulai').value = data.tanggal_mulai ? data.tanggal_mulai.split(' ')[0] : '';
        document.getElementById('edit_tgl_selesai').value = data.tanggal_selesai ? data.tanggal_selesai.split(' ')[0] : '';
        document.getElementById('edit_status_toggle').checked = (data.status == 1 || data.status === 'Aktif');
        
        document.querySelectorAll('#edit_product_list input').forEach(cb => cb.checked = false);
        document.querySelectorAll('#edit_store_list input').forEach(cb => cb.checked = false);

        productUuids.forEach(uuid => {
            const cb = document.querySelector(`#edit_product_list input[value="${uuid}"]`);
            if (cb) cb.checked = true;
        });

        storeUuids.forEach(uuid => {
            const cb = document.querySelector(`#edit_store_list input[value="${uuid}"]`);
            if (cb) cb.checked = true;
        });

        updatePills(document.getElementById('edit_product_pills').closest('.dropdown-checkbox-wrapper'), 'edit', 'product');
        updatePills(document.getElementById('edit_store_pills').closest('.dropdown-checkbox-wrapper'), 'edit', 'store');

        const preview = document.getElementById('edit_banner_preview_content');
        if (data.tipe === 'promo' && data.image_banner) {
            const imgUrl = data.image_banner.startsWith('http') ? data.image_banner : `/storage/${data.image_banner.replace('/storage/', '')}`;
            preview.innerHTML = `<img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover;">`;
        } else {
            preview.innerHTML = `<iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 32px; color: #cbd5e1;"></iconify-icon>`;
        }
        handleCategoryChange(data.tipe, 'edit_banner_wrapper', 'edit_kode_wrapper', 'edit');
        openModal('editModalDiskon');
    }

    function openDeleteModalDiskon(uuid) {
        Swal.fire({ 
            title: 'Hapus Promo?', 
            text: "Data promo akan dihapus permanen!", 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonText: 'Ya, Hapus!', 
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#cbd5e1'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Promo...',
                    text: 'Harap tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                const form = document.createElement('form'); form.method = 'POST'; form.action = `/transaksi/diskon/${uuid}`;
                form.innerHTML = `@csrf @method('DELETE')`; document.body.appendChild(form); form.submit();
            }
        });
    }

    function setLoading(btn) {
        const form = btn.closest('form');
        if (!form) return;

        // Reset previous errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        let hasError = false;

        const showError = (element, message) => {
            element.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.innerText = message;
            element.parentNode.appendChild(feedback);
            hasError = true;
        };

        // 1. Validasi Produk
        const productsChecked = form.querySelectorAll('input[name="product_ids[]"]:checked');
        if (productsChecked.length === 0) {
            const productBtn = form.querySelector('input[name="product_ids[]"]').closest('.dropdown-checkbox-wrapper').querySelector('.dropdown-checkbox-btn');
            showError(productBtn, 'Harap pilih minimal satu produk!');
        }

        // 2. Validasi Cabang (Store)
        const storesChecked = form.querySelectorAll('input[name="store_ids[]"]:checked');
        if (storesChecked.length === 0) {
            const storeBtn = form.querySelector('input[name="store_ids[]"]').closest('.dropdown-checkbox-wrapper').querySelector('.dropdown-checkbox-btn');
            showError(storeBtn, 'Harap pilih minimal satu cabang!');
        }

        // 3. Validasi Gambar (Hanya Add & Tipe Promo)
        const isAdd = form.action.includes('diskon') && !form.querySelector('input[name="_method"]');
        const imgInput = form.querySelector('input[name="image_banner"]');
        const promoType = form.querySelector('select[name="tipe"]').value;

        if (isAdd && promoType === 'promo' && (!imgInput || imgInput.files.length === 0)) {
            const bannerPreview = form.querySelector('.banner-preview-4x2');
            showError(bannerPreview, 'Gambar Banner wajib diisi untuk tipe Promo!');
        }

        // 4. Validasi Field Required Lainnya (Nama Promo, dll)
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value) {
                showError(input, 'Bagian ini wajib diisi!');
            }
        });

        if (hasError) {
            form.querySelector('.modal-body-vertical').scrollTop = 0;
            return;
        }

        if (form.checkValidity()) {
            const isUpdate = btn.innerText.toLowerCase().includes('update') || btn.innerText.toLowerCase().includes('perbarui');
            btn.innerHTML = `<iconify-icon icon="eos-icons:loading"></iconify-icon> ${isUpdate ? 'Memperbarui...' : 'Menyimpan...'}`;
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';

            Swal.fire({
                title: isUpdate ? 'Memperbarui Promo...' : 'Menyimpan Promo...',
                text: 'Harap tunggu, data sedang dikirim ke server.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        } else {
            form.reportValidity();
        }
    }

    @if($errors->any() && !old('_method'))
        switchTab('diskon');
        openModal('addModalDiskon');
    @endif
</script>
@endsection
