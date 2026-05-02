<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Buku Kas</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0081C9; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #0081C9; }
        .info { margin-bottom: 20px; line-height: 1.6; }
        
        .summary-box { background: #EEF7FF; border: 1px solid #5EB7EB; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .summary-title { font-weight: bold; margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #5EB7EB; padding-bottom: 5px; color: #0081C9; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th { background-color: #EEF7FF; color: #0081C9; padding: 10px; border: 1px solid #5EB7EB; text-align: left; }
        td { padding: 8px; border: 1px solid #eee; }
        tr:nth-child(even) { background-color: #fafafa; }
        
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-primary { color: #0081C9; font-weight: bold; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; display: inline-block; }
        .badge-lunas { background: #dcfce7; color: #166534; }
        .badge-belum { background: #ffedd5; color: #9a3412; }
        h4 { color: #0081C9; border-bottom: 1px dashed #5EB7EB; padding-bottom: 5px; margin-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">TWINS SYSTEM</div>
        <div style="font-size: 16px; margin-top: 5px;">LAPORAN BUKU KAS</div>
    </div>

    <div class="info">
        <strong>Tanggal Cetak:</strong> {{ date('d F Y') }}<br>
        <strong>Outlet:</strong> {{ $outlet_name }}<br>
        <strong>Kategori:</strong> {{ count($kategoriList) === 4 ? 'Semua (Lengkap)' : implode(', ', $kategoriList) }}<br>
        <strong>Periode:</strong> {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('d/m/Y') : 'Awal' }} - {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d/m/Y') : 'Sekarang' }}<br>
        <strong>Dicetak Oleh:</strong> {{ Auth::user()->name }}
    </div>

    @if(in_array('Pemasukan', $kategoriList) || in_array('Pengeluaran', $kategoriList))
        <div class="summary-box">
            <div class="summary-title">Ringkasan Cashflow</div>
            <table style="border: none; margin-bottom: 0;">
                <tr style="border: none; background-color: transparent;">
                    <td style="border: none; padding: 4px 0;">Total Pemasukan</td>
                    <td style="border: none; padding: 4px 0;" class="text-right text-success">Rp {{ number_format($total_pemasukan ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border: none; background-color: transparent;">
                    <td style="border: none; padding: 4px 0;">Total Pengeluaran</td>
                    <td style="border: none; padding: 4px 0;" class="text-right text-danger">Rp {{ number_format($total_pengeluaran ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border: none; background-color: transparent;">
                    <td style="border: none; padding: 8px 0 0 0; font-weight: bold; border-top: 1px dashed #5EB7EB;">Selisih Bersih (Net)</td>
                    @php $net = ($total_pemasukan ?? 0) - ($total_pengeluaran ?? 0); @endphp
                    <td style="border: none; padding: 8px 0 0 0; font-weight: bold; border-top: 1px dashed #5EB7EB;" class="text-right {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($net, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        @if(in_array('Pemasukan', $kategoriList))
            <h4 style="margin-top: 10px;">Daftar Pemasukan</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Outlet</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pemasukan ?? [] as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $p->keterangan }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td class="text-right text-success">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada data pemasukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if(in_array('Pengeluaran', $kategoriList))
            <h4>Daftar Pengeluaran</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Outlet</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluaran ?? [] as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $p->keterangan }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada data pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('Hutang', $kategoriList) || in_array('Piutang', $kategoriList))
        
        <div class="summary-box">
            <div class="summary-title">Ringkasan Hutang Piutang</div>
            <table style="border: none; margin-bottom: 0;">
                <tr style="border: none; background-color: transparent;">
                    <td style="border: none; padding: 4px 0;">Total Hutang (Sisa Tagihan)</td>
                    <td style="border: none; padding: 4px 0;" class="text-right text-danger">Rp {{ number_format($total_sisa_hutang ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border: none; background-color: transparent;">
                    <td style="border: none; padding: 4px 0;">Total Piutang (Sisa Tagihan)</td>
                    <td style="border: none; padding: 4px 0;" class="text-right text-primary">Rp {{ number_format($total_sisa_piutang ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if(in_array('Hutang', $kategoriList))
            <h4>Daftar Hutang Supplier</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Supplier</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Total Hutang</th>
                        <th class="text-right">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hutang ?? [] as $i => $h)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $h->contact->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($h->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $h->sisa <= 0 ? 'badge-lunas' : 'badge-belum' }}">
                                    {{ $h->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($h->sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada data hutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if(in_array('Piutang', $kategoriList))
            <h4>Daftar Piutang Customer</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Customer</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Total Piutang</th>
                        <th class="text-right">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutang ?? [] as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $p->contact->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $p->sisa <= 0 ? 'badge-lunas' : 'badge-belum' }}">
                                    {{ $p->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="text-right text-primary">Rp {{ number_format($p->sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada data piutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    @endif

    <div class="footer">
        Dicetak otomatis oleh Sistem Manajemen Produk TWINS pada {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
