@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="fitur-container">
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 15px; border-radius: 12px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 13px;">
        <iconify-icon icon="solar:check-circle-bold-duotone" style="vertical-align: middle; margin-right: 5px;"></iconify-icon>
        {{ session('success') }}
    </div>
    @endif

    {{-- HEADER --}}
    <div class="header-section" style="margin-bottom: 30px;">

    </div>

    {{-- TABS --}}
    <div class="tab-navigation">
        <button onclick="switchTab('customer')" id="tab-customer-btn" class="tab-pill active">
            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
            <span>Pelanggan</span>
        </button>
        <button onclick="switchTab('supplier')" id="tab-supplier-btn" class="tab-pill">
            <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
            <span>Supplier</span>
        </button>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="left-actions-group">
            <div class="search-wrapper">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau nomor HP...">
            </div>
            <div class="dropdown">
                <button type="button" class="btn-filter" style="width: auto; padding: 0 16px; border-radius: 50px; background: white; border: 2px solid var(--border-blue); color: var(--primary-blue); gap: 8px;" onclick="toggleDropdown(event)">
                    <iconify-icon icon="solar:sort-from-top-to-bottom-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    <span style="font-weight: 600; font-size: 14px;">Urutkan: {{ $sort == 'terbaru' ? 'Terbaru' : 'Terlama' }}</span>
                </button>
                <div class="dropdown-content">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'terbaru']) }}" class="{{ $sort == 'terbaru' ? 'active-dropdown-item' : '' }}">Terbaru</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'terlama']) }}" class="{{ $sort == 'terlama' ? 'active-dropdown-item' : '' }}">Terlama</a>
                </div>
            </div>
        </div>
        <button onclick="openAddModal()" class="btn-filter" style="width: auto; padding: 0 20px; border-radius: 50px; background: var(--primary-blue); color: white; gap: 8px;">
            <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 24px;"></iconify-icon>
            <span id="addBtnText" style="font-weight: 600; font-size: 14px;">Tambah Pelanggan</span>
        </button>
    </div>

    {{-- TABLE PELANGGAN --}}
    <div id="tab-customer" class="tab-content">
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>NAMA PELANGGAN</th>
                            <th>NOMOR HP</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggan as $p)
                        <tr class="contact-row" data-search="{{ strtolower($p->nama . ' ' . $p->no_hp) }}">
                            <td style="font-weight: 600; color: var(--text-dark);">{{ $p->nama }}</td>
                            <td style="color: var(--text-muted);">{{ $p->no_hp }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($p)' onclick="openViewModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($p)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $p->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444; border-color: #ffcccc;">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 50px; color: var(--text-muted);">Belum ada data pelanggan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABLE SUPPLIER --}}
    <div id="tab-supplier" class="tab-content" style="display: none;">
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>NAMA SUPPLIER</th>
                            <th>NOMOR HP</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier as $s)
                        <tr class="contact-row" data-search="{{ strtolower($s->nama . ' ' . $s->no_hp) }}">
                            <td style="font-weight: 600; color: var(--text-dark);">{{ $s->nama }}</td>
                            <td style="color: var(--text-muted);">{{ $s->no_hp }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($s)' onclick="openViewModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($s)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $s->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444; border-color: #ffcccc;">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 50px; color: var(--text-muted);">Belum ada data supplier.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Pelanggan</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('kontak.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="tipe" id="add_tipe" value="customer">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center; border-radius: 50px;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center; border-radius: 50px; background: var(--primary-blue);">Simpan Kontak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Kontak</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center; border-radius: 50px;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center; border-radius: 50px; background: var(--primary-blue);">Update Kontak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL VIEW --}}
<div id="viewModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Kontak</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="background: var(--light-blue); border-radius: 16px; padding: 20px; border: 1px solid var(--border-blue);">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Nama Lengkap</label>
                    <p id="view_nama" style="color: var(--text-dark); font-weight: 700; font-size: 16px; margin: 0;"></p>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Nomor HP</label>
                    <p id="view_no_hp" style="color: var(--text-dark); font-weight: 600; font-size: 15px; margin: 0;"></p>
                </div>
                <div>
                    <label style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Tipe Kontak</label>
                    <span id="view_tipe" class="status-badge" style="background: white; color: var(--primary-blue); border: 1px solid var(--primary-blue); font-weight: 700; text-transform: capitalize;"></span>
                </div>
            </div>
        </div>
        <div style="margin-top: 20px;">
            <button onclick="closeModal('viewModal')" class="btn-action" style="width: 100%; justify-content: center; background: white; color: var(--primary-blue); border: 1px solid var(--border-blue);">Tutup Detail</button>
        </div>
    </div>
</div>

<script>
    let activeTab = 'customer';

    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        document.querySelectorAll('.dropdown-content').forEach(el => {
            if (el !== dropdown) el.classList.remove('show');
        });
        dropdown.classList.toggle('show');
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.getElementById('tab-' + tab).style.display = 'block';

        document.querySelectorAll('.tab-pill').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + tab + '-btn').classList.add('active');

        // Update UI components
        document.getElementById('addBtnText').innerText = 'Tambah ' + (tab === 'customer' ? 'Pelanggan' : 'Supplier');
        document.getElementById('add_tipe').value = tab;
        document.getElementById('modalTitle').innerText = 'Tambah ' + (tab === 'customer' ? 'Pelanggan' : 'Supplier');
    }

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openAddModal() {
        openModal('addModal');
    }

    function openEditModal(data) {
        document.getElementById('editForm').action = `/kontak/${data.uuid}`;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_no_hp').value = data.no_hp;
        openModal('editModal');
    }

    function openViewModal(data) {
        document.getElementById('view_nama').innerText = data.nama;
        document.getElementById('view_no_hp').innerText = data.no_hp;
        document.getElementById('view_tipe').innerText = data.tipe;
        openModal('viewModal');
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.contact-row').forEach(row => {
            const searchText = row.getAttribute('data-search');
            if (searchText.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Form Processing Notification
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            Swal.fire({
                title: 'Memproses Data...',
                text: 'Harap tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        });
    });

    // Close modal on click outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection
