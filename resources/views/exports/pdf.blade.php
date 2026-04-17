<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0081C9; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #0081C9; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #EEF7FF; color: #0081C9; padding: 10px; border: 1px solid #5EB7EB; text-align: left; }
        td { padding: 8px; border: 1px solid #eee; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">TWINS SYSTEM</div>
        <div style="font-size: 16px;">{{ $title }}</div>
    </div>

    <div class="info">
        <strong>Tanggal Cetak:</strong> {{ $date }}<br>
        <strong>Outlet:</strong> {{ Auth::user()->store->nama ?? 'Semua Outlet' }}<br>
        <strong>Dicetak Oleh:</strong> {{ Auth::user()->name }}
    </div>

    <table>
        <thead>
            <tr>
                @if($tab == 'produk')
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Barcode</th>
                    <th>Kategori</th>
                    <th>Harga Modal</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                @elseif($tab == 'opname')
                    <th>No</th>
                    <th>No Ref</th>
                    <th>Tanggal</th>
                    <th>User</th>
                    <th>Outlet</th>
                    <th>Total Item</th>
                    <th>Selisih</th>
                    <th>Status</th>
                @elseif($tab == 'request')
                    <th>No</th>
                    <th>Produk</th>
                    <th>Pemohon</th>
                    <th>Outlet</th>
                    <th>Jumlah</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if($tab == 'produk')
                        <td>{{ $item->nama_produk }}</td>
                        <td>{{ $item->barcode }}</td>
                        <td>{{ $item->category->nama_category ?? '-' }}</td>
                        <td>Rp {{ number_format($item->harga_modal, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                        <td>{{ Auth::user()->isOwner() ? $item->stores->sum('stok') : ($item->stores->where('store_id', Auth::user()->store_id)->first()->stok ?? 0) }}</td>
                    @elseif($tab == 'opname')
                        <td>{{ $item->uuid }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->store->nama ?? '-' }}</td>
                        <td>{{ $item->total_items }}</td>
                        <td>{{ $item->total_selisih }}</td>
                        <td>{{ $item->status }}</td>
                    @elseif($tab == 'request')
                        <td>{{ $item->product->nama_produk ?? '-' }}</td>
                        <td>{{ $item->pemohon }}</td>
                        <td>{{ $item->store->nama ?? '-' }}</td>
                        <td>{{ $item->jumlah_minta }}</td>
                        <td>{{ $item->prioritas }}</td>
                        <td>{{ $item->status }}</td>
                        <td>-</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Manajemen Produk TWINS pada {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
