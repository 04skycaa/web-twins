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
            <h4>Manajemen User</h4>
            <div class="header-actions">
                <button class="btn-primary-small" onclick="openModal('addModal')">
                    <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
                    Tambah User
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Outlet</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="text-bold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role == 'owner')
                                <span class="badge" style="background: #fef08a; color: #854d0e;">Owner</span>
                            @elseif($user->role == 'kepala_toko')
                                <span class="badge" style="background: #bae6fd; color: #0369a1;">Kepala Toko</span>
                            @else
                                <span class="badge" style="background: #e2e8f0; color: #475569;">Kasir</span>
                            @endif
                        </td>
                        <td>{{ $user->outlet ? $user->outlet->nama_outlet : '-' }}</td>
                        <td>
                            <div class="action-buttons-table">
                                <button class="btn-icon" onclick="openEditModal({{ json_encode($user) }})">
                                    <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                </button>
                                <button class="btn-icon text-danger" onclick="openDeleteModal({{ $user->id }})">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">Belum ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Tambah User Baru</h5>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="owner">Owner</option>
                            <option value="kepala_toko">Kepala Toko</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label>Penempatan Outlet</label>
                        <select name="outlet_id" class="form-control">
                            <option value="">Pusat / Tidak Ada</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->idoutlet }}">{{ $out->nama_outlet }}</option>
                            @endforeach
                        </select>
                    </div>
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
    <div class="modal-content">
        <div class="modal-header">
            <h5>Edit User</h5>
            <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="owner">Owner</option>
                            <option value="kepala_toko">Kepala Toko</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label>Penempatan Outlet</label>
                        <select name="outlet_id" id="edit_outlet" class="form-control">
                            <option value="">Pusat / Tidak Ada</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->idoutlet }}">{{ $out->nama_outlet }}</option>
                            @endforeach
                        </select>
                    </div>
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
            <h5>Hapus User</h5>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body text-center">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size: 50px; color: #ef4444; margin-bottom: 10px;"></iconify-icon>
            <p>Apakah Anda yakin ingin menghapus user ini?</p>
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
        document.getElementById('editForm').action = `/users/${data.id}`;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_role').value = data.role;
        document.getElementById('edit_outlet').value = data.outlet_id || '';
        openModal('editModal');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteForm').action = `/users/${id}`;
        openModal('deleteModal');
    }
</script>

<style>
    /* Styling Sama dengan Diskon Modals */
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
    .form-row { display: flex; gap: 15px; }
    .half { flex: 1; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; color: #475569; font-weight: 500; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: border 0.3s; box-sizing: border-box; }
    .form-control:focus { border-color: #0ea5e9; }
    .modal-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px; }
    
    .alert { padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .text-center { text-align: center; }
</style>
@endsection
