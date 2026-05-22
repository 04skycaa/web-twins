@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
@endpush

@section('content')
<div class="dashboard-wrapper mobile-pb">
    @if(Auth::user()->role === 'owner')
        <div class="welcome-section">
            <div>
                <h1 class="welcome-title">
                    Selamat Datang, 
                    <span class="user-name-gradient">{{ Auth::user()->name }}</span>
                    <span class="waving-hand">👋</span>
                </h1>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Outlet Filter -->
                <div class="filter-group-minimal">
                    <iconify-icon icon="solar:shop-2-bold-duotone" style="color: #0477bf; font-size: 1.25rem;"></iconify-icon>
                    <select class="outlet-select" style="border: none; background: transparent; font-size: 0.8rem; font-weight: 700; color: #475569; outline: none; padding-right: 15px;" onchange="filterByStore(this.value)">
                        <option value="">Semua Outlet (Pusat)</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->uuid }}" {{ ($currentStoreId ?? '') == $store->uuid ? 'selected' : '' }}>
                                {{ $store->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Refresh Button -->
                <button onclick="refreshDashboard()" class="filter-group-minimal cursor-pointer" style="padding: 8px 15px; display: flex; align-items: center; gap: 5px; border: 1px solid #e2e8f0; border-radius: 50px; background: #f8fafc; outline: none;">
                    <iconify-icon icon="solar:restart-bold-duotone" style="color: #10b981; font-size: 1.25rem;"></iconify-icon>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Refresh Data</span>
                </button>
            </div>
        </div>
    <!-- Top Row Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Transaksi -->
        <div class="stat-card" style="background: #f0f7ff; animation-delay: 0.1s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-value">{{ number_format($total_transaksi) }}</div>
            <div class="stat-trend {{ $diff_transaksi >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_transaksi >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_transaksi) }}% dari kemarin
            </div>
        </div>

        <!-- Pendapatan Offline -->
        <div class="stat-card" style="background: #f0fdf4; animation-delay: 0.2s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <iconify-icon icon="solar:cart-large-minimalistic-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pendapatan Offline</div>
            </div>
            <div class="stat-value">Rp {{ number_format($rev_offline / 1000, 0) }}k</div>
            <div class="stat-trend {{ $diff_offline >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_offline >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_offline) }}% dari kemarin
            </div>
        </div>

        <!-- Pendapatan Online -->
        <div class="stat-card" style="background: #f0f9ff; animation-delay: 0.3s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0f9ff; color: #0ea5e9;">
                    <iconify-icon icon="solar:global-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pendapatan Online</div>
            </div>
            <div class="stat-value">Rp {{ number_format($rev_online / 1000, 0) }}k</div>
            <div class="stat-trend {{ $diff_online >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_online >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_online) }}% dari kemarin
            </div>
        </div>

        <!-- Produk Terjual -->
        <div class="stat-card" style="background: #fffaf0; animation-delay: 0.4s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #fff7ed; color: #f97316;">
                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Produk Terjual</div>
            </div>
            <div class="stat-value">{{ number_format($total_produk_terjual) }}</div>
            <div class="stat-trend {{ $diff_produk_terjual >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_produk_terjual >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_produk_terjual) }}% dari kemarin
            </div>
        </div>

        <!-- Total Customer -->
        <div class="stat-card" style="background: #f5f3ff; animation-delay: 0.5s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #faf5ff; color: #8b5cf6;">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Customer</div>
            </div>
            <div class="stat-value">{{ number_format($total_customers) }}</div>
            <div style="margin-top: 10px;">
                <a href="{{ url('/users') }}" class="btn-detail-modern" style="--btn-theme: #8b5cf6; --btn-bg: rgba(139, 92, 246, 0.08); --btn-border: rgba(139, 92, 246, 0.1);">
                    <span>Lihat Detail</span>
                    <iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>
                </a>
            </div>
        </div>
    </div>

    <!-- Second Row Charts -->
    <div class="main-grid">
        <!-- Main Sales Chart -->
        <div class="col-span-12 lg:col-span-8 card" style="background: #f0f7ff; border-color: rgba(59, 130, 246, 0.15);">
            <div class="card-header">
                <h3 class="card-title">Penjualan Hari Ini</h3>
                <div class="flex items-center gap-2">
                    <div id="year-range-picker" class="hidden flex items-center gap-2 mr-2">
                        <input type="number" id="year-from" class="chart-select w-20" value="{{ date('Y') - 4 }}" placeholder="Dari">
                        <span class="text-xs font-bold">-</span>
                        <input type="number" id="year-to" class="chart-select w-20" value="{{ date('Y') }}" placeholder="Ke">
                        <button onclick="applyYearRange()" class="p-2 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <iconify-icon icon="solar:check-read-bold"></iconify-icon>
                        </button>
                    </div>
                    <select class="chart-select" onchange="updateMainChart(this.value)">
                        <option value="harian" selected>Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
            </div>
            <div id="mainSalesChart" style="min-height: 300px;"></div>
            <div class="grid grid-cols-2 mt-2 pt-4 border-t border-slate-100">
                <div class="text-center">
                    <p class="stat-label">Total Penjualan Hari Ini</p>
                    <p class="value" style="font-size: 1.25rem; font-weight: 800; color: #3b82f6;">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</p>
                </div>
                <div class="text-center border-l border-slate-100">
                    <p class="stat-label">Rata-rata per Jam</p>
                    <p class="value" style="font-size: 1.25rem; font-weight: 800;">Rp {{ number_format($total_pendapatan / 24, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Right Side Charts -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <!-- Pemasukan & Pengeluaran -->
            <div class="card" style="background: #f0fdf4; border-color: rgba(16, 185, 129, 0.1);">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 0.95rem;">Pemasukan & Pengeluaran</h3>
                    <select class="chart-select" onchange="updateCashFlow(this.value)">
                        <option value="harian">Hari Ini</option>
                        <option value="mingguan">Minggu Ini</option>
                        <option value="bulanan" selected>Bulan Ini</option>
                        <option value="tahunan">Tahun Ini</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-500">
                                <iconify-icon icon="solar:alt-arrow-down-bold"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Pemasukan</span>
                        </div>
                        <p class="font-extrabold text-green-600 text-sm" id="cf-total-pemasukan">Rp {{ number_format($cfBulanan['total_pemasukan'] / 1000, 0) }}k</p>
                        <div id="pemasukanChart" style="height: 60px;"></div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                                <iconify-icon icon="solar:alt-arrow-up-bold"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Pengeluaran</span>
                        </div>
                        <p class="font-extrabold text-red-600 text-sm" id="cf-total-pengeluaran">Rp {{ number_format($cfBulanan['total_pengeluaran'] / 1000, 0) }}k</p>
                        <div id="pengeluaranChart" style="height: 60px;"></div>
                    </div>
                </div>
            </div>

            <!-- Hutang & Piutang -->
            <div class="card flex-1" style="background: #fef2f2; border-color: rgba(239, 68, 68, 0.1);">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 0.95rem;">Hutang & Piutang</h3>
                    <iconify-icon icon="solar:alt-arrow-right-bold" style="color: #cbd5e1;"></iconify-icon>
                </div>
                <div class="flex items-center gap-4">
                    <div id="debtChart" style="width: 140px;"></div>
                    <div class="flex-1">
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-[11px] font-bold text-slate-500">Piutang</span>
                            </div>
                            <p style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                <span class="text-[11px] font-bold text-slate-500">Hutang</span>
                            </div>
                            <p style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">Rp {{ number_format($totalHutang, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row Tables -->
    <div class="main-grid">
        <!-- Stok & Kadaluarsa Tabbed Card -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #fff1f2; border-color: rgba(225, 29, 72, 0.1);">
            <div class="card-header" style="margin-bottom: 10px;">
                <h3 class="card-title">Stok & Kadaluarsa</h3>
                <a href="{{ url('/products?tab=stok') }}" class="btn-detail-modern" style="--btn-theme: #3b82f6; --btn-bg: rgba(59, 130, 246, 0.08); --btn-border: rgba(59, 130, 246, 0.1);">
                    <span>Kelola</span>
                    <iconify-icon icon="solar:settings-bold-duotone"></iconify-icon>
                </a>
            </div>
            
            <div class="stock-tabs">
                <button class="stock-tab-btn active" onclick="switchStockTab('stok', this)">
                    <iconify-icon icon="solar:Box-bold-duotone"></iconify-icon>
                    Stok Menipis
                </button>
                <button class="stock-tab-btn" onclick="switchStockTab('expired', this)">
                    <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                    Segera Expired
                </button>
            </div>

            <!-- Tab Stok Menipis -->
            <div id="tab-stok" class="stock-content active">
                <div class="scrollable-table-container">
                    <table class="custom-table compact-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="text-align: center; width: 50px;">Stok</th>
                                <th style="text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $ps)
                            @php
                                $psObj = is_array($ps) ? (object) $ps : $ps;
                                $productVal = is_array($ps) ? ($ps['product'] ?? null) : ($ps->product ?? null);
                                $productObj = is_array($productVal) ? (object) $productVal : $productVal;
                                $storeVal = is_array($ps) ? ($ps['store'] ?? null) : ($ps->store ?? null);
                                $storeObj = is_array($storeVal) ? (object) $storeVal : $storeVal;
                                $stokVal = is_array($ps) ? ($ps['stok'] ?? 0) : ($ps->stok ?? 0);
                            @endphp
                            @if($productObj)
                            @php
                                $imageUrl = $productObj->resolved_image_url ?? null;
                                if (!$imageUrl) {
                                    $path = $productObj->image_url ?? null;
                                    if (!$path) {
                                        $imageUrl = asset('images/placeholder-product.png');
                                    } elseif (str_starts_with($path, 'http')) {
                                        $imageUrl = $path;
                                    } else {
                                        $cleanPath = ltrim($path, '/');
                                        if (str_starts_with($cleanPath, 'storage/')) {
                                            $cleanPath = substr($cleanPath, 8);
                                        }
                                        $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $imageUrl }}" class="product-img w-8 h-8">
                                        <div>
                                            <p class="font-bold text-[11px] mb-0 line-clamp-1">{{ $productObj->nama_produk ?? 'Unknown' }}</p>
                                            <p class="text-[9px] text-slate-400 mb-0">{{ $storeObj->nama ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;" class="font-extrabold">{{ $stokVal }}</td>
                                <td style="text-align: right;">
                                    <span class="status-badge {{ $stokVal <= 2 ? 'badge-critical' : 'badge-low' }}" style="padding: 2px 6px; font-size: 0.6rem;">
                                        {{ $stokVal <= 2 ? 'Kritis' : 'Rendah' }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr><td colspan="3" class="text-center py-8 text-slate-400">Semua stok aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Expired -->
            <div id="tab-expired" class="stock-content">
                <div class="scrollable-table-container">
                    <table class="custom-table compact-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="text-align: center;">Expired</th>
                                <th style="text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiredProducts ?? [] as $ps)
                            @php
                                $psObj = is_array($ps) ? (object) $ps : $ps;
                                $productVal = is_array($ps) ? ($ps['product'] ?? null) : ($ps->product ?? null);
                                $productObj = is_array($productVal) ? (object) $productVal : $productVal;
                                $storeVal = is_array($ps) ? ($ps['store'] ?? null) : ($ps->store ?? null);
                                $storeObj = is_array($storeVal) ? (object) $storeVal : $storeVal;
                                $kadaluarsaVal = is_array($ps) ? ($ps['kadaluarsa'] ?? null) : ($ps->kadaluarsa ?? null);
                            @endphp
                            @if($productObj && $kadaluarsaVal)
                            @php
                                $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($kadaluarsaVal), false);
                                $imageUrl = $productObj->resolved_image_url ?? null;
                                if (!$imageUrl) {
                                    $path = $productObj->image_url ?? null;
                                    if (!$path) {
                                        $imageUrl = asset('images/placeholder-product.png');
                                    } elseif (str_starts_with($path, 'http')) {
                                        $imageUrl = $path;
                                    } else {
                                        $cleanPath = ltrim($path, '/');
                                        if (str_starts_with($cleanPath, 'storage/')) {
                                            $cleanPath = substr($cleanPath, 8);
                                        }
                                        $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $imageUrl }}" class="product-img w-8 h-8">
                                        <div>
                                            <p class="font-bold text-[11px] mb-0 line-clamp-1">{{ $productObj->nama_produk ?? 'Unknown' }}</p>
                                            <p class="text-[9px] text-slate-400 mb-0">{{ $storeObj->nama ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;" class="font-extrabold text-[10px]">
                                    {{ \Carbon\Carbon::parse($kadaluarsaVal)->format('d/m/y') }}
                                </td>
                                <td style="text-align: right;">
                                    <span class="status-badge {{ $daysLeft <= 7 ? 'badge-critical' : 'badge-low' }}" style="padding: 2px 6px; font-size: 0.6rem;">
                                        {{ $daysLeft <= 0 ? 'Expired' : ($daysLeft <= 7 ? 'Kritis' : 'Dekat') }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr><td colspan="3" class="text-center py-8 text-slate-400">Tidak ada produk segera expired.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Produk Terlaris Table -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #f5f3ff; border-color: rgba(139, 92, 246, 0.1);">
            <div class="card-header">
                <h3 class="card-title">Produk Terlaris</h3>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Produk</th>
                        <th style="text-align: center; width: 40px;">Qty</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $tp)
                    @php
                        $tpObj = is_array($tp) ? (object) $tp : $tp;
                        $productVal = is_array($tp) ? ($tp['product'] ?? null) : ($tp->product ?? null);
                        $productObj = is_array($productVal) ? (object) $productVal : $productVal;
                        $totalQtyVal = is_array($tp) ? ($tp['total_qty'] ?? 0) : ($tp->total_qty ?? 0);
                        $totalRevVal = is_array($tp) ? ($tp['total_revenue'] ?? 0) : ($tp->total_revenue ?? 0);
                    @endphp
                    @if($productObj)
                    @php
                        $imageUrl = $productObj->resolved_image_url ?? null;
                        if (!$imageUrl) {
                            $path = $productObj->image_url ?? null;
                            if (!$path) {
                                $imageUrl = asset('images/placeholder-product.png');
                            } elseif (str_starts_with($path, 'http')) {
                                $imageUrl = $path;
                            } else {
                                $cleanPath = ltrim($path, '/');
                                if (str_starts_with($cleanPath, 'storage/')) {
                                    $cleanPath = substr($cleanPath, 8);
                                }
                                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
                            }
                        }
                    @endphp
                    <tr>
                        <td class="font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $imageUrl }}" class="product-img w-8 h-8">
                                <span class="font-bold text-xs truncate max-w-[100px]">{{ $productObj->nama_produk ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td style="text-align: center;" class="font-extrabold">{{ $totalQtyVal }}</td>
                        <td style="text-align: right;" class="font-extrabold text-blue-600">Rp {{ number_format($totalRevVal / 1000, 0) }}k</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Aktivitas Penjualan Feed -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #f0fdfa; border-color: rgba(20, 184, 166, 0.1);">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Penjualan</h3>
                <iconify-icon icon="solar:history-bold-duotone" style="color: #64748b;"></iconify-icon>
            </div>
            <div class="activity-feed">
                @forelse($activities as $act)
                @php
                    $actObj = is_array($act) ? (object) $act : $act;
                    $roleVal = is_array($act) ? ($act['role'] ?? null) : ($act->role ?? null);
                @endphp
                @if($roleVal)
                <div class="activity-item">
                    <div class="activity-icon" style="background: {{ $roleVal == 'Online' ? '#e0e7ff' : '#f1f5f9' }};">
                        <iconify-icon icon="{{ $actObj->icon ?? '' }}" style="color: {{ $roleVal == 'Online' ? '#4f46e5' : '#64748b' }};"></iconify-icon>
                    </div>
                    <div class="activity-content">
                        <div class="flex justify-between items-start">
                            <span class="activity-user">{{ $actObj->user ?? '' }}</span>
                            <span class="activity-time">{{ $actObj->time ?? '' }}</span>
                        </div>
                        <p class="activity-text">
                            <span class="font-bold text-[10px] uppercase {{ $roleVal == 'Online' ? 'text-teal-600' : 'text-slate-400' }}">
                                {{ $roleVal }}
                            </span> | {{ $actObj->action ?? '' }}
                        </p>
                    </div>
                </div>
                @endif
                @empty
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm font-bold">Belum ada aktivitas penjualan hari ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Premium Dashboard for Kepala Toko / Staff -->
        <div class="welcome-section">
            <div>
                <h1 class="welcome-title">
                    Dashboard {{ ucfirst(Auth::user()->role) }}
                    <span class="waving-hand">👋</span>
                </h1>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-top: 2px;">
                    Mengelola outlet: <span style="color: #0477bf;">{{ Auth::user()->outlet->nama ?? 'Semua Outlet' }}</span>
                </p>
            </div>
        </div>

        <!-- 5-Column Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Transaksi -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f0f7ff; color: #3b82f6; border-radius: 12px;">
                        <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Total Transaksi</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($total_transaksi) }}</div>
                <div class="stat-trend {{ $diff_transaksi >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_transaksi >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_transaksi) }}% dari kemarin
                </div>
            </div>

            <!-- Pendapatan -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f0fdf4; color: #10b981; border-radius: 12px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Pendapatan</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.25rem; margin: 0.5rem 0;">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</div>
                <div class="stat-trend {{ $diff_pendapatan >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_pendapatan >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_pendapatan) }}% dari kemarin
                </div>
            </div>

            <!-- Produk Terjual -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #fffaf0; color: #f97316; border-radius: 12px;">
                        <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Produk Terjual</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($total_produk_terjual) }}</div>
                <div class="stat-trend {{ $diff_produk_terjual >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_produk_terjual >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_produk_terjual) }}% dari kemarin
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #fef2f2; color: #ef4444; border-radius: 12px;">
                        <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Stok Menipis</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($low_stock_count) }}</div>
                <a href="{{ url('/products?tab=stok') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700; text-decoration: underline;">Lihat detail</a>
            </div>

            <!-- Karyawan Aktif -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f5f3ff; color: #8b5cf6; border-radius: 12px;">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Karyawan Aktif</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ $active_employees }} <span style="font-size: 0.8rem; color: #94a3b8;">/ {{ $total_employees }}</span></div>
                <a href="{{ url('/users') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700; text-decoration: underline;">Lihat detail</a>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="card mt-6" style="border-radius: 24px; padding: 1.5rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid rgba(226, 232, 240, 0.8);">
            <div class="card-header">
                <h3 class="card-title">Penjualan Hari Ini</h3>
                <div class="flex items-center gap-2">
                    <select class="chart-select" onchange="updateMainChart(this.value)" style="padding: 6px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.75rem; font-weight: 700; color: #475569; outline: none; cursor: pointer;">
                        <option value="harian">Per Jam</option>
                        <option value="mingguan">Per Hari</option>
                        <option value="bulanan">Per Bulan</option>
                        <option value="tahunan">Per Tahun</option>
                    </select>
                    <div id="year-range-picker" class="hidden flex items-center gap-2">
                        <input type="number" id="year-from" value="{{ date('Y')-4 }}" class="w-16 p-1 text-[10px] border rounded">
                        <span class="text-[10px]">-</span>
                        <input type="number" id="year-to" value="{{ date('Y') }}" class="w-16 p-1 text-[10px] border rounded">
                        <button onclick="applyYearRange()" class="p-1 bg-blue-500 text-white rounded"><iconify-icon icon="solar:check-read-bold"></iconify-icon></button>
                    </div>
                </div>
            </div>
            <div id="mainSalesChart" style="min-height: 300px;"></div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-12 gap-6 mt-6">
            <!-- Produk Terlaris -->
            <div class="col-span-12 lg:col-span-6 card" style="border-radius: 24px; padding: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Produk Terlaris Hari Ini</h3>
                    <a href="{{ url('/products') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Produk</th>
                            <th style="text-align: center;">Terjual</th>
                            <th style="text-align: right;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $index => $tp)
                        @php
                            $tpObj = is_array($tp) ? (object) $tp : $tp;
                            $productVal = is_array($tp) ? ($tp['product'] ?? null) : ($tp->product ?? null);
                            $productObj = is_array($productVal) ? (object) $productVal : $productVal;
                            $totalQtyVal = is_array($tp) ? ($tp['total_qty'] ?? 0) : ($tp->total_qty ?? 0);
                            $totalRevVal = is_array($tp) ? ($tp['total_revenue'] ?? 0) : ($tp->total_revenue ?? 0);
                        @endphp
                        @if($productObj)
                        @php
                            $imageUrl = $productObj->resolved_image_url ?? null;
                            if (!$imageUrl) {
                                $path = $productObj->image_url ?? null;
                                if (!$path) {
                                    $imageUrl = asset('images/placeholder-product.png');
                                } elseif (str_starts_with($path, 'http')) {
                                    $imageUrl = $path;
                                } else {
                                    $cleanPath = ltrim($path, '/');
                                    if (str_starts_with($cleanPath, 'storage/')) {
                                        $cleanPath = substr($cleanPath, 8);
                                    }
                                    $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
                                }
                            }
                        @endphp
                        <tr>
                            <td class="font-bold text-slate-400">{{ $index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <img src="{{ $imageUrl }}" class="product-img w-8 h-8" style="border-radius: 8px;">
                                    <span class="font-bold text-xs">{{ $productObj->nama_produk ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;" class="font-extrabold">{{ $totalQtyVal }}</td>
                            <td style="text-align: right;" class="font-extrabold text-blue-600">Rp {{ number_format($totalRevVal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Aktivitas Karyawan -->
            <div class="col-span-12 lg:col-span-6 card" style="border-radius: 24px; padding: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Karyawan Hari Ini</h3>
                    <a href="#" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
                </div>
                <div class="activity-feed">
                    @forelse($activities as $act)
                    @php
                        $actObj = is_array($act) ? (object) $act : $act;
                        $userVal = is_array($act) ? ($act['user'] ?? null) : ($act->user ?? null);
                    @endphp
                    @if($userVal)
                    <div class="activity-item" style="margin-bottom: 1rem;">
                        <div class="activity-icon" style="background: #f1f5f9; border-radius: 50%;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($userVal) }}&background=random" class="w-full h-full rounded-full">
                        </div>
                        <div class="activity-content">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="activity-user" style="margin-bottom: 0;">{{ $userVal }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $actObj->role ?? '' }}</p>
                                </div>
                                <span class="activity-time">{{ $actObj->time ?? '' }}</span>
                            </div>
                            <p class="activity-text" style="margin-top: 2px;">{{ $actObj->action ?? '' }}</p>
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="text-center py-8 text-slate-400">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

@if(Auth::user()->role === 'owner' || Auth::user()->role === 'kepala toko')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    window.dashboardData = {
        chartHarian: @json($chartHarian),
        chartMingguan: @json($chartMingguan),
        chartBulanan: @json($chartBulanan),
        chartTahunan: @json($chartTahunan),
        cfHarian: @json($cfHarian),
        cfMingguan: @json($cfMingguan),
        cfBulanan: @json($cfBulanan),
        cfTahunan: @json($cfTahunan),
        totalPiutang: {{ $totalPiutang ?? 0 }},
        totalHutang: {{ $totalHutang ?? 0 }},
        formattedTotalDebt: '{{ number_format(($totalPiutang ?? 0) + ($totalHutang ?? 0), 0, "", ".") }}'
    };
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endif
@endsection
