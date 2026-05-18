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
        <strong>Outlet:</strong> {{ Auth::user()->outlet->nama ?? 'Semua Outlet' }}<br>
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
                    @if(request('sub_tab') == 'produk_rugi')
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Barcode</th>
                        <th>Outlet</th>
                        <th>Tanggal Opname</th>
                        <th>Stok Sistem</th>
                        <th>Stok Fisik</th>
                        <th>Selisih</th>
                        <th>Kerugian (Rp)</th>
                    @else
                        <th>No</th>
                        <th>No Ref</th>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Outlet</th>
                        <th>Total Item</th>
                        <th>Total Selisih</th>
                        <th>Potensi Kerugian (Rp)</th>
                        <th>Status</th>
                    @endif
                @elseif($tab == 'stok' || $tab == 'request')
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Barcode</th>
                    <th>Outlet</th>
                    <th>Stok</th>
                    <th>Kadaluarsa</th>
                    <th>Kategori</th>
                @elseif($tab == 'restok')
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Petugas</th>
                @elseif($tab == 'transfer')
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Dari</th>
                    <th>Tujuan</th>
                    <th>Status</th>
                    <th>Petugas</th>
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
                        @php
                            $selectedStoreId = request('store_id');
                            if (!Auth::user()->isOwner()) {
                                $selectedStoreId = Auth::user()->store_id;
                            }
                            if ($selectedStoreId && $selectedStoreId !== 'all') {
                                $stok = $item->stores->where('store_id', $selectedStoreId)->first()->stok ?? 0;
                            } else {
                                $stok = $item->stores->sum('stok');
                            }
                        @endphp
                        <td>{{ $stok }}</td>
                    @elseif($tab == 'opname')
                        @if(request('sub_tab') == 'produk_rugi')
                            @php
                                $modal = $item->product->harga_modal ?? 0;
                                $kerugian = abs($item->selisih * $modal);
                            @endphp
                            <td>{{ $item->product->nama_produk ?? '-' }}</td>
                            <td>{{ $item->product->barcode ?? '-' }}</td>
                            <td>{{ $item->opname->store->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->opname->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $item->stok_sistem }}</td>
                            <td>{{ $item->stok_fisik }}</td>
                            <td>{{ $item->selisih }}</td>
                            <td>Rp {{ number_format($kerugian, 0, ',', '.') }}</td>
                        @else
                            <td>{{ $item->uuid }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $item->user->name ?? $item->user->username ?? '-' }}</td>
                            <td>{{ $item->store->nama ?? '-' }}</td>
                            <td>{{ $item->total_items }}</td>
                            <td>{{ $item->total_selisih }}</td>
                            <td>Rp {{ number_format(abs($item->total_kerugian), 0, ',', '.') }}</td>
                            <td>{{ $item->status }}</td>
                        @endif
                    @elseif($tab == 'stok' || $tab == 'request')
                        <td>{{ $item->product->nama_produk ?? '-' }}</td>
                        <td>{{ $item->product->barcode ?? '-' }}</td>
                        <td>{{ $item->store->nama ?? '-' }}</td>
                        <td>{{ $item->stok }}</td>
                        <td>{{ $item->kadaluarsa ? \Carbon\Carbon::parse($item->kadaluarsa)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $item->product->category->nama_category ?? '-' }}</td>
                    @elseif($tab == 'restok')
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y H:i') }}</td>
                        <td>{{ $item->contact->nama ?? 'Umum' }}</td>
                        <td style="font-weight: bold;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td>{{ $item->bayar < $item->total ? 'Hutang' : 'Lunas' }}</td>
                        <td>{{ $item->user->name ?? $item->user->username ?? '-' }}</td>
                    @elseif($tab == 'transfer')
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y H:i') }}</td>
                        <td>{{ $item->store->nama ?? '-' }}</td>
                        <td>{{ $item->tujuanStore->nama ?? '-' }}</td>
                        <td>{{ $item->status ?: 'Pending' }}</td>
                        <td>{{ $item->user->username ?? '-' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Point of Sale Toko Bahan Kue Twins pada {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
