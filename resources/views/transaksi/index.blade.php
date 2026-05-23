@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/transaksi/fitur.css') }}">
@endpush

@section('content')
<div class="transaksi-wrapper">
    @include('transaksi.partials.tabs')

    <div class="pos-layout">
        <!-- Kiri: Daftar Produk -->
        <div class="product-section">
            <div class="card-header">
                <h4>Pilih Produk</h4>
                <div class="search-box">
                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    <input type="text" placeholder="Cari produk...">
                </div>
            </div>
            
            <div class="product-grid">
                <!-- Dummy Product 1 -->
                <div class="product-card">
                    <div class="product-img">
                        <iconify-icon icon="solar:cup-hot-bold-duotone"></iconify-icon>
                    </div>
                    <div class="product-info">
                        <h5>Kopi Susu Aren</h5>
                        <p class="price">Rp 20.000</p>
                    </div>
                </div>
                <!-- Dummy Product 2 -->
                <div class="product-card">
                    <div class="product-img">
                        <iconify-icon icon="solar:bread-bold-duotone"></iconify-icon>
                    </div>
                    <div class="product-info">
                        <h5>Croissant Butter</h5>
                        <p class="price">Rp 25.000</p>
                    </div>
                </div>
                <!-- Dummy Product 3 -->
                <div class="product-card">
                    <div class="product-img">
                        <iconify-icon icon="solar:cup-paper-bold-duotone"></iconify-icon>
                    </div>
                    <div class="product-info">
                        <h5>Es Teh Leci</h5>
                        <p class="price">Rp 15.000</p>
                    </div>
                </div>
                <!-- Dummy Product 4 -->
                <div class="product-card">
                    <div class="product-img">
                        <iconify-icon icon="solar:cake-bold-duotone"></iconify-icon>
                    </div>
                    <div class="product-info">
                        <h5>Brownies Slice</h5>
                        <p class="price">Rp 18.000</p>
                    </div>
                </div>
                 <!-- Dummy Product 5 -->
                 <div class="product-card">
                    <div class="product-img">
                        <iconify-icon icon="solar:donut-bitten-bold-duotone"></iconify-icon>
                    </div>
                    <div class="product-info">
                        <h5>Donat Coklat</h5>
                        <p class="price">Rp 10.000</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Keranjang -->
        <div class="cart-section">
            <div class="card-header">
                <h4>Detail Pesanan</h4>
                <span class="badge warning">Baru</span>
            </div>
            
            <div class="cart-items">
                <!-- Dummy Cart Item -->
                <div class="cart-item">
                    <div class="item-desc">
                        <h6>Kopi Susu Aren</h6>
                        <p>Rp 20.000</p>
                    </div>
                    <div class="item-qty">
                        <button class="qty-btn">-</button>
                        <span>2</span>
                        <button class="qty-btn">+</button>
                    </div>
                    <div class="item-total">
                        Rp 40.000
                    </div>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp 40.000</span>
                </div>
                <div class="summary-row text-success">
                    <span>Diskon (Member)</span>
                    <span>- Rp 5.000</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rp 35.000</span>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-secondary">Simpan Draft</button>
                    <button class="btn btn-primary">Bayar Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
