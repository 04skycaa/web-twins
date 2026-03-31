@extends('layouts.app')

@section('content')
<div class="transaksi-wrapper">
    @include('transaksi.partials.tabs')

    <div class="content-card">
        <div class="card-header">
            <h4>Riwayat Transaksi</h4>
            <div class="header-actions">
                <input type="date" class="date-filter">
                <button class="btn-primary-small">
                    <iconify-icon icon="solar:printer-minimalistic-line-duotone"></iconify-icon>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dummy Data 1 -->
                    <tr>
                        <td class="text-bold">#TW-00123</td>
                        <td>24 Okt 2023 14:30</td>
                        <td>Budi (Admin)</td>
                        <td>Umum / Non-Member</td>
                        <td>3 pcs</td>
                        <td class="text-bold">Rp 60.000</td>
                        <td><span class="badge success">Selesai</span></td>
                        <td>
                            <button class="btn-icon">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    <!-- Dummy Data 2 -->
                    <tr>
                        <td class="text-bold">#TW-00124</td>
                        <td>24 Okt 2023 15:10</td>
                        <td>Siti (Kasir)</td>
                        <td>Member (Andi)</td>
                        <td>5 pcs</td>
                        <td class="text-bold">Rp 120.000</td>
                        <td><span class="badge success">Selesai</span></td>
                        <td>
                            <button class="btn-icon">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    <!-- Dummy Data 3 -->
                    <tr>
                        <td class="text-bold">#TW-00125</td>
                        <td>24 Okt 2023 16:05</td>
                        <td>Budi (Admin)</td>
                        <td>GrabFood</td>
                        <td>2 pcs</td>
                        <td class="text-bold">Rp 45.000</td>
                        <td><span class="badge warning">Proses</span></td>
                        <td>
                            <button class="btn-icon">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            <!-- Dummy Pagination -->
            <span class="text-muted">Menampilkan 1-3 dari 50 transaksi</span>
            <div class="pagination-buttons">
                <button disabled>&laquo; Prev</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>Next &raquo;</button>
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

    .card-header h4 {
        margin: 0;
        font-size: 16px;
        color: #1e293b;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .date-filter {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        color: #475569;
        outline: none;
    }

    .btn-primary-small {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #0ea5e9;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-primary-small:hover { opacity: 0.9; }

    .table-responsive {
        overflow-x: auto;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        text-align: left;
        padding: 12px 15px;
        background: #f8fafc;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }

    .custom-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
    }

    .text-bold { font-weight: 600; }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .success { background: #dcfce7; color: #166534; }
    .warning { background: #fef9c3; color: #854d0e; }

    .btn-icon {
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-icon:hover {
        background: #e0f2fe;
        color: #0ea5e9;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    .text-muted {
        font-size: 13px;
        color: #64748b;
    }

    .pagination-buttons {
        display: flex;
        gap: 5px;
    }

    .pagination-buttons button {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        cursor: pointer;
    }

    .pagination-buttons button.active {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }

    .pagination-buttons button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection
