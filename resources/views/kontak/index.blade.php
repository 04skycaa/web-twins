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
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue);" data-item='@json($p)' onclick="openViewModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #8B5CF6;" data-item='@json($p)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $p->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444;">
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
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue);" data-item='@json($s)' onclick="openViewModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #8B5CF6;" data-item='@json($s)' onclick="openEditModal(JSON.parse(this.dataset.item))">
                                        <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $s->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444;">
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
<div id="addModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div class="modal-content" style="background: white; width: 100%; max-width: 480px; border-radius: 24px; padding: 32px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 id="modalTitle" style="color: var(--text-dark); margin: 0; font-size: 20px; font-weight: 700;">Tambah Pelanggan</h3>
            <button onclick="closeModal('addModal')" style="background: var(--light-blue); border: none; color: var(--primary-blue); cursor: pointer; font-size: 24px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
            </button>
        </div>
        <form action="{{ route('kontak.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" id="add_tipe" value="customer">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-size: 14px; font-weight: 600;">Nama Lengkap</label>
                <input type="text" name="nama" class="search-input" placeholder="Contoh: Budi Santoso" required style="padding-left: 20px;">
            </div>
            <div style="margin-bottom: 28px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-size: 14px; font-weight: 600;">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" class="search-input" placeholder="Contoh: 08123456789" required style="padding-left: 20px;">
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="closeModal('addModal')" style="flex: 1; padding: 14px; border-radius: 50px; border: 2px solid var(--border-blue); background: white; color: var(--primary-blue); font-weight: 700; cursor: pointer; transition: all 0.3s;">Batal</button>
                <button type="submit" style="flex: 1; padding: 14px; border-radius: 50px; border: none; background: var(--primary-blue); color: white; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(0, 129, 201, 0.2);">Simpan Kontak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="editModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div class="modal-content" style="background: white; width: 100%; max-width: 480px; border-radius: 24px; padding: 32px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="color: var(--text-dark); margin: 0; font-size: 20px; font-weight: 700;">Edit Kontak</h3>
            <button onclick="closeModal('editModal')" style="background: var(--light-blue); border: none; color: var(--primary-blue); cursor: pointer; font-size: 24px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
            </button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-size: 14px; font-weight: 600;">Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" class="search-input" required style="padding-left: 20px;">
            </div>
            <div style="margin-bottom: 28px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-size: 14px; font-weight: 600;">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" id="edit_no_hp" class="search-input" required style="padding-left: 20px;">
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="closeModal('editModal')" style="flex: 1; padding: 14px; border-radius: 50px; border: 2px solid var(--border-blue); background: white; color: var(--primary-blue); font-weight: 700; cursor: pointer; transition: all 0.3s;">Batal</button>
                <button type="submit" style="flex: 1; padding: 14px; border-radius: 50px; border: none; background: #8B5CF6; color: white; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);">Update Kontak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL VIEW --}}
<div id="viewModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div class="modal-content" style="background: white; width: 100%; max-width: 480px; border-radius: 24px; padding: 32px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="color: var(--text-dark); margin: 0; font-size: 20px; font-weight: 700;">Detail Kontak</h3>
            <button onclick="closeModal('viewModal')" style="background: var(--light-blue); border: none; color: var(--primary-blue); cursor: pointer; font-size: 24px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
            </button>
        </div>
        <div style="background: var(--light-blue); border-radius: 20px; padding: 24px; border: 2px solid var(--border-blue);">
            <div style="margin-bottom: 18px;">
                <label style="display: block; color: var(--primary-blue); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; margin-bottom: 6px;">Nama Lengkap</label>
                <p id="view_nama" style="color: var(--text-dark); font-weight: 700; font-size: 18px; margin: 0;"></p>
            </div>
            <div style="margin-bottom: 18px;">
                <label style="display: block; color: var(--primary-blue); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; margin-bottom: 6px;">Nomor HP</label>
                <p id="view_no_hp" style="color: var(--text-dark); font-weight: 600; font-size: 16px; margin: 0;"></p>
            </div>
            <div>
                <label style="display: block; color: var(--primary-blue); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; margin-bottom: 6px;">Tipe Kontak</label>
                <span id="view_tipe" style="background: white; color: var(--primary-blue); border: 2px solid var(--primary-blue); padding: 4px 14px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: capitalize; display: inline-block;"></span>
            </div>
        </div>
        <button onclick="closeModal('viewModal')" style="width: 100%; margin-top: 28px; padding: 14px; border-radius: 50px; border: 2px solid var(--border-blue); background: white; color: var(--primary-blue); font-weight: 700; cursor: pointer; transition: all 0.3s;">Tutup Detail</button>
    </div>
</div>

<script>
    let activeTab = 'customer';

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

    // Close modal on click outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection
