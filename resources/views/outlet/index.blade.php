@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="fitur-container">
    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="left-actions-group">
            <div class="search-wrapper">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" id="outletSearch" class="search-input" placeholder="Cari nama atau alamat..." onkeyup="filterOutlets()">
            </div>
        </div>
        <div class="right-actions">
            <button class="btn-action" onclick="openModal('addModal')">
                <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                <span>Tambah Outlet</span>
            </button>
        </div>
    </div>

    {{-- MAIN BOX --}}
    <div class="main-content-box">
        <div class="table-container">
            <table class="fitur-table">
                <thead>
                    <tr>
                        <th>NAMA OUTLET</th>
                        <th>ALAMAT</th>
                        <th>NO. TELP</th>
                        <th>JAM BUKA</th>
                        <th>RATING</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outlets as $outlet)
                    <tr class="outlet-row" data-name="{{ strtolower($outlet->nama) }}" data-address="{{ strtolower($outlet->alamat) }}">
                        <td style="font-weight: 600;">{{ $outlet->nama }}</td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $outlet->alamat ?? '-' }}</td>
                        <td>{{ $outlet->notelp ?? '-' }}</td>
                        <td>
                            <span class="status-badge" style="background: rgba(14, 165, 233, 0.1); color: var(--accent-purple); border: 1px solid rgba(14, 165, 233, 0.2);">
                                {{ $outlet->jam_buka ?? '08.00 - 23.59' }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 4px; color: #f59e0b; font-weight: 700;">
                                <iconify-icon icon="solar:star-bold"></iconify-icon>
                                {{ number_format($outlet->rating, 1) }}
                            </div>
                        </td>
                        <td>
                            @if($outlet->status_aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($outlet)' onclick="openViewModal(JSON.parse(this.dataset.item))" title="View Detail">
                                    <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item='@json($outlet)' onclick="openEditModal(JSON.parse(this.dataset.item))" title="Edit Outlet">
                                    <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: {{ $outlet->status_aktif ? '#ef4444' : '#10b981' }};" onclick="toggleStatus('{{ $outlet->uuid }}', {{ $outlet->status_aktif ? 'true' : 'false' }})" title="{{ $outlet->status_aktif ? 'Nonaktifkan Outlet' : 'Aktifkan Outlet' }}">
                                    <iconify-icon icon="{{ $outlet->status_aktif ? 'solar:shop-2-bold-duotone' : 'solar:shop-bold-duotone' }}"></iconify-icon>
                                </button>
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="openDeleteModal('{{ $outlet->uuid }}')" title="Hapus Outlet">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999; padding: 40px;">Belum ada data outlet</td>
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
            <h3>Tambah Outlet Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('outlet.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: TWINS Bakery Pusat" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Jl. Raya No. 123..."></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" class="form-control" placeholder="08123456789">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" class="form-control" placeholder="Contoh: 08.00 - 22.00" value="08.00 - 23.59">
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
            <h3>Edit Outlet</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" id="edit_alamat" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" id="edit_notelp" class="form-control">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" id="edit_jam_buka" class="form-control">
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
            <h3>Detail Outlet</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">NAMA OUTLET</label>
                <div id="view_nama" style="font-weight: 600; color: #334155; font-size: 16px;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">ALAMAT</label>
                <div id="view_alamat" style="font-weight: 500; color: #334155;">-</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">NO. TELP</label>
                    <div id="view_notelp" style="font-weight: 600; color: #334155;">-</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">JAM OPERASIONAL</label>
                    <div id="view_jam_buka" style="font-weight: 600; color: #334155;">-</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">RATING</label>
                    <div id="view_rating" style="font-weight: 700; color: #f59e0b; display: flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="solar:star-bold"></iconify-icon>
                        <span>-</span>
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">STATUS</label>
                    <div id="view_status">-</div>
                </div>
            </div>
        </div>
        <div style="padding: 0 20px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openViewModal(data) {
        document.getElementById('view_nama').innerText = data.nama;
        document.getElementById('view_alamat').innerText = data.alamat || '-';
        document.getElementById('view_notelp').innerText = data.notelp || '-';
        document.getElementById('view_jam_buka').innerText = data.jam_buka || '-';
        document.getElementById('view_rating').querySelector('span').innerText = parseFloat(data.rating || 0).toFixed(1);
        
        const statusEl = document.getElementById('view_status');
        if (data.status_aktif) {
            statusEl.innerHTML = '<span class="status-badge status-active">Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive">Nonaktif</span>';
        }
        
        openModal('viewModal');
    }

    function openEditModal(data) {
        document.getElementById('editForm').action = `/outlet/${data.uuid}`;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_alamat').value = data.alamat || '';
        document.getElementById('edit_notelp').value = data.notelp || '';
        document.getElementById('edit_jam_buka').value = data.jam_buka || '';
        openModal('editModal');
    }

    function toggleStatus(id, isAktif) {
        const action = isAktif ? 'Nonaktifkan' : 'Aktifkan';
        Swal.fire({
            title: `${action} Outlet?`,
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} outlet ini?`,
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
                form.action = `/outlet/${id}/toggle-status`;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function openDeleteModal(id) {
        Swal.fire({
            title: 'Hapus Outlet?',
            text: "Data outlet dan relasi terkait akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/outlet/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function filterOutlets() {
        const search = document.getElementById('outletSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.outlet-row');
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const address = row.dataset.address;
            if (name.includes(search) || address.includes(search)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Oops...', text: "{{ session('error') }}" });
        @endif
    });
</script>
@endsection
