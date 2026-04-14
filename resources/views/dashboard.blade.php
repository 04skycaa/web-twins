@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Total Produk</p>
                <h3>1,240</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <iconify-icon icon="solar:wad-of-money-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Pendapatan Hari Ini</p>
                <h3>Rp 3.500.000</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Pesanan Baru</p>
                <h3>45</h3>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h4>Transaksi Terbaru</h4>
            <a href="#" class="btn-view-all">Lihat Semua</a>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#TWN-001</td>
                    <td>Kiyomi Agatca</td>
                    <td>Kopi Susu Gula Aren</td>
                    <td><span class="badge success">Selesai</span></td>
                    <td>Rp 25.000</td>
                </tr>
                <tr>
                    <td>#TWN-002</td>
                    <td>Fara Yuanita Agustin</td>
                    <td>Croissant Almond</td>
                    <td><span class="badge warning">Proses</span></td>
                    <td>Rp 45.000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .dashboard-wrapper {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .blue { background: #e0f2fe; color: #0ea5e9; }
    .green { background: #dcfce7; color: #22c55e; }
    .orange { background: #ffedd5; color: #f97316; }

    .stat-info p { color: #64748b; font-size: 14px; margin: 0; }
    .stat-info h3 { margin: 5px 0 0; font-size: 20px; color: #1e293b; }

    .content-card {
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
    }

    .btn-view-all {
        font-size: 13px;
        color: #6366f1;
        text-decoration: none;
        font-weight: 600;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        text-align: left;
        padding: 12px;
        background: #f8fafc;
        color: #64748b;
        font-size: 13px;
    }

    .custom-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #334155;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .success { background: #dcfce7; color: #166534; }
    .warning { background: #fef9c3; color: #854d0e; }
</style>
@endsection