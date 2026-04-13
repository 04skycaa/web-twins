@extends('layouts.app')

@section('content')
<div class="transaksi-wrapper">
    @include('transaksi.partials.tabs')

    <div class="content-card mt-3">
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
                    @forelse($data as $trx)
                    <tr>
                        <td class="text-bold">{{ $trx['id'] }}</td>
                        <td>{{ $trx['tanggal'] }}</td>
                        <td>{{ $trx['kasir'] }}</td>
                        <td>{{ $trx['pelanggan'] }}</td>
                        <td>{{ $trx['qty'] }} pcs</td>
                        <td class="text-bold">{{ $trx['total'] }}</td>
                        <td><span class="badge {{ strtolower($trx['status']) == 'selesai' ? 'success' : 'warning' }}">{{ $trx['status'] }}</span></td>
                        <td>
                            <button class="btn-icon">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            <span class="text-muted">Menampilkan {{ count($data) }} dari {{ count($data) }} transaksi</span>
            <div class="pagination-buttons">
                <button disabled>&laquo; Prev</button>
                <button class="active">1</button>
                <button disabled>Next &raquo;</button>
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

    .mt-3 { margin-top: 1rem; }

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
    .text-center { text-align: center; }
    .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }

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
