@extends('layouts.app')

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

<style>
    .transaksi-wrapper {
        padding: 20px;
        display: flex;
        flex-direction: column;
    }

    .pos-layout {
        display: grid;
        grid-template-columns: 2.5fr 1.5fr;
        gap: 20px;
        align-items: start;
    }

    .product-section, .cart-section {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 15px;
    }

    .card-header h4 {
        margin: 0;
        font-size: 16px;
        color: #1e293b;
    }

    /* Search Box */
    .search-box {
        display: flex;
        align-items: center;
        background: #f8fafc;
        padding: 8px 15px;
        border-radius: 8px;
        gap: 10px;
        border: 1px solid #e2e8f0;
    }
    .search-box iconify-icon {
        color: #64748b;
    }
    .search-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 13px;
        color: #334155;
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
    }

    .product-card {
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .product-card:hover {
        border-color: #0ea5e9;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1);
        transform: translateY(-2px);
    }

    .product-img {
        width: 60px;
        height: 60px;
        margin: 0 auto 10px;
        background: #f8fafc;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: #94a3b8;
    }

    .product-info h5 {
        margin: 0 0 5px;
        font-size: 13px;
        color: #334155;
    }

    .product-info .price {
        margin: 0;
        font-size: 13px;
        color: #0ea5e9;
        font-weight: 600;
    }

    /* Cart Section */
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .warning { background: #fef9c3; color: #854d0e; }
    .success { background: #dcfce7; color: #166534; }

    .cart-items {
        min-height: 250px;
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 20px;
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #f1f5f9;
    }

    .item-desc h6 { margin: 0 0 4px; font-size: 13px; color: #334155; }
    .item-desc p { margin: 0; font-size: 12px; color: #64748b; }

    .item-qty {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .qty-btn {
        background: #fff;
        border: 1px solid #e2e8f0;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #334155;
        font-weight: bold;
    }

    .item-total { font-size: 13px; font-weight: 600; color: #1e293b; }

    .cart-summary {
        background: #f8fafc;
        padding: 15px;
        border-radius: 12px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 13px;
        color: #64748b;
    }

    .summary-row.total {
        border-top: 1px dashed #cbd5e1;
        padding-top: 10px;
        font-size: 16px;
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 20px;
    }

    .text-success { color: #166534; }

    .action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .btn {
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: opacity 0.2s;
        text-align: center;
    }

    .btn:hover { opacity: 0.9; }

    .btn-primary {
        background: #0ea5e9;
        color: white;
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #475569;
    }
</style>
@endsection
