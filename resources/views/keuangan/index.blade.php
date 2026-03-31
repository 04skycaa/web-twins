@extends('layouts.app')

@section('content')
    <div class="keuangan-wrapper">
        <div class="page-header">
            <div>

            </div>
            <button class="btn-primary" onclick="window.print()">
                <iconify-icon icon="solar:printer-bold-duotone"></iconify-icon>
                Cetak Laporan
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="card-icon" style="background: #e0f2fe; color: #0284c7;">
                    <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <p>Pendapatan Hari Ini</p>
                    <h3>Rp {{ number_format($todayIncome, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon" style="background: #fef08a; color: #854d0e;">
                    <iconify-icon icon="solar:cart-large-4-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <p>Transaksi Hari Ini</p>
                    <h3>{{ $todayTransactions }} <span
                            style="font-size:14px; color:#64748b; font-weight:normal;">Struk</span></h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon" style="background: #dcfce7; color: #166534;">
                    <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <p>Omzet Bulan Ini</p>
                    <h3>Rp {{ number_format($monthIncome, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon" style="background: #f3e8ff; color: #7e22ce;">
                    <iconify-icon icon="solar:safe-2-bold-duotone"></iconify-icon>
                </div>
                <div class="card-info">
                    <p>Estimasi Laba Bersih (30%)</p>
                    <h3>Rp {{ number_format($monthIncome * 0.3, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="grid-layout">
            <!-- Chart Section -->
            <div class="content-card chart-container">
                <div class="card-header">
                    <h4>Grafik Pendapatan (7 Hari Terakhir)</h4>
                </div>
                <canvas id="keuanganChart" height="100"></canvas>
            </div>

            <!-- Recent Transactions -->
            <div class="content-card">
                <div class="card-header">
                    <h4>Riwayat Mutasi Terakhir</h4>
                    <a href="{{ route('transaksi.index') }}"
                        style="font-size: 13px; color: #0ea5e9; text-decoration: none; font-weight: 500;">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="custom-table" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>No. Transaksi</th>
                                <th>Status</th>
                                <th style="text-align: right;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($tx->tanggalorder)->format('d M, H:i') }}</td>
                                    <td class="text-bold">#TRX-{{ str_pad($tx->idorder, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        @if(strtolower($tx->status) == 'completed' || strtolower($tx->status) == 'selesai')
                                            <span class="badge success">Selesai</span>
                                        @else
                                            <span class="badge warning">{{ ucfirst($tx->status ?? 'Selesai') }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: #166534;">
                                        + Rp {{ number_format($tx->grandtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">Belum ada riwayat
                                        pemasukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('keuanganChart').getContext('2d');

            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(14, 165, 233, 0.5)'); // Cyan transparent
            gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');   // Transparent

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartDates) !!}.reverse(), // reverse karena array date disubstitute 6 ke 0
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: {!! json_encode($chartData) !!}.reverse(),
                        borderColor: '#0ea5e9',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.4 // Smooth curve
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000) return value / 1000000 + 'Jt';
                                    if (value >= 1000) return value / 1000 + 'k';
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>

    <style>
        .keuangan-wrapper {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            margin: 0;
            font-size: 24px;
            color: #0f172a;
        }

        .page-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #64748b;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0ea5e9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .summary-card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .card-info p {
            margin: 0;
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .card-info h3 {
            margin: 5px 0 0 0;
            font-size: 20px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        @media (max-width: 1024px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .content-card {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid #f1f5f9;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f8fafc;
            padding-bottom: 15px;
        }

        .card-header h4 {
            margin: 0;
            font-size: 16px;
            color: #1e293b;
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
            padding: 12px 10px;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }

        .custom-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        .text-bold {
            font-weight: 600;
            color: #0f172a;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .warning {
            background: #fef9c3;
            color: #854d0e;
        }

        @media print {
            .page-header button {
                display: none;
            }

            .keuangan-wrapper {
                padding: 0;
                gap: 10px;
            }

            .summary-grid,
            .grid-layout {
                display: block;
            }

            .content-card,
            .summary-card {
                break-inside: avoid;
                border: 1px solid #ccc;
                box-shadow: none;
                margin-bottom: 20px;
            }

            .card-header a {
                display: none;
            }
        }
    </style>
@endsection