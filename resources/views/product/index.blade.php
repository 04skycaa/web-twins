@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>



<div class="fitur-container">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="{{ route('products.index') }}" class="tab-pill {{ $active_tab == 'produk' ? 'active' : '' }}">
            <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
            <span>Produk</span>
        </a>
        <a href="{{ route('products.opname') }}" class="tab-pill {{ $active_tab == 'opname' ? 'active' : '' }}">
            <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
            <span>Produk Opname</span>
        </a>
        <a href="{{ route('products.request') }}" class="tab-pill {{ $active_tab == 'request' ? 'active' : '' }}">
            <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
            <span>Request Produk</span>
        </a>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <form action="{{ url()->current() }}" method="GET" style="display: contents;" id="filterForm">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" name="search" class="search-input" 
                        placeholder="{{ $active_tab == 'request' ? 'Cari produk atau status request...' : 'cari data' }}" 
                        value="{{ request('search') }}">
                </div>

                <input type="hidden" name="category_id" id="hiddenCategoryId" value="{{ request('category_id') }}">
                <div class="dropdown">
                    <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:filter-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="left: 0; right: auto;">
                        <a href="javascript:void(0)" onclick="setCategory('')" class="{{ !request('category_id') ? 'active-dropdown-item' : '' }}">
                            Semua Kategori
                        </a>
                        @foreach($categories as $category)
                            <a href="javascript:void(0)" onclick="setCategory('{{ $category->uuid }}')" class="{{ request('category_id') == $category->uuid ? 'active-dropdown-item' : '' }}">
                                {{ $category->nama_category }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($active_tab == 'request')
                    {{-- Status Filter Request --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Status: {{ request('status') ?: 'Semua' }}" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:check-read-bold-duotone" style="font-size: 24px;" class="{{ request('status') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}">Semua Status</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Pending']) }}">Pending</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Diproses']) }}">Diproses</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Dikirim']) }}">Dikirim</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Selesai']) }}">Selesai</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Ditolak']) }}">Ditolak</a>
                        </div>
                    </div>

                    {{-- Priority Filter --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Prioritas: {{ request('prioritas') ?: 'Semua' }}" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:flag-bold-duotone" style="font-size: 24px;" class="{{ request('prioritas') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['prioritas' => '']) }}">Semua Prioritas</a>
                            <a href="{{ request()->fullUrlWithQuery(['prioritas' => 'Tinggi']) }}">Tinggi</a>
                            <a href="{{ request()->fullUrlWithQuery(['prioritas' => 'Sedang']) }}">Sedang</a>
                            <a href="{{ request()->fullUrlWithQuery(['prioritas' => 'Rendah']) }}">Rendah</a>
                        </div>
                    </div>
                @elseif($active_tab == 'opname')
                    {{-- Status Filter Opname --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Status: {{ request('status') ?: 'Semua' }}" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:check-read-bold-duotone" style="font-size: 24px;" class="{{ request('status') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}">Semua Status</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Draft']) }}">Draft</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Proses']) }}">Proses</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Selesai']) }}">Selesai</a>
                        </div>
                    </div>
                @endif
            </div>
        </form>

        <div class="right-actions">
            {{-- EXTRACT DROPDOWN --}}
            <div class="dropdown">
                <button class="btn-action" onclick="toggleDropdown(event)">
                    <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                    <span>Extract</span>
                </button>
                <div class="dropdown-content">
                    <a href="{{ route('products.export.excel', array_merge(request()->query(), ['active_tab' => $active_tab])) }}">
                        <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                        Excel
                    </a>
                    <a href="{{ route('products.export.pdf', array_merge(request()->query(), ['active_tab' => $active_tab])) }}" target="_blank">
                        <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                        PDF
                    </a>
                </div>
            </div>

            @if($active_tab == 'produk')
                <div id="normalActionGroup" style="display: flex; gap: 12px;">
                    <button type="button" class="btn-action btn-danger" onclick="toggleMassDeleteMode(true)">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                        <span>Hapus Produk</span>
                    </button>
                    <button type="button" class="btn-action" onclick="openModal('addModal')">
                        <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                        <span>Tambah Produk</span>
                    </button>
                </div>
                <div id="massDeleteActionGroup" style="display: none; gap: 12px;">
                    <button type="button" class="btn-action" style="background: #999;" onclick="toggleMassDeleteMode(false)">
                        <span>Batal</span>
                    </button>
                    <button type="button" class="btn-action btn-danger" onclick="confirmMassDelete()">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                        <span id="massDeleteBtnText">Hapus Terpilih (0)</span>
                    </button>
                </div>
            @elseif($active_tab == 'opname')
                <button class="btn-action" onclick="openAddOpnameModal()">
                    <iconify-icon icon="solar:clipboard-add-bold-duotone"></iconify-icon>
                    <span>Tambah Opname</span>
                </button>
            @elseif($active_tab == 'request')
                <button class="btn-action" onclick="openAddRequestModal()">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span>Ajukan Request</span>
                </button>
            @endif
        </div>
    </div>

    {{-- MAIN BOX --}}
    <div class="main-content-box">
        <div class="table-container">
            @if($active_tab == 'produk')
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th class="mass-delete-checkbox" style="display: none; width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" style="transform: scale(1.2); cursor: pointer;">
                            </th>
                            <th>PRODUK</th>
                            <th>KATEGORI</th>
                            <th>HARGA MODAL</th>
                            <th>HARGA JUAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="mass-delete-checkbox" style="display: none; text-align: center;">
                                    <input type="checkbox" class="product-checkbox" value="{{ $product->uuid }}" data-nama="{{ $product->nama_produk }}" onchange="updateMassDeleteCount()" style="transform: scale(1.2); cursor: pointer;">
                                </td>
                                <td>
                                    <div class="product-info">
                                        <img src="{{ $product->image_url ?? asset('images/placeholder-product.png') }}" class="product-img">
                                        <div>
                                            <div style="font-weight: 600;">{{ $product->nama_produk }}</div>
                                            <div style="font-size: 12px; color: #888;">{{ $product->barcode ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->category->nama_category ?? '-' }}</td>
                                <td class="price-text">Rp {{ number_format($product->harga_modal, 0, ',', '.') }}</td>
                                <td class="price-text">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($product)' onclick="openViewModal(JSON.parse(this.dataset.item))">
                                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px;" data-item='@json($product)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>
                                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="confirmDelete('{{ $product->uuid }}', '{{ $product->nama_produk }}')">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" id="emptyProdukTd" style="text-align: center; padding: 40px; color: #999;">Belum ada data produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-container">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @elseif($active_tab == 'opname')
                @if(Auth::user()->isOwner())
                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="background: #FFEBEE; color: #C62828; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #888;">🔥 Belum Selesai</div>
                                <div style="font-size: 18px; font-weight: 700;">{{ $unfinished_count ?? 0 }}</div>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="background: #FFF3E0; color: #E65100; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <iconify-icon icon="solar:graph-down-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #888;">📉 Selisih Stok Hari Ini</div>
                                <div style="font-size: 18px; font-weight: 700;" class="{{ ($total_selisih_today ?? 0) != 0 ? 'bg-selisih' : '' }}">
                                    {{ ($total_selisih_today ?? 0) > 0 ? '+' : '' }}{{ $total_selisih_today ?? 0 }}
                                </div>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="background: #E8F5E9; color: #2E7D32; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #888;">✅ Selesai Hari Ini</div>
                                <div style="font-size: 18px; font-weight: 700;">{{ $completed_today_count ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @elseif(($unfinished_count ?? 0) > 0)
                    <div style="background: #FFF9C4; color: #827717; padding: 10px 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; border-left: 4px solid #FBC02D;">
                        <span>🔥 {{ $unfinished_count }} opname belum selesai hari ini</span>
                    </div>
                @endif
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>OUTLET / STORE</th>
                            <th>PETUGAS</th>
                            <th>SUMMARY</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opnames as $opname)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($opname->tanggal)->format('d F Y') }}</td>
                                <td>{{ $opname->store->nama ?? '-' }}</td>
                                <td><strong>{{ $opname->user->name ?? $opname->user->username ?? '-' }}</strong></td>
                                <td>
                                    <div style="font-weight: 600;">{{ $opname->total_items }} item</div>
                                    <div style="font-size: 12px;" class="{{ $opname->total_selisih != 0 ? 'bg-selisih' : '' }}">
                                        {{ $opname->total_selisih > 0 ? '+' : '' }}{{ $opname->total_selisih }}
                                        @if($opname->total_selisih != 0)
                                            <iconify-icon icon="solar:danger-bold" style="font-size: 14px; vertical-align: middle;"></iconify-icon>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php $lowStatus = strtolower($opname->status); @endphp
                                    <span class="status-badge stat-{{ $lowStatus }}">
                                        {{ $opname->status }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" 
                                            data-uuid="{{ $opname->uuid }}" onclick="openOpnameDetailModal(this.dataset.uuid)" title="Lihat Detail">
                                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                        @if($opname->status != 'Selesai')
                                            <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" 
                                                data-uuid="{{ $opname->uuid }}" onclick="continueOpname(this.dataset.uuid)" title="{{ $opname->status == 'Draft' ? 'Edit Opname' : 'Lanjutkan Opname' }}">
                                                <iconify-icon icon="{{ $opname->status == 'Draft' ? 'solar:pen-new-square-bold-duotone' : 'solar:play-bold-duotone' }}"></iconify-icon>
                                            </button>
                                        @endif
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #999; border: none; background: transparent;" 
                                            data-uuid="{{ $opname->uuid }}" data-date="{{ \Carbon\Carbon::parse($opname->tanggal)->format('d F Y') }}"
                                            onclick="confirmDeleteOpname(this.dataset.uuid, this.dataset.date)" title="Hapus">
                                            <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #999;">Belum ada riwayat opname.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-container">
                    {{ $opnames->links() }}
                </div>
            @elseif($active_tab == 'request')
                @if(Auth::user()->isOwner())
                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="background: #E3F2FD; color: #1976D2; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #888;">📦 Request Pending</div>
                                <div style="font-size: 18px; font-weight: 700;">{{ $pending_requests_count ?? 0 }}</div>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="background: #FFEBEE; color: #C62828; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #888;">⚠️ Prioritas Tinggi</div>
                                <div style="font-size: 18px; font-weight: 700;">{{ $high_priority_count ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">PRODUK</th>
                            <th>PEMOHON</th>
                            @if(Auth::user()->isOwner())
                                <th>OUTLET</th>
                            @endif
                            <th>JUMLAH</th>
                            <th>PRIORITAS</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr class="{{ $req->prioritas == 'Tinggi' ? 'row-high-prio' : '' }} {{ $req->status == 'Dikirim' && !Auth::user()->isOwner() ? 'row-shipped' : '' }}">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        @if($req->prioritas == 'Tinggi')
                                            <iconify-icon icon="solar:danger-bold" style="color: #C62828; font-size: 18px;" title="Prioritas Tinggi"></iconify-icon>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600;">{{ $req->product->nama_produk ?? 'Produk Terhapus' }}</div>
                                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 2px;">
                                                <span style="font-size: 11px; color: #888;">{{ $req->product->barcode ?? '-' }}</span>
                                                @php
                                                    $localStock = $req->product->stores->where('store_id', $req->store_id)->first()->stok ?? 0;
                                                @endphp
                                                <span style="background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600;" title="Stok saat ini di outlet Anda">
                                                    Stok Anda: {{ $localStock }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><strong>{{ $req->pemohon }}</strong></td>
                                @if(Auth::user()->isOwner())
                                    <td style="font-size: 12px; color: #666;">{{ $req->store->nama ?? '-' }}</td>
                                @endif
                                <td style="font-weight: 700; font-size: 15px;">{{ $req->jumlah_minta }}</td>
                                <td>
                                    @php $prioClass = 'prio-' . strtolower($req->prioritas); @endphp
                                    <span class="status-badge {{ $prioClass }}">
                                        @if($req->prioritas == 'Tinggi')
                                            <iconify-icon icon="solar:danger-bold" style="margin-right: 4px; font-size: 14px; vertical-align: middle;"></iconify-icon>
                                        @endif
                                        {{ $req->prioritas }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $lowStatus = strtolower($req->status);
                                        $icon = [
                                            'pending' => 'solar:clock-circle-bold-duotone',
                                            'diproses' => 'solar:settings-bold-duotone',
                                            'dikirim' => 'solar:routing-2-bold-duotone',
                                            'selesai' => 'solar:check-circle-bold-duotone',
                                            'ditolak' => 'solar:close-circle-bold-duotone'
                                        ][$lowStatus] ?? 'solar:clock-circle-bold-duotone';
                                    @endphp
                                    <span class="status-badge stat-{{ $lowStatus }}" style="gap: 4px;">
                                        <iconify-icon icon="{{ $icon }}" style="font-size: 14px;"></iconify-icon>
                                        {{ $req->status }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        @if(Auth::user()->isOwner())
                                            <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($req)' onclick="openRequestDetailModal(JSON.parse(this.dataset.item))" title="Lihat Detail">
                                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                            </button>
                                            @if($req->status == 'Pending')
                                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #2E7D32; border-color: #E8F5E9;" onclick="confirmRequestAction('{{ $req->uuid }}', 'approve')" title="Setujui">
                                                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                                </button>
                                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #FFEBEE;" onclick="confirmRequestAction('{{ $req->uuid }}', 'reject')" title="Tolak">
                                                    <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                                                </button>
                                            @elseif($req->status == 'Diproses')
                                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #1976D2; border-color: #E3F2FD;" onclick="confirmShipRequest('{{ $req->uuid }}')" title="Kirim Barang">
                                                    <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
                                                </button>
                                            @endif
                                        @else
                                            @if($req->status == 'Pending')
                                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item='@json($req)' onclick="openEditRequestModal(JSON.parse(this.dataset.item))" title="Ubah Request">
                                                    <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                                                </button>
                                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #FFEBEE;" onclick="confirmCancelRequest('{{ $req->uuid }}')" title="Batalkan Request">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                                </button>
                                            @elseif($req->status == 'Dikirim')
                                                <button type="button" class="btn-action" style="padding: 6px 12px; font-size: 12px; background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9;" onclick="confirmReceiveRequest('{{ $req->uuid }}')">
                                                    📦 Terima Barang
                                                </button>
                                            @else
                                                <span style="font-size: 10px; color: #999; font-style: italic;">No Action Required</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isOwner() ? 7 : 6 }}" style="text-align: center; padding: 40px; color: #999;">Belum ada data request produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-container">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODALS --}}

<!-- Tambah Produk -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Produk Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="text-align: center;">
                <label style="display: block; text-align: left;">Foto Produk (Opsional)</label>
                <input type="file" id="productImageInput" accept="image/*" style="display: none;">
                <input type="hidden" name="cropped_image" id="croppedImageResult">
                <div id="imagePreviewContainer" style="position: relative; width: 150px; height: 150px; border: 2px dashed #ddd; border-radius: 12px; margin: 0 auto; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden;" onclick="document.getElementById('productImageInput').click()">
                    <span style="color: #999; font-size: 12px;">+ Pilih/Foto</span>
                    <button type="button" id="smartScanBtn" onclick="event.stopPropagation(); scanFromProductImage();" style="display: none; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); background: #2E7D32; color: white; border: none; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.2); align-items: center; gap: 4px; z-index: 10;">
                        <iconify-icon icon="solar:camera-shine-bold-duotone"></iconify-icon> Pindai Barcode
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" id="addNamaProduk" class="form-control" required placeholder="Contoh: Coca Cola">
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="barcode" id="addBarcode" class="form-control" placeholder="Scan atau ketik barcode..." style="flex: 1;">
                    <button type="button" class="btn-action" onclick="openScannerModal()" style="padding: 0 16px; background: #333;">
                        <iconify-icon icon="solar:camera-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori_id" class="form-control" required>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->uuid }}">{{ $category->nama_category }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Harga Modal</label>
                    <input type="number" name="harga_modal" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <input type="number" name="harga_jual" class="form-control" value="0">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Scanner Barcode -->
<div id="scannerModal" class="modal-overlay" style="z-index: 1050;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Scan Barcode</h3>
            <button class="close-modal" onclick="closeScannerModal()">&times;</button>
        </div>
        <div id="reader" style="width: 100%; min-height: 250px; background: #000; border-radius: 12px; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center;">
            <span id="camera-placeholder" style="color: #666; font-size: 13px;">Pratinjau Kamera</span>
            <div class="scanner-line" id="scannerLine"></div>
            <div class="scanner-target" id="scannerTarget"></div>
            <div class="zoom-badge" id="zoomBadge">DIGITAL ZOOM 2X</div>
            <div id="deepScanIndicator" style="position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 10px; display: none; z-index: 110; align-items: center; gap: 5px;">
                <div class="spinner-border spinner-border-sm" role="status" style="width: 12px; height: 12px; border-width: 2px;"></div>
                Smart Analyzing...
            </div>
        </div>
        
        <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-action" id="zoomBtn" style="flex: 1; justify-content: center; background: #555;" onclick="toggleDigitalZoom()">
                    <iconify-icon icon="solar:magnifer-zoom-in-bold-duotone"></iconify-icon> Zoom 2X
                </button>
                <button type="button" class="btn-action" style="flex: 1; justify-content: center; background: #2E7D32;" onclick="captureHighResAndScan()">
                    <iconify-icon icon="solar:camera-square-bold-duotone"></iconify-icon> Ambil Foto
                </button>
            </div>
            <button type="button" class="btn-action" style="justify-content: center; background: #007BFF;" onclick="startCameraScan()">
                <iconify-icon icon="solar:videocamera-bold-duotone"></iconify-icon> Gunakan Kamera Live
            </button>
            <div style="display: flex; align-items: center; gap: 10px;">
                <hr style="flex: 1; border: 0; border-top: 1px solid #ddd;">
                <span style="font-size: 12px; color: #888;">ATAU</span>
                <hr style="flex: 1; border: 0; border-top: 1px solid #ddd;">
            </div>
            <button type="button" class="btn-action" style="justify-content: center; background: #FFB300; color: #333;" onclick="document.getElementById('scanGalleryInput').click()">
                <iconify-icon icon="solar:gallery-bold-duotone"></iconify-icon> Upload dari Galeri
            </button>
            <input type="file" id="scanGalleryInput" accept="image/*" style="display: none;" onchange="scanImageFile(event)">
        </div>
    </div>
</div>

<!-- Modal Cropper Foto -->
<div id="cropperModal" class="modal-overlay" style="z-index: 1050;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Sesuaikan Foto (1:1)</h3>
            <button class="close-modal" onclick="closeCropperModal()">&times;</button>
        </div>
        <div style="width: 100%; height: 300px; background: #eee; border-radius: 12px; overflow: hidden;">
            <img id="cropperImage" src="" style="max-width: 100%;">
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="button" class="btn-action btn-danger" style="flex: 1;" onclick="closeCropperModal()">Batal</button>
            <button type="button" class="btn-action" style="flex: 1; justify-content: center; background: #2E7D32;" onclick="applyCrop()">Terapkan Foto</button>
        </div>
    </div>
</div>

<!-- Edit Produk -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Produk</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" name="barcode" id="edit_barcode" class="form-control">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori_id" id="edit_kategori" class="form-control" required>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->uuid }}">{{ $category->nama_category }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Harga Modal</label>
                    <input type="number" name="harga_modal" id="edit_modal" class="form-control">
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <input type="number" name="harga_jual" id="edit_jual" class="form-control">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- View Detail Produk -->
<div id="viewModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Rincian Produk</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div id="viewDetailContent">
            {{-- Content injected via JS --}}
        </div>
        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Tambah Opname Modal -->
