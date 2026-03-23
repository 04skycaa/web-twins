@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --bg-light: #f9fafb;
        --border-color: #e5e7eb;
        --text-main: #1f2937;
        --text-muted: #6b7280;
    }

    .app-container { 
        padding: 1.5rem; 
        font-family: 'Inter', sans-serif; 
        background: #fff; 
        min-height: 100vh; 
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-title h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; }

    .tab-nav {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .tab-btn {
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-muted);
        border: none;
        background: none;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }
    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .btn-custom {
        padding: 0.6rem 1.2rem;
        border-radius: 0.6rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-outline { background: white; border-color: #d1d5db; color: #374151; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-danger { background: var(--danger); color: white; }

    .search-container {
        position: relative;
        margin-bottom: 1rem;
        max-width: 350px;
    }
    .search-input {
        width: 100%;
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: 0.6rem;
        font-size: 0.875rem;
        outline: none;
    }
    .search-icon { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

    .card-table {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        background: var(--bg-light);
        color: var(--secondary);
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .data-table td { padding: 1rem 1.5rem; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        width: 100%;
        max-width: 550px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .detail-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
    .detail-value { font-size: 0.9rem; color: var(--text-main); font-weight: 600; margin-bottom: 0.5rem; }

    .form-group { margin-bottom: 1rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main); }
    .form-input { 
        width: 100%; 
        padding: 0.6rem; 
        border: 1px solid var(--border-color); 
        border-radius: 0.5rem; 
        outline: none;
    }
    .form-input:focus { 
        border-color: var(--primary); 
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); 
    }
</style>

<div class="app-container">
    <div class="page-header">
        <div class="page-title">
            <h2 id="current-title">Inventaris Produk</h2>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="exportToExcel()" class="btn-custom btn-success">
                <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon> Export Excel
            </button>
            <button onclick="openModal('addModal')" class="btn-custom btn-primary">
                Tambah Data
            </button>
        </div>
    </div>

    <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('products', 'Inventaris Produk')">Produk</button>
    <button class="tab-btn" onclick="switchTab('opname', 'Produk Opname')">Produk Opname</button>
    <button class="tab-btn" onclick="switchTab('request', 'Request Produk')">Request Produk</button>
</div>

<div class="search-container">
    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
    <input type="text" id="searchInput" class="search-input" placeholder="Cari data...">
</div>

<!-- tabel produk -->
<div id="tab-products" class="tab-content active">
    <div class="card-table">
        <table class="data-table" id="productTable">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($products as $product)
                <tr>
                    <td>
                        <div class="search-field" style="font-weight: 700;">{{ $product->nama_produk }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted)">{{ $product->sku }}</div>
                    </td>
                    <td>{{ $product->kategori }}</td>
                    <td>
                        @php
                            $isLowStock = $product->stok <= $product->minimal_stok;
                            $stockColor = $isLowStock ? 'var(--danger)' : 'inherit';
                        @endphp
                        <strong style="color: var(--dynamic-color); --dynamic-color: {{ $stockColor }}">
                            {{ $product->stok }} {{ $product->satuan }}
                        </strong>
                    </td>
                    <td>{{ $product->lokasi_rak ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 0.4rem;">
                            <button class="btn-custom btn-outline" 
                                    data-product="{{ json_encode($product) }}"
                                    onclick="handleView(this)">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                            <button class="btn-custom btn-outline" 
                                    style="color:var(--primary)" 
                                    data-product="{{ json_encode($product) }}"
                                    onclick="handleEdit(this)">
                                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                            </button>
                            <button class="btn-custom btn-outline" 
                                    style="color:var(--danger)" 
                                    onclick="confirmDelete('{{ $product->id }}', '{{ addslashes($product->nama_produk) }}')">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- tabel produk opname-->
<div id="tab-opname" class="tab-content">
    <div class="card-table">
        <table class="data-table" id="opnameTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Sistem</th>
                    <th>Fisik</th>
                    <th>Selisih</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($opnames as $opname)
                <tr>
                    <td>{{ $opname->tanggal_cek }}</td>
                    <td class="search-field">{{ $opname->petugas }}</td>
                    <td>{{ $opname->stok_sistem }}</td>
                    <td>{{ $opname->stok_fisik }}</td>
                    <td>
                        @php $diffColor = $opname->selisih < 0 ? 'var(--danger)' : 'var(--success)'; @endphp
                        <span style="font-weight:bold; color: var(--diff-color); --diff-color: {{ $diffColor }}">
                            {{ $opname->selisih }}
                        </span>
                    </td>
                    <td>{{ $opname->keterangan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- tabel request produk -->
<div id="tab-request" class="tab-content">
    <div class="card-table">
        <table class="data-table" id="requestTable">
            <thead>
                <tr>
                    <th>Pemohon</th>
                    <th>Jumlah</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($requests as $req)
                <tr>
                    <td class="search-field"><strong>{{ $req->pemohon }}</strong></td>
                    <td>{{ $req->jumlah_minta }}</td>
                    <td>
                        @php
                            $isHigh = $req->prioritas == 'Tinggi';
                            $prioBg = $isHigh ? '#fee2e2' : '#fef3c7';
                            $prioText = $isHigh ? '#991b1b' : '#92400e';
                        @endphp
                        <span class="status-badge" style="--bg-color: {{ $prioBg }}; --text-color: {{ $prioText }}">
                            {{ $req->prioritas }}
                        </span>
                    </td>
                    <td><strong>{{ $req->status }}</strong></td>
                    <td>{{ $req->alasan_permintaan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- modal detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Detail Produk</h3>
            <button onclick="closeModal('detailModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <div id="detailContent" class="detail-grid"></div>
        <button onclick="closeModal('detailModal')" class="btn-custom btn-outline" style="width:100%; margin-top:1.5rem; justify-content:center">Tutup</button>
    </div>
</div>

<!-- tambah data -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Tambah Produk Baru</h3>
            <button onclick="closeModal('addModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form action="/products" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-input" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" name="harga_beli" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" name="harga_jual" class="form-input">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="closeModal('addModal')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- edit data -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Edit Produk</h3>
            <button onclick="closeModal('editModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" id="edit_nama" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" id="edit_stok" class="form-input" required>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal('editModal')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Update Data</button>
            </div>
        </form>
    </div>
</div>

<!-- konfirmasi hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="color: var(--danger); font-size: 3rem; margin-bottom: 1rem;">
            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
        </div>
        <h3 id="delTitle">Hapus Data?</h3>
        <p id="delMsg" style="color: var(--text-muted); margin: 1rem 0; font-size: 0.875rem;"></p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal('deleteModal')" class="btn-custom btn-outline" style="flex:1; justify-content: center;">Batal</button>
                <button type="submit" class="btn-custom btn-danger" style="flex:1; justify-content: center;">Hapus Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- script -->
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>

<script>
    function openModal(id) { 
        const modal = document.getElementById(id);
        if(modal) modal.style.display = 'flex'; 
    }
    
    function closeModal(id) { 
        const modal = document.getElementById(id);
        if(modal) modal.style.display = 'none'; 
    }

    function switchTab(tabId, title) {
        document.getElementById('current-title').innerText = title;
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        if (window.event && window.event.currentTarget) {
            window.event.currentTarget.classList.add('active');
        }
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        const targetTab = document.getElementById('tab-' + tabId);
        if(targetTab) targetTab.classList.add('active');
        document.getElementById('searchInput').value = '';
        document.querySelectorAll('.table-body tr').forEach(row => row.style.display = '');
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const activeTab = document.querySelector('.tab-content.active');
        if(!activeTab) return;
        const rows = activeTab.querySelectorAll('.table-body tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(searchText) ? '' : 'none';
        });
    });
    
    function handleView(el) {
        const product = JSON.parse(el.getAttribute('data-product'));
        viewDetail(product);
    }

    function handleEdit(el) {
        const product = JSON.parse(el.getAttribute('data-product'));
        openEdit(product);
    }

    function viewDetail(product) {
        const content = document.getElementById('detailContent');
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
        content.innerHTML = `
            <div><div class="detail-label">Nama</div><div class="detail-value">${product.nama_produk}</div></div>
            <div><div class="detail-label">SKU</div><div class="detail-value">${product.sku}</div></div>
            <div><div class="detail-label">Harga Beli</div><div class="detail-value">${formatter.format(product.harga_beli)}</div></div>
            <div><div class="detail-label">Harga Jual</div><div class="detail-value">${formatter.format(product.harga_jual)}</div></div>
            <div><div class="detail-label">Stok</div><div class="detail-value">${product.stok} ${product.satuan}</div></div>
            <div><div class="detail-label">Lokasi</div><div class="detail-value">${product.lokasi_rak || '-'}</div></div>
        `;
        openModal('detailModal');
    }

    function openEdit(product) {
        document.getElementById('edit_nama').value = product.nama_produk;
        document.getElementById('edit_stok').value = product.stok;
        document.getElementById('editForm').action = `/products/${product.id}`;
        openModal('editModal');
    }

    function confirmDelete(id, name) {
        document.getElementById('delMsg').innerText = `Data "${name}" akan dihapus permanen.`;
        document.getElementById('deleteForm').action = `/products/${id}`;
        openModal('deleteModal');
    }

    function exportToExcel() {
        const activeTab = document.querySelector('.tab-content.active');
        if(!activeTab) return;
        const table = activeTab.querySelector('table');
        const title = document.getElementById('current-title').innerText;
        if (typeof XLSX === 'undefined') return;
        const wb = XLSX.utils.table_to_book(table, {sheet: title});
        XLSX.writeFile(wb, `${title.replace(/\s+/g, '_')}.xlsx`);
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) event.target.style.display = 'none';
    }
</script>
@endsection