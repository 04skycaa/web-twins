@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="fitur-container">
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 15px; border-radius: 12px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 13px;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 15px; border-radius: 12px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 13px;">
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 15px; border-radius: 12px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 13px;">
        <ul style="margin:0; padding-left:15px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="left-actions-group">
            
        </div>
        <div class="right-actions">
            <button class="btn-action" onclick="openModal('addModal')">
                <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
                <span>Tambah User</span>
            </button>
        </div>
    </div>

    {{-- MAIN BOX --}}
    <div class="main-content-box">
        <div class="table-container">
            <table class="fitur-table">
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>OUTLET</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td style="font-weight: 600;">{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="status-badge" style="background: #E3F2FD; color: #1976D2; border: 1px solid #BBDEFB;">
                                {{ $user->operator ? $user->operator->nama : 'Tidak Ada Role' }}
                            </span>
                        </td>
                        <td>{{ $user->outlet ? $user->outlet->nama : '-' }}</td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($user)' data-outlet="{{ $user->outlet ? $user->outlet->nama : '-' }}" onclick="openViewModal(JSON.parse(this.dataset.item), this.dataset.outlet)">
                                    <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($user)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                    <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="openDeleteModal('{{ $user->uuid }}')">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999; padding: 40px;">Belum ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Tambah User Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST" id="addForm">
            @csrf
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 20px;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="add_password" class="form-control" required style="padding-right: 40px;">
                        <button type="button" onclick="togglePassword('add_password', 'add_eye')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                            <iconify-icon id="add_eye" icon="solar:eye-closed-bold"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->uuid }}">{{ $op->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Penempatan Outlet</label>
                        <select name="outlet_id" class="form-control">
                            <option value="">Pusat / Tidak Ada</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->uuid }}">{{ $out->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Hak Akses Fitur <small style="color: #888;">(Pilih fitur yang bisa diakses user ini)</small></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        @foreach($fiturs as $fitur)
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
                                <input type="checkbox" name="fitur[]" value="{{ $fitur->id }}" style="width: 16px; height: 16px;">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <iconify-icon icon="{{ $fitur->ikon }}" style="color: var(--primary-blue);"></iconify-icon>
                                    {{ $fitur->nama }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 20px;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password <small style="color: #888;">(Kosongkan jika tidak diubah)</small></label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="edit_password" class="form-control" style="padding-right: 40px;">
                        <button type="button" onclick="togglePassword('edit_password', 'edit_eye')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                            <iconify-icon id="edit_eye" icon="solar:eye-closed-bold"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->uuid }}">{{ $op->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Penempatan Outlet</label>
                        <select name="outlet_id" id="edit_outlet" class="form-control">
                            <option value="">Pusat / Tidak Ada</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->uuid }}">{{ $out->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Hak Akses Fitur <small style="color: #888;">(Pilih fitur yang bisa diakses user ini)</small></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        @foreach($fiturs as $fitur)
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
                                <input type="checkbox" name="fitur[]" id="edit_fitur_{{ $fitur->id }}" value="{{ $fitur->id }}" style="width: 16px; height: 16px;" class="edit-fitur-checkbox">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <iconify-icon icon="{{ $fitur->ikon }}" style="color: var(--primary-blue);"></iconify-icon>
                                    {{ $fitur->nama }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View -->
<div id="viewModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Detail User</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">Nama Lengkap</label>
                <div id="view_name" style="font-weight: 600; color: #334155;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">Email</label>
                <div id="view_email" style="font-weight: 600; color: #334155;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">No. HP</label>
                <div id="view_no_hp" style="font-weight: 600; color: #334155;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">Role</label>
                <div id="view_role" style="font-weight: 600; color: #334155;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">Penempatan Outlet</label>
                <div id="view_outlet" style="font-weight: 600; color: #334155;">-</div>
            </div>
        </div>
        <div style="padding: 0 20px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Hapus User</h3>
            <button class="close-modal" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px; text-align: center;">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size: 50px; color: #ef4444; margin-bottom: 10px;"></iconify-icon>
            <p style="color: #334155; font-size: 14px; margin-bottom: 0;">Apakah Anda yakin ingin menghapus user ini?</p>
        </div>
        <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
            <button type="button" class="btn-action" style="flex: 1; justify-content: center; background: #f1f5f9; color: #475569;" onclick="closeModal('deleteModal')">Batal</button>
            <form id="deleteForm" method="POST" style="flex: 1; display: flex;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-danger" style="flex: 1; justify-content: center;">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) { 
        if(id === 'addModal') {
            document.getElementById('addForm').reset();
            document.getElementById('add_password').type = 'password';
            document.getElementById('add_eye').setAttribute('icon', 'solar:eye-closed-bold');
        }
        document.getElementById(id).style.display = 'flex'; 
    }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('icon', 'solar:eye-bold');
        } else {
            input.type = 'password';
            icon.setAttribute('icon', 'solar:eye-closed-bold');
        }
    }

    function openViewModal(data, outletName) {
        document.getElementById('view_name').innerText = data.username;
        document.getElementById('view_email').innerText = data.email;
        document.getElementById('view_no_hp').innerText = data.no_hp || '-';
        document.getElementById('view_role').innerText = data.operator ? data.operator.nama : 'Tidak Ada Role';
        document.getElementById('view_outlet').innerText = outletName;
        openModal('viewModal');
    }

    function openEditModal(data) {
        document.getElementById('editForm').reset();
        document.getElementById('edit_password').type = 'password';
        document.getElementById('edit_eye').setAttribute('icon', 'solar:eye-closed-bold');
        document.getElementById('editForm').action = `/users/${data.uuid}`;
        document.getElementById('edit_name').value = data.username;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_no_hp').value = data.no_hp || '';
        document.getElementById('edit_role').value = data.operator_id || '';
        document.getElementById('edit_outlet').value = data.outlet_id || '';
        
        // Reset checkboxes
        document.querySelectorAll('.edit-fitur-checkbox').forEach(cb => cb.checked = false);
        // Check checkboxes if the user's operator has them
        if (data.operator && data.operator.fitur) {
            try {
                let userFeatures = JSON.parse(data.operator.fitur);
                userFeatures.forEach(fiturId => {
                    let cb = document.getElementById('edit_fitur_' + fiturId);
                    if(cb) cb.checked = true;
                });
            } catch(e) {}
        }

        openModal('editModal');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteForm').action = `/users/${id}`;
        openModal('deleteModal');
    }
</script>
@endsection
