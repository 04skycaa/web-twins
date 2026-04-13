@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0; padding-left:15px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="content-card">
        <div class="card-header">
            <h4>Operasional Outlet</h4>
            <div class="header-actions">
                <button class="btn-primary-small" onclick="openModal('addModal')">
                    <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                    Tambah Outlet
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Outlet</th>
                        <th>Alamat</th>
                        <th>Total User/Karyawan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outlets as $out)
                    <tr>
                        <td class="text-bold">{{ $out['kode_outlet'] }}</td>
                        <td class="text-bold text-success">{{ $out['nama_outlet'] }}</td>
                        <td>{{ $out['alamat'] ?: '-' }}</td>
                        <td>
                            @php
                                $count = \App\Models\User::where('outlet_id', $out['idoutlet'])->count();
                            @endphp
                            <span class="badge" style="background: #e0f2fe; color: #0284c7;">{{ $count }} User</span>
                        </td>
                        <td>
                            <div class="action-buttons-table">
                                <button class="btn-icon" onclick="openEditModal({{ json_encode($out) }})">
                                    <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                </button>
                                <button class="btn-icon text-danger" onclick="openDeleteModal({{ $out['idoutlet'] }})">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">Belum ada data outlet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h5>Tambah Outlet Baru</h5>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('outlet.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama_outlet" class="form-control" required placeholder="Contoh: SweetBake Cab. A">
                </div>
                <div class="form-group">
                    <label>Kode Outlet (Opsional)</label>
                    <input type="text" name="kode_outlet" class="form-control" placeholder="Contoh: OTL002">
                </div>
                <div class="form-group">
                    <label>Alamat (Opsional)</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h5>Edit Outlet</h5>
            <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama_outlet" id="edit_nama_outlet" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat (Opsional)</label>
                    <textarea name="alamat" id="edit_alamat" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h5>Hapus Outlet</h5>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body text-center">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size: 50px; color: #ef4444; margin-bottom: 10px;"></iconify-icon>
            <p>Apakah Anda yakin ingin menghapus outlet ini?</p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Batal</button>
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    function openEditModal(data) {
        document.getElementById('editForm').action = `/outlet/${data.idoutlet}`;
        document.getElementById('edit_nama_outlet').value = data.nama_outlet;
        document.getElementById('edit_alamat').value = data.alamat || '';
        openModal('editModal');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteForm').action = `/outlet/${id}`;
        openModal('deleteModal');
    }
</script>

<style>
    /* Mengikuti Style yang Sama */
    .page-wrapper { padding: 20px; display: flex; flex-direction: column; }
    .content-card { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .card-header h4 { margin: 0; font-size: 16px; color: #1e293b; }
    
    .table-responsive { overflow-x: auto; }
    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 13px; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
    .custom-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
    
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .text-bold { font-weight: 600; }
    .text-success { color: #0284c7; }

    .action-buttons-table { display: flex; gap: 8px; }
    .btn-icon, .btn-primary-small, .btn-secondary, .btn-primary, .btn-danger {
        border: none; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 13px; font-weight: 600;
    }
    .btn-icon { background: #f1f5f9; width: 32px; height: 32px; color: #64748b; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
    .btn-icon:hover { background: #e0f2fe; color: #0ea5e9; }
    .btn-icon.text-danger:hover { background: #fee2e2; color: #ef4444; }
    
    .btn-primary-small { display: flex; align-items: center; gap: 6px; background: #0ea5e9; color: white; padding: 8px 16px; }
    .btn-primary-small:hover { opacity: 0.9; }

    .btn-secondary { background: #f1f5f9; color: #475569; padding: 8px 16px; }
    .btn-primary { background: #0ea5e9; color: white; padding: 8px 16px; }
    .btn-danger { background: #ef4444; color: white; padding: 8px 16px; }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-primary:hover, .btn-danger:hover { opacity: 0.9; }

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
    .modal-overlay.show { opacity: 1; visibility: visible; }
    .modal-content { background: #fff; width: 100%; max-width: 500px; border-radius: 12px; transform: translateY(-20px); transition: all 0.3s ease; }
    .modal-overlay.show .modal-content { transform: translateY(0); }
    .modal-sm { max-width: 400px; }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #f1f5f9; }
    .modal-header h5 { margin: 0; font-size: 16px; color: #1e293b; }
    .close-btn { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; }
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; color: #475569; font-weight: 500; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: border 0.3s; box-sizing: border-box; resize: vertical; }
    .form-control:focus { border-color: #0ea5e9; }
    .modal-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px; }
    
    .alert { padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .text-center { text-align: center; }
</style>
@endsection
