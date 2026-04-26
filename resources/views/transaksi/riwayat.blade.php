@extends('layouts.app')

@section('content')
<div class="fitur-container">
    @include('transaksi.partials.tabs')

    <div class="action-bar">
        <div class="left-actions-group">
            <div class="search-wrapper">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" class="search-input" placeholder="Cari ID transaksi, kasir, atau pelanggan...">
            </div>
            <input type="date" class="category-filter">
        </div>

        <div class="right-actions">
            <button class="btn-action">
                <iconify-icon icon="solar:printer-minimalistic-line-duotone"></iconify-icon>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    <div class="main-content-box">
        <div class="table-container">
            <table class="fitur-table">
                <thead>
                    <tr>
                        <th>ID TRANSAKSI</th>
                        <th>TANGGAL</th>
                        <th>KASIR</th>
                        <th>PELANGGAN</th>
                        <th>ITEM</th>
                        <th>TOTAL HARGA</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $trx)
                    <tr>
                        <td style="font-weight: 700; color: var(--primary-blue);">{{ $trx['id'] }}</td>
                        <td>{{ $trx['tanggal'] }}</td>
                        <td>{{ $trx['kasir'] }}</td>
                        <td>{{ $trx['pelanggan'] }}</td>
                        <td>{{ $trx['qty'] }} pcs</td>
                        <td class="price-text">{{ $trx['total'] }}</td>
                        <td>
                            <span class="status-badge {{ strtolower($trx['status']) == 'selesai' ? 'status-active' : 'status-proses' }}">
                                {{ $trx['status'] }}
                            </span>
                        </td>
                        <td>
                            <button class="btn-filter" style="width: 32px; height: 32px;">
                                <iconify-icon icon="solar:eye-bold-duotone" style="font-size: 18px;"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px; color: #94a3b8; font-style: italic;">
                            Belum ada riwayat transaksi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <ul class="pagination">
                <li class="disabled"><span>&laquo; Prev</span></li>
                <li class="active"><span>1</span></li>
                <li class="disabled"><span>Next &raquo;</span></li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* Import styles from fitur.css logic */
    :root {
        --primary-blue: #0081C9;
        --light-blue: #EEF7FF;
        --border-blue: #5EB7EB;
        --text-dark: #333333;
    }

    .fitur-container {
        padding: 24px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 12px;
    }

    .left-actions-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }

    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 400px;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px 12px 48px;
        border-radius: 50px;
        border: 2px solid var(--border-blue);
        outline: none;
        font-size: 15px;
        background: white;
        transition: border-color 0.3s;
    }

    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-blue);
        font-size: 24px;
    }

    .category-filter {
        padding: 10px 16px;
        border-radius: 50px;
        border: 2px solid var(--border-blue);
        outline: none;
        font-size: 14px;
        background: white;
        color: var(--text-dark);
        cursor: pointer;
    }

    .btn-action {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 12px;
        border: none;
        background: var(--primary-blue);
        color: white;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-action:hover { opacity: 0.9; transform: translateY(-1px); }

    .main-content-box {
        border: 2px solid var(--border-blue);
        border-radius: 30px;
        min-height: 400px;
        background: white;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .table-container {
        overflow-x: auto;
    }

    .fitur-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .fitur-table th {
        padding: 16px;
        background: var(--light-blue);
        color: var(--primary-blue);
        font-weight: 700;
        font-size: 13px;
        border-bottom: 2px solid var(--border-blue);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .fitur-table td {
        padding: 18px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #475569;
        vertical-align: middle;
    }

    .price-text {
        font-weight: 700;
        color: var(--primary-blue);
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
    }

    .status-active { background: #E8F5E9; color: #2E7D32; }
    .status-proses { background: #FFF3E0; color: #E65100; }

    .btn-filter {
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 2px solid var(--border-blue);
        border-radius: 50%;
        color: var(--primary-blue);
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-filter:hover { background: var(--light-blue); }

    .pagination-container {
        padding: 30px 0 10px;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
    }

    .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        background: white;
        border: 2px solid var(--border-blue);
        color: var(--primary-blue);
        font-weight: 700;
        font-size: 13px;
    }

    .pagination li.active span {
        background: var(--primary-blue);
        color: white;
        border-color: var(--primary-blue);
    }

    .pagination li.disabled span {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #cbd5e1;
    }
</style>
@endsection
