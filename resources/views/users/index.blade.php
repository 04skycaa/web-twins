@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="fitur-container">
    {{-- Alerts are now handled by SweetAlert2 at the bottom --}}


    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="left-actions-group">
            <div class="search-wrapper">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" id="userSearch" class="search-input" placeholder="Cari nama atau email..." onkeyup="filterUsers()">
            </div>

            <!-- Role Filter Dropdown -->
            <div class="dropdown">
                <button class="btn-filter" onclick="toggleFilterDropdown('roleDropdown', this)" title="Filter Role">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" style="font-size: 24px;"></iconify-icon>
                </button>
                <div id="roleDropdown" class="dropdown-content">
                    <a href="javascript:void(0)" onclick="setFilter('role', '', 'Semua Role', this)" class="active-dropdown-item">Semua Role</a>
                    @foreach($operators as $op)
                        <a href="javascript:void(0)" onclick="setFilter('role', '{{ $op->nama }}', '{{ $op->nama }}', this)">{{ $op->nama }}</a>
                    @endforeach
                </div>
            </div>

            <!-- Outlet Filter Dropdown -->
            <div class="dropdown">
                <button class="btn-filter" onclick="toggleFilterDropdown('outletDropdown', this)" title="Filter Outlet">
                    <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"></iconify-icon>
                </button>
                <div id="outletDropdown" class="dropdown-content">
                    <a href="javascript:void(0)" onclick="setFilter('outlet', '', 'Semua Outlet', this)" class="active-dropdown-item">Semua Outlet</a>
                    <a href="javascript:void(0)" onclick="setFilter('outlet', '-', 'Pusat', this)">Pusat</a>
                    @foreach($outlets as $out)
                        <a href="javascript:void(0)" onclick="setFilter('outlet', '{{ $out->nama }}', '{{ $out->nama }}', this)">{{ $out->nama }}</a>
                    @endforeach
                </div>
            </div>
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
                        <th>STATUS</th>
                        <th>LAST LOGIN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="user-row" 
                        data-name="{{ strtolower($user->username) }}" 
                        data-email="{{ strtolower($user->email) }}"
                        data-role="{{ $user->operator ? $user->operator->nama : 'Tidak Ada Role' }}"
                        data-outlet="{{ $user->outlet ? $user->outlet->nama : '-' }}">
                        <td style="font-weight: 600;">{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="status-badge" style="background: #E3F2FD; color: #1976D2; border: 1px solid #BBDEFB;">
                                {{ $user->operator ? $user->operator->nama : 'Tidak Ada Role' }}
                            </span>
                        </td>
                        <td>{{ $user->outlet ? $user->outlet->nama : '-' }}</td>
                        <td>
                            @if($user->status_aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td style="color: #64748b; font-size: 12px;">
                            {{ $user->last_login_at ? $user->last_login_at->translatedFormat('d M Y, H:i') : 'Belum pernah' }}
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($user)' data-outlet="{{ $user->outlet ? $user->outlet->nama : '-' }}" onclick="openViewModal(JSON.parse(this.dataset.item), this.dataset.outlet)" title="View Detail">
                                    <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($user)' onclick="openEditModal(JSON.parse(this.dataset.item))" title="Edit User">
                                    <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: {{ $user->status_aktif ? '#ef4444' : '#10b981' }};" onclick="toggleStatus('{{ $user->uuid }}', {{ $user->status_aktif ? 'true' : 'false' }})" title="{{ $user->status_aktif ? 'Suspend User' : 'Aktifkan User' }}">
                                    <iconify-icon icon="{{ $user->status_aktif ? 'solar:user-block-bold-duotone' : 'solar:user-check-bold-duotone' }}"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="openDeleteModal('{{ $user->uuid }}')" title="Hapus User">
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
                    <div style="margin-top: 8px; text-align: right;">
                        <a href="{{ route('password.request') }}" style="color: #f59e0b; font-size: 12px; text-decoration: none; display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-weight: 600;">
                            <iconify-icon icon="solar:key-minimalistic-bold-duotone"></iconify-icon>
                            Gunakan Fitur Reset Password
                        </a>
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
        window.currentEditingUuid = data.uuid;
        document.getElementById('editForm').reset();
        document.getElementById('edit_password').type = 'password';
        document.getElementById('edit_eye').setAttribute('icon', 'solar:eye-closed-bold');
        document.getElementById('editForm').action = `/users/${data.uuid}`;
        document.getElementById('edit_name').value = data.username;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_no_hp').value = data.no_hp || '';
        document.getElementById('edit_role').value = data.operator_id || '';
        document.getElementById('edit_outlet').value = data.store_id || ''; // Using store_id directly from JSON
        
        // Reset checkboxes
        document.querySelectorAll('.edit-fitur-checkbox').forEach(cb => cb.checked = false);
        
        // Check checkboxes if the user's operator has them
        if (data.operator && data.operator.fitur) {
            let userFeatures = data.operator.fitur;
            
            // If it's a string, try to parse it
            if (typeof userFeatures === 'string') {
                try {
                    userFeatures = JSON.parse(userFeatures);
                } catch(e) {
                    userFeatures = [];
                }
            }

            if (Array.isArray(userFeatures)) {
                userFeatures.forEach(fiturId => {
                    let cb = document.getElementById('edit_fitur_' + fiturId);
                    if(cb) cb.checked = true;
                });
            }
        }

        openModal('editModal');
    }

    function openDeleteModal(id) {
        Swal.fire({
            title: 'Hapus User?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/users/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function toggleStatus(id, isAktif) {
        const action = isAktif ? 'Suspend' : 'Aktifkan';
        Swal.fire({
            title: `${action} User?`,
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} user ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isAktif ? '#ef4444' : '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/users/${id}/toggle-status`;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    let currentRoleFilter = "";
    let currentOutletFilter = "";

    function toggleFilterDropdown(id, btn) {
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(el => {
            if (el.id !== id) el.classList.remove('show');
        });
        document.getElementById(id).classList.toggle('show');
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            }
        }
    }

    function setFilter(type, value, label, el) {
        if (type === 'role') currentRoleFilter = value;
        if (type === 'outlet') currentOutletFilter = value;

        // Update active state in dropdown
        el.closest('.dropdown-content').querySelectorAll('a').forEach(a => a.classList.remove('active-dropdown-item'));
        el.classList.add('active-dropdown-item');

        // Close dropdown
        el.closest('.dropdown-content').classList.remove('show');

        // Execute filtering
        filterUsers();
    }

    function filterUsers() {
        const search = document.getElementById('userSearch').value.toLowerCase();
        const role = currentRoleFilter;
        const outlet = currentOutletFilter;

        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const rowRole = row.dataset.role;
            const rowOutlet = row.dataset.outlet;

            const matchesSearch = name.includes(search) || email.includes(search);
            const matchesRole = role === "" || rowRole === role;
            const matchesOutlet = outlet === "" || rowOutlet === outlet;

            if (matchesSearch && matchesRole && matchesOutlet) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // SweetAlert2 Notifications

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}",
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `<ul style="text-align: left; font-size: 14px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                       </ul>`,
            });
        @endif
    });
</script>
@endsection
