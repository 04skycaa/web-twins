@extends('layouts.app')

@section('content')
<div class="absensi-wrapper">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <iconify-icon icon="solar:user-check-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Hadir Hari Ini</p>
                <h3>12/15</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Terlambat</p>
                <h3>2</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
            </div>
            <div class="stat-info">
                <p>Rata-rata Kehadiran</p>
                <h3>94%</h3>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h4>Log Kehadiran Karyawan</h4>
            <div class="header-actions">
                <button class="btn-filter">
                    <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon>
                    Filter
                </button>
                <button class="btn-export">
                    <iconify-icon icon="solar:download-bold-duotone"></iconify-icon>
                    Export
                </button>
            </div>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="user-info">
                            <span class="user-name">Kiyomi Agatca</span>
                        </div>
                    </td>
                    <td>21 Apr 2026</td>
                    <td>07:55:12</td>
                    <td>17:05:43</td>
                    <td><span class="badge success">Tepat Waktu</span></td>
                    <td>Outlet Pusat</td>
                </tr>
                <tr>
                    <td>
                        <div class="user-info">
                            <span class="user-name">Fara Yuanita</span>
                        </div>
                    </td>
                    <td>21 Apr 2026</td>
                    <td>08:15:22</td>
                    <td>-</td>
                    <td><span class="badge warning">Terlambat</span></td>
                    <td>Outlet Cabang A</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .absensi-wrapper {
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

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-filter, .btn-export {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-filter:hover, .btn-export:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
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

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
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
