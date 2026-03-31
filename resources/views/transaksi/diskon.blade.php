@extends('layouts.app')

@section('content')
    <div class="transaksi-wrapper">
        @include('transaksi.partials.tabs')

        <div class="content-card">
            <div class="card-header">
                <h4>Manajemen Diskon & Promo</h4>
                <div class="header-actions">
                    <button class="btn-primary-small">
                        <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                        Tambah Diskon
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Diskon</th>
                            <th>Kode Promo</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dummy Data 1 -->
                        <tr>
                            <td class="text-bold">Diskon Member</td>
                            <td>MEMBER-ROSTER</td>
                            <td>Potongan Harga</td>
                            <td class="text-bold text-success">Rp 5.000</td>
                            <td>Selamanya</td>
                            <td><span class="badge success">Aktif</span></td>
                            <td>
                                <div class="action-buttons-table">
                                    <button class="btn-icon">
                                        <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-icon text-danger">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Dummy Data 2 -->
                        <tr>
                            <td class="text-bold">Promo Akhir Tahun</td>
                            <td>YREND-2023</td>
                            <td>Persentase</td>
                            <td class="text-bold text-success">15%</td>
                            <td>01 Des - 31 Des</td>
                            <td><span class="badge warning">Nonaktif</span></td>
                            <td>
                                <div class="action-buttons-table">
                                    <button class="btn-icon">
                                        <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-icon text-danger">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
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

        .btn-primary-small:hover {
            opacity: 0.9;
        }

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

        .text-bold {
            font-weight: 600;
        }

        .text-success {
            color: #166534;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .action-buttons-table {
            display: flex;
            gap: 8px;
        }

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

        .btn-icon.text-danger:hover {
            background: #fee2e2;
            color: #ef4444;
        }
    </style>
@endsection