<div id="addOpnameModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Input Opname Stok</h3>
            <button class="close-modal" onclick="closeModal('addOpnameModal')">&times;</button>
        </div>
        <form action="{{ route('products.opname.store') }}" method="POST" id="opnameForm">
            @csrf
            <div id="opnameMethod"></div>
            <div class="form-group">
                <label>Pilih Toko / Outlet</label>
                <select name="store_id" class="form-control" required @if(!Auth::user()->isOwner()) style="background-color: #f8f9fa; pointer-events: none;" @endif>
                    @if(Auth::user()->isOwner())
                        <option value="">-- Pilih Toko --</option>
                    @endif
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store->uuid }}" selected>{{ $store->nama }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0;">Item Produk</h4>
                    <button type="button" class="btn-action" style="padding: 6px 12px; font-size: 12px;" onclick="addOpnameRow()">
                        <iconify-icon icon="solar:add-circle-bold-duotone" style="margin-right: 4px;"></iconify-icon> Tambah Baris
                    </button>
                </div>
                <div style="overflow-x: auto; max-height: 300px;">
                    <table class="fitur-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Produk</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Ket.</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody id="opnameItemsTable">
                            {{-- Rows will be added here --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1;" onclick="closeModal('addOpnameModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan Opname</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Opname Modal -->
<div id="opnameDetailModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Rincian Opname Stok</h3>
            <button class="close-modal" onclick="closeModal('opnameDetailModal')">&times;</button>
        </div>
        <div style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
            <div>
                <div style="color: #888;">Tanggal:</div>
                <div id="det_op_tanggal" style="font-weight: 600;">-</div>
            </div>
            <div>
                <div style="color: #888;">Toko:</div>
                <div id="det_op_toko" style="font-weight: 600;">-</div>
            </div>
            <div>
                <div style="color: #888;">Petugas:</div>
                <div id="det_op_petugas" style="font-weight: 600;">-</div>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="fitur-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Sistem</th>
                        <th>Fisik</th>
                        <th>Selisih</th>
                    </tr>
                </thead>
                <tbody id="opnameDetailRows">
                    {{-- Rows will be injected here --}}
                </tbody>
            </table>
        </div>
        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('opnameDetailModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Tambah Request Modal -->
<div id="addRequestModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajukan Request Produk</h3>
            <button class="close-modal" onclick="closeModal('addRequestModal')">&times;</button>
        </div>
        <form action="{{ route('products.request.store') }}" method="POST" id="requestForm">
            @csrf
            <div id="requestMethod"></div>

            @if(Auth::user()->isOwner())
                <div class="form-group">
                    <label>Pilih Cabang (Khusus Owner)</label>
                    <select name="store_id" class="form-control" required style="border-color: #007BFF; background-color: #f8fbff;">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($all_stores ?? [] as $store)
                            <option value="{{ $store->uuid }}">{{ $store->nama }} ({{ $store->lokasi }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label>Pilih Produk dari Semua Katalog</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Cari atau Pilih Produk --</option>
                    @foreach($all_products ?? [] as $product)
                        <option value="{{ $product->uuid }}">{{ $product->nama_produk }} ({{ $product->barcode ?? 'Tanpa Barcode' }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Jumlah Permintaan</label>
                    <input type="number" name="jumlah_minta" class="form-control" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Prioritas</label>
                    <select name="prioritas" class="form-control" required>
                        <option value="Sedang">Sedang</option>
                        <option value="Tinggi" style="color: #D9534F;">Tinggi</option>
                        <option value="Rendah">Rendah</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Alasan Permintaan</label>
                <textarea name="alasan_permintaan" class="form-control" rows="3" placeholder="Contoh: Stok menipis, persiapan promo, dll."></textarea>
            </div>
            
            <div style="margin-top: 24px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1;" onclick="closeModal('addRequestModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Request Modal -->
<div id="requestDetailModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Rincian Request Produk</h3>
            <button class="close-modal" onclick="closeModal('requestDetailModal')">&times;</button>
        </div>
        <div style="margin-bottom: 20px; display: grid; gap: 15px; font-size: 14px;">
            <div style="display: grid; grid-template-columns: 100px 1fr; gap: 10px;">
                <div style="color: #888;">Produk:</div>
                <div id="det_req_produk" style="font-weight: 600;">-</div>
                
                <div style="color: #888;">Pemohon:</div>
                <div id="det_req_pemohon" style="font-weight: 600;">-</div>

                <div style="color: #888;">Outlet:</div>
                <div id="det_req_outlet" style="font-weight: 600;">-</div>

                <div style="color: #888;">Jumlah:</div>
                <div id="det_req_jumlah" style="font-weight: 600; font-size: 16px;">-</div>

                <div style="color: #888;">Prioritas:</div>
                <div id="det_req_prioritas">-</div>

                <div style="color: #888;">Status:</div>
                <div id="det_req_status">-</div>
            </div>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 5px 0;">
            <div>
                <div style="color: #888; margin-bottom: 4px;">Alasan Permintaan:</div>
                <div id="det_req_alasan" style="background: #f8f9fa; padding: 12px; border-radius: 8px; font-style: italic; line-height: 1.5;">-</div>
            </div>
        </div>
        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('requestDetailModal')">Tutup</button>
        </div>
    </div>
</div>

<script>
    // --- Global Helpers ---
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // --- Dropdown Logic ---
    function toggleDropdown(event) {
        const dropdown = event.currentTarget.closest('.dropdown');
        const content = dropdown.querySelector('.dropdown-content');
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => {
            if (d !== content) d.classList.remove('show');
        });
        
        content.classList.toggle('show');
    }

    // --- Mass Delete Logic ---
    let isMassDeleteMode = false;
    function toggleMassDeleteMode(active) {
        isMassDeleteMode = active;
        document.getElementById('normalActionGroup').style.display = active ? 'none' : 'flex';
        document.getElementById('massDeleteActionGroup').style.display = active ? 'flex' : 'none';
        document.querySelectorAll('.mass-delete-checkbox').forEach(cb => cb.style.display = active ? 'table-cell' : 'none');
        if (!active) {
            document.getElementById('selectAllCheckbox').checked = false;
            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
            updateMassDeleteCount();
        }
    }
    function toggleSelectAll() {
        const check = document.getElementById('selectAllCheckbox').checked;
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = check);
        updateMassDeleteCount();
    }
    function updateMassDeleteCount() {
        const count = document.querySelectorAll('.product-checkbox:checked').length;
        document.getElementById('massDeleteBtnText').innerText = `Konfirmasi Hapus (${count})`;
    }
    function confirmMassDelete() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        if (selected.length === 0) return Swal.fire('Pilih Produk', 'Centang produk yang ingin dihapus.', 'warning');
        Swal.fire({
            title: `Hapus ${selected.length} Produk?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Hapus!'
        }).then(r => {
            if (r.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST'; 
                form.action = '{{ route("products.mass_destroy") }}';
                form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                selected.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                    form.appendChild(input);
                });
                document.body.appendChild(form); form.submit();
            }
        });
    }

    // --- Filter logic ---
    function setCategory(id) {
        document.getElementById('hiddenCategoryId').value = id;
        document.getElementById('filterForm').submit();
    }

    // --- Product Actions ---
    function openViewModal(product) {
        const content = document.getElementById('viewDetailContent');
        const priceModal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.harga_modal);
        const priceJual = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.harga_jual);
        
        content.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; gap: 20px; align-items: start;">
                    <img src="${product.image_url || '/images/placeholder-product.png'}" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; background: #f0f0f0;">
                    <div style="flex: 1;">
                        <div style="font-size: 14px; color: #888; margin-bottom: 4px;">Nama Produk</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--primary-blue);">${product.nama_produk}</div>
                        <div style="font-size: 14px; color: #555; margin-top: 8px;">${product.barcode || 'Tidak ada barcode'}</div>
                    </div>
                </div>
                <hr style="border: none; border-top: 1px solid #eee;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: #888; text-transform: uppercase;">Kategori</div>
                        <div style="font-weight: 600;">${product.category ? product.category.nama_category : '-'}</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: #888; text-transform: uppercase;">Modal</div>
                        <div style="font-weight: 700; color: #D9534F;">${priceModal}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #888; text-transform: uppercase;">Jual</div>
                        <div style="font-weight: 700; color: var(--primary-blue);">${priceJual}</div>
                    </div>
                </div>
            </div>
        `;
        openModal('viewModal');
    }

    function openEditModal(product) {
        const form = document.getElementById('editForm');
        form.action = `/products/${product.uuid}`;
        document.getElementById('edit_nama').value = product.nama_produk;
        document.getElementById('edit_barcode').value = product.barcode;
        document.getElementById('edit_kategori').value = product.kategori_id;
        document.getElementById('edit_modal').value = product.harga_modal;
        document.getElementById('edit_jual').value = product.harga_jual;
        openModal('editModal');
    }

    function confirmDelete(uuid, name) {
        Swal.fire({
            title: 'Hapus Produk?', text: `Yakin hapus ${name}?`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#D9534F', confirmButtonText: 'Ya, Hapus!'
        }).then(r => {
            if (r.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST'; form.action = `/products/${uuid}`;
                form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form); form.submit();
            }
        });
    }

    // --- Custom Scanner & Unified Brain ---
    let html5Qrcode = null;
    let deepScanTimer = null;
    let cyclicIndex = 0;
    let scanStartTime = 0;
    const scanPasses = [
        { msg: "Menyeimbangkan cahaya...", t: 140, a: 0, s: true },
        { msg: "Menajamkan garis...", t: 170, a: 0, s: true },
        { msg: "Mengoreksi lengkungan...", t: 150, a: 5, s: false },
        { msg: "Mengoreksi lengkungan...", t: 150, a: -5, s: false },
        { msg: "Mendeteksi barcode...", t: 190, a: 0, s: true }
    ];

    function openScannerModal() {
        document.getElementById('scannerModal').style.display = 'flex';
        if (!html5Qrcode) html5Qrcode = new Html5Qrcode("reader");
    }

    let isDigitalZoom = false;
    function toggleDigitalZoom() {
        isDigitalZoom = !isDigitalZoom;
        const btn = document.getElementById('zoomBtn');
        const badge = document.getElementById('zoomBadge');
        btn.style.background = isDigitalZoom ? '#2E7D32' : '#555';
        btn.innerHTML = isDigitalZoom ? '<iconify-icon icon="solar:magnifer-zoom-out-bold-duotone"></iconify-icon> Zoom 1X' : '<iconify-icon icon="solar:magnifer-zoom-in-bold-duotone"></iconify-icon> Zoom 2X';
        badge.style.display = isDigitalZoom ? 'block' : 'none';
    }

    const processImage = (file, filter = 'none', cropCenter = false, threshold = 0, angle = 0, stripCrop = false) => {
        return new Promise((r) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let w = img.width, h = img.height, sx = 0, sy = 0, sw = w, sh = h;
                if (cropCenter) { sx = w*0.15; sy = h*0.15; sw = w*0.7; sh = h*0.7; }
                else if (stripCrop) { sy = h*0.35; sh = h*0.3; }
                const MAX = 1000; const scale = Math.min(1, MAX/Math.max(sw, sh));
                const dw = sw*scale, dh = sh*scale, p = 150;
                canvas.width = dw+p*2; canvas.height = dh+p*2;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = 'white'; ctx.fillRect(0,0,canvas.width,canvas.height);
                ctx.save(); ctx.translate(canvas.width/2, canvas.height/2);
                if (angle) ctx.rotate(angle * Math.PI/180);
                if (filter !== 'none') ctx.filter = filter;
                ctx.drawImage(img, sx, sy, sw, sh, -dw/2, -dh/2, dw, dh); ctx.restore();
                if (threshold > 0) {
                    const idata = ctx.getImageData(0,0,canvas.width,canvas.height); const data = idata.data;
                    for (let i=0; i<data.length; i+=4) {
                        const gray = (data[i]+data[i+1]+data[i+2])/3;
                        const v = gray > threshold ? 255 : 0; data[i]=data[i+1]=data[i+2]=v;
                    }
                    ctx.putImageData(idata, 0, 0);
                }
                canvas.toBlob(r, 'image/png');
            };
            img.src = (file instanceof Blob) ? URL.createObjectURL(file) : file;
        });
    };

    function quaggaScan(blob) {
        return new Promise((r, j) => {
            const reader = new FileReader();
            reader.onload = () => {
                Quagga.decodeSingle({
                    src: reader.result, numOfWorkers: 0,
                    decoder: { readers: ["ean_reader", "upc_reader", "upc_e_reader", "code_128_reader"] },
                    locate: true
                }, res => (res && res.codeResult) ? r(res.codeResult.code) : j());
            };
            reader.readAsDataURL(blob);
        });
    }

    async function tryScannerBrain(blob, isExperimental = false) {
        const F = window.Html5QrcodeSupportedFormats || (window.Html5Qrcode ? Html5Qrcode.SupportedFormats : null);
        const config = isExperimental ? true : (F ? { formatsToSupport: [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128] } : {});
        try { const res = await html5Qrcode.scanFile(blob, config); if (res) return res; } catch(e) {}
        try { return await quaggaScan(blob); } catch(e) { return null; }
    }

    function startCameraScan() {
        document.getElementById('camera-placeholder').style.display = 'none';
        document.getElementById('scannerLine').style.display = 'block';
        document.getElementById('scannerTarget').style.display = 'block';
        scanStartTime = Date.now();
        Quagga.init({
            inputStream: { name: "Live", type: "LiveStream", target: document.querySelector('#reader'), constraints: { facingMode: "environment", width: 1280, height: 720 } },
            decoder: { readers: ["ean_reader", "upc_reader", "upc_e_reader", "code_128_reader"] },
            locate: true
        }, err => {
            if (err) return Swal.fire('Error', 'Gagal kamera.', 'error');
            Quagga.start(); deepScanTimer = setInterval(captureAndScanFrame, 800);
        });
        Quagga.onDetected(res => res && res.codeResult && handleSuccessfulDetection(res.codeResult.code));
    }

    async function captureAndScanFrame() {
        const video = document.querySelector('#reader video');
        if (!video || !video.videoWidth) return;
        const pass = scanPasses[cyclicIndex % scanPasses.length];
        const indicator = document.getElementById('deepScanIndicator');
        indicator.innerHTML = `<div class="spinner-border spinner-border-sm"></div> ${pass.msg}`;
        indicator.style.display = 'flex';
        try {
            const canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            if (isDigitalZoom) { ctx.drawImage(video, video.videoWidth/4, video.videoHeight/4, video.videoWidth/2, video.videoHeight/2, 0, 0, video.videoWidth, video.videoHeight); }
            else ctx.drawImage(video, 0, 0);
            const blob = await new Promise(r => canvas.toBlob(r, 'image/png'));
            const res = await tryScannerBrain(await processImage(blob, 'none', false, pass.t, pass.a, pass.s));
            if (res) handleSuccessfulDetection(res);
            cyclicIndex++;
        } catch(e) {}
    }

    async function captureHighResAndScan() {
        const video = document.querySelector('#reader video');
        if (!video) return Swal.fire('Info', 'Aktifkan kamera.', 'info');
        Swal.fire({ title: 'Analisis Mendalam...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0,0);
        const blob = await new Promise(r => canvas.toBlob(r, 'image/png'));
        for (let t of [130, 160, 200]) {
            for (let a of [0, -5, 5]) {
                const res = await tryScannerBrain(await processImage(blob, 'none', false, t, a, true));
                if (res) { Swal.close(); handleSuccessfulDetection(res); return; }
            }
        }
        Swal.fire({ title: 'Belum Terbaca', text: 'Ketik manual:', input: 'text' }).then(r => r.value && onScanSuccess(r.value));
    }

    function handleSuccessfulDetection(code) {
        if (deepScanTimer) clearInterval(deepScanTimer);
        document.getElementById('deepScanIndicator').style.display = 'none';
        try { Quagga.stop(); } catch(e) {}
        onScanSuccess(code);
    }

    async function scanImageFile(event) {
        const file = event.target.files[0]; if (!file) return;
        Swal.fire({ title: 'Scanner Brain: Analyzing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        if (!html5Qrcode) html5Qrcode = new Html5Qrcode("reader");
        try {
            let res = await tryScannerBrain(await processImage(file)); 
            if (!res) res = await tryScannerBrain(await processImage(file, 'none', true, 150));
            if (!res) res = await tryScannerBrain(await processImage(file, 'contrast(2) grayscale(1)', true));
            if (res) { onScanSuccess(res); return; }
            throw 0;
        } catch(e) { Swal.fire({ title: 'Gagal', text: 'Barcode sulit dibaca. Ketik manual:', input: 'text' }).then(r => r.value && onScanSuccess(r.value)); }
        event.target.value = '';
    }

    function closeScannerModal() {
        document.getElementById('scannerModal').style.display = 'none';
        if (deepScanTimer) clearInterval(deepScanTimer);
        document.getElementById('deepScanIndicator').style.display = 'none';
        try { Quagga.stop(); } catch(e) {}
        if (html5Qrcode && html5Qrcode.isScanning) html5Qrcode.stop();
        document.getElementById('camera-placeholder').style.display = 'block';
    }

    function onScanSuccess(text) {
        closeScannerModal();
        document.getElementById('addBarcode').value = text;
        Swal.fire({ title: 'Mencari Data...', html: `Barcode: <b>${text}</b>`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(`https://world.openfoodfacts.org/api/v0/product/${text}.json`)
            .then(r => r.json()).then(d => {
                if (d.status === 1 && d.product) {
                    const p = d.product;
                    const fullName = `${p.brands || ''} ${p.product_name || ''} ${p.quantity || ''}`.trim().replace(/\s+/g, ' ');
                    document.getElementById('addNamaProduk').value = fullName;
                    Swal.fire({ title: 'Ditemukan!', html: `<b>${fullName}</b>`, icon: 'success' });
                } else Swal.fire({ title: 'Disalin!', text: 'Data tidak ditemukan di database global.', icon: 'success' });
            }).catch(() => Swal.fire({ title: 'Berhasil!', text: 'Barcode berhasil terbaca.', icon: 'success' }));
    }

    // --- Cropper Logic ---
    let cropper = null;
    const imgInput = document.getElementById('productImageInput');
    const cropImg = document.getElementById('cropperImage');
    imgInput.addEventListener('change', e => {
        const f = e.target.files[0]; if (!f) return;
        const reader = new FileReader();
        reader.onload = ev => {
            cropImg.src = ev.target.result; document.getElementById('cropperModal').style.display = 'flex';
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImg, { aspectRatio: 1, viewMode: 1 });
        };
        reader.readAsDataURL(f);
    });
    function closeCropperModal() { document.getElementById('cropperModal').style.display='none'; if (cropper) cropper.destroy(); }
    function applyCrop() {
        const canvas = cropper.getCroppedCanvas({ width: 500, height: 500 });
        const b64 = canvas.toDataURL('image/png');
        document.getElementById('croppedImageResult').value = b64;
        document.getElementById('imagePreviewContainer').innerHTML = `<img src="${b64}" style="width:100%; height:100%; object-fit:cover;">`;
        document.getElementById('smartScanBtn').style.display = 'flex';
        document.getElementById('imagePreviewContainer').appendChild(document.getElementById('smartScanBtn'));
        closeCropperModal();
    }
    async function scanFromProductImage() {
        const b64 = document.getElementById('croppedImageResult').value;
        if (!b64) return;
        Swal.fire({ title: 'Analyzing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        if (!html5Qrcode) html5Qrcode = new Html5Qrcode("reader");
        try {
            const blob = await (await fetch(b64)).blob();
            const res = await tryScannerBrain(await processImage(blob));
            if (res) onScanSuccess(res); else throw 0;
        } catch(e) { Swal.fire('Gagal', 'Barcode tidak terdeteksi.', 'warning'); }
    }

    // --- Opname Helpers ---
    let productsList = JSON.parse('@json($products ?? [])');
    function openAddOpnameModal() {
        const form = document.getElementById('opnameForm');
        form.action = "{{ route('products.opname.store') }}";
        document.getElementById('opnameMethod').innerHTML = '';
        document.querySelector('#addOpnameModal h3').innerText = 'Input Opname Stok';
        document.getElementById('opnameItemsTable').innerHTML = '';
        addOpnameRow(); openModal('addOpnameModal');
    }
    function addOpnameRow() {
        const tbody = document.getElementById('opnameItemsTable');
        const i = tbody.children.length; const row = document.createElement('tr');
        let opts = '<option value="">-- Pilih Produk --</option>';
        productsList.forEach(p => opts += `<option value="${p.uuid}">${p.nama_produk}</option>`);
        row.innerHTML = `<td><select name="items[${i}][product_id]" class="form-control" required>${opts}</select></td>
            <td><input type="number" name="items[${i}][stok_sistem]" class="form-control" value="0"></td>
            <td><input type="number" name="items[${i}][stok_fisik]" class="form-control" value="0"></td>
            <td><input type="text" name="items[${i}][keterangan]" class="form-control"></td>
            <td><button type="button" class="btn-filter" onclick="this.closest('tr').remove()"><iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon></button></td>`;
        tbody.appendChild(row);
    }
    function continueOpname(uuid) {
        fetch(`/products/${uuid}`).then(r => r.json()).then(d => {
            const form = document.getElementById('opnameForm');
            form.action = `/products/opname/${uuid}`;
            document.getElementById('opnameMethod').innerHTML = '@method("PUT")';
            document.querySelector('#addOpnameModal h3').innerText = 'Lanjutkan Opname';
            const tbody = document.getElementById('opnameItemsTable'); tbody.innerHTML = '';
            d.details.forEach((it, idx) => {
                const r = document.createElement('tr');
                r.innerHTML = `<td><select name="items[${idx}][product_id]" class="form-control"><option value="${it.product_id}">${it.product.nama_produk}</option></select></td>
                    <td><input type="number" name="items[${idx}][stok_sistem]" class="form-control" value="${it.stok_sistem}" readonly></td>
                    <td><input type="number" name="items[${idx}][stok_fisik]" class="form-control" value="${it.stok_fisik}"></td>
                    <td><input type="text" name="items[${idx}][keterangan]" class="form-control" value="${it.keterangan||''}"></td>
                    <td><button type="button" class="btn-filter" onclick="this.closest('tr').remove()"><iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon></button></td>`;
                tbody.appendChild(r);
            });
            openModal('addOpnameModal');
        });
    }

    // --- Request Helpers ---
    function openAddRequestModal() {
        const form = document.getElementById('requestForm');
        form.action = "{{ route('products.request.store') }}";
        document.getElementById('requestMethod').innerHTML = '';
        form.reset();
        openModal('addRequestModal');
    }

    function openEditRequestModal(req) {
        const form = document.getElementById('requestForm');
        form.action = `/products/request/${req.uuid}`;
        document.getElementById('requestMethod').innerHTML = '@method("PUT")';
        
        form.querySelector('[name="product_id"]').value = req.product_id;
        form.querySelector('[name="jumlah_minta"]').value = req.jumlah_minta;
        form.querySelector('[name="prioritas"]').value = req.prioritas;
        form.querySelector('[name="alasan_permintaan"]').value = req.alasan_permintaan || '';
        
        if (form.querySelector('[name="store_id"]')) {
            form.querySelector('[name="store_id"]').value = req.store_id;
        }
        
        openModal('addRequestModal');
    }

    function openRequestDetailModal(req) {
        document.getElementById('det_req_produk').innerText = req.product ? req.product.nama_produk : 'Produk Terhapus';
        document.getElementById('det_req_pemohon').innerText = req.pemohon;
        document.getElementById('det_req_outlet').innerText = req.store ? req.store.nama : '-';
        document.getElementById('det_req_jumlah').innerText = req.jumlah_minta;
        document.getElementById('det_req_prioritas').innerText = req.prioritas;
        document.getElementById('det_req_status').innerText = req.status;
        document.getElementById('det_req_alasan').innerText = req.alasan_permintaan || '(Tidak ada alasan)';
        
        openModal('requestDetailModal');
    }

    function confirmCancelRequest(uuid) {
        Swal.fire({
            title: 'Batalkan Request?',
            text: "Yakin ingin membatalkan permintaan stok ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Batalkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}`, 'DELETE');
            }
        });
    }

    function confirmRequestAction(uuid, action) {
        const isApprove = action === 'approve';
        Swal.fire({
            title: isApprove ? 'Setujui Request?' : 'Tolak Request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#2E7D32' : '#D9534F',
            confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}/${action}`, 'POST');
            }
        });
    }

    function confirmShipRequest(uuid) {
        Swal.fire({
            title: 'Kirim Barang?',
            text: "Barang akan ditandai sebagai sedang dikirim ke cabang.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#1976D2',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}/ship`, 'POST');
            }
        });
    }

    function confirmReceiveRequest(uuid) {
        Swal.fire({
            title: 'Terima Barang?',
            text: "Klik Ya jika barang sudah sampai. Stok cabang akan bertambah otomatis.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#2E7D32',
            confirmButtonText: 'Ya, Terima'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}/receive`, 'POST');
            }
        });
    }

    function confirmDeleteOpname(uuid, date) {
        Swal.fire({
            title: 'Hapus Riwayat Opname?',
            text: `Yakin hapus data opname tanggal ${date}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/opname/${uuid}`, 'DELETE');
            }
        });
    }

    function submitHiddenForm(url, method) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="${method}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    (() => {
        const success = '{{ session("success") }}';
        const error = '{{ session("error") }}';
        if (success) Swal.fire({ icon: 'success', title: 'Berhasil!', text: success, timer: 2500, showConfirmButton: false });
        if (error) Swal.fire({ icon: 'error', title: 'Gagal!', text: error });
    })();

    window.onclick = e => { 
        if (e.target.className === 'modal-overlay') e.target.style.display = 'none'; 

        // Close dropdowns if clicking outside any dropdown
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    }
</script>
@endsection
