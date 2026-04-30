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
    <div class="header-section" style="margin-bottom: 20px;">
    </div>

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

    {{-- QUICK STATS --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 20px; border: 2px solid var(--border-blue); display: flex; align-items: center; gap: 15px; transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--light-blue); display: flex; align-items: center; justify-content: center; color: var(--primary-blue);">
                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" style="font-size: 30px;"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Pelanggan</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-dark);">{{ number_format($totalPelanggan) }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Orang</span></div>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 20px; border: 2px solid #bbf7d0; display: flex; align-items: center; gap: 15px; transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #dcfce7; display: flex; align-items: center; justify-content: center; color: #15803d;">
                <iconify-icon icon="solar:chart-bold-duotone" style="font-size: 30px;"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Aktif Bulan Ini</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-dark);">+{{ number_format($aktifBulanIni) }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Pelanggan</span></div>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 20px; border: 2px solid #fed7aa; display: flex; align-items: center; gap: 15px; transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #fff7ed; display: flex; align-items: center; justify-content: center; color: #c2410c;">
                <iconify-icon icon="solar:crown-bold-duotone" style="font-size: 30px;"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Top Spender</div>
                <div style="font-size: 16px; font-weight: 800; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $topSpender ? ($topSpender->matching_username ?? $topSpender->nama) : '-' }}</div>
                <div style="font-size: 11px; color: #c2410c; font-weight: 700;">{{ $topSpender ? $topSpender->total_transaksi : 0 }} Transaksi</div>
            </div>
        </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="left-actions-group">
            <div class="search-wrapper">
                <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau nomor HP..." aria-label="Cari nama atau nomor HP">
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

    {{-- BULK ACTION BAR --}}
    <div id="bulk-action-bar" style="display: none; background: #fee2e2; border: 1px solid #fecaca; border-radius: 15px; padding: 12px 20px; margin-bottom: 15px; align-items: center; justify-content: space-between; animation: slideIn 0.3s ease;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <iconify-icon icon="solar:info-circle-bold-duotone" style="color: #ef4444; font-size: 24px;"></iconify-icon>
            <span style="font-weight: 600; color: #b91c1c;"><span id="selected-count">0</span> Kontak Terpilih</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="openBroadcastModal()" class="btn-action" style="padding: 8px 16px; font-size: 13px; background: #22c55e; color: white; border: none;">
                <iconify-icon icon="solar:whatsapp-bold-duotone"></iconify-icon>
                Siaran WA
            </button>
            <button onclick="bulkDelete()" class="btn-action btn-danger" style="padding: 8px 16px; font-size: 13px;">
                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                Hapus Terpilih
            </button>
        </div>
    </div>

    {{-- TABLE PELANGGAN --}}
    <div id="tab-customer" class="tab-content">
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="checkAllCustomer" style="width: 18px; height: 18px; cursor: pointer;" aria-label="Pilih semua pelanggan">
                            </th>
                            <th>NAMA PELANGGAN</th>
                            <th>NOMOR HP</th>
                            <th style="text-align: center;">TOTAL TRANSAKSI</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggan as $p)
                        <tr class="contact-row" data-search="{{ strtolower(($p->matching_username ?? $p->nama) . ' ' . $p->no_hp) }}">
                            <td style="text-align: center;">
                                <input type="checkbox" class="customer-checkbox" value="{{ $p->uuid }}" style="width: 18px; height: 18px; cursor: pointer;" aria-label="Pilih pelanggan {{ $p->nama }}">
                            </td>
                            <td style="font-weight: 600; color: var(--text-dark);">
                                {{ $p->nama }}
                                @if($p->matching_email)
                                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 400; margin-top: 2px;">
                                        <iconify-icon icon="solar:letter-bold-duotone" style="vertical-align: middle;"></iconify-icon>
                                        {{ $p->matching_email }}
                                    </div>
                                @endif
                            </td>
                            <td style="color: var(--text-muted);">{{ $p->no_hp }}</td>
                            <td style="text-align: center;">
                                @if($p->total_transaksi > 0)
                                    <span class="status-badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-weight: 700;">
                                        {{ $p->total_transaksi }}x
                                    </span>
                                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                                        Terhubung: {{ $p->matching_username ?? ($p->user->username ?? 'Ya') }}
                                    </div>
                                @else
                                    <span class="status-badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 700;">
                                        0x
                                    </span>
                                    <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Tidak ada transaksi</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $p->no_hp)) }}" target="_blank" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #25d366; border-color: #dcfce7; background: #f0fff4; display: flex; align-items: center; justify-content: center; text-decoration: none;" title="WhatsApp">
                                        <iconify-icon icon="ic:baseline-whatsapp" style="font-size: 20px;"></iconify-icon>
                                    </a>
                                    <a href="tel:{{ $p->no_hp }}" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue); background: var(--light-blue); display: flex; align-items: center; justify-content: center; text-decoration: none;" title="Telepon">
                                        <iconify-icon icon="solar:phone-calling-bold-duotone" style="font-size: 20px;"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($p)' onclick="openViewModal(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($p)' onclick="openEditModal(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $p->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444; border-color: #ffcccc;" title="Hapus">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px; color: var(--text-muted);">Belum ada data pelanggan.</td>
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
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $s->no_hp)) }}" target="_blank" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #25d366; border-color: #dcfce7; background: #f0fff4; display: flex; align-items: center; justify-content: center; text-decoration: none;" title="WhatsApp">
                                        <iconify-icon icon="ic:baseline-whatsapp" style="font-size: 20px;"></iconify-icon>
                                    </a>
                                    <a href="tel:{{ $s->no_hp }}" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue); background: var(--light-blue); display: flex; align-items: center; justify-content: center; text-decoration: none;" title="Telepon">
                                        <iconify-icon icon="solar:phone-calling-bold-duotone" style="font-size: 20px;"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($s)' onclick="openViewModal(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button type="button" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: var(--primary-blue); border-color: var(--border-blue);" data-item='@json($s)' onclick="openEditModal(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <form action="{{ route('kontak.destroy', $s->uuid) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kontak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter" style="width: 36px; height: 36px; border-radius: 10px; color: #ef4444; border-color: #ffcccc;" title="Hapus">
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

    {{-- MODAL BROADCAST --}}
    <div id="modalBroadcast" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 45px; height: 45px; border-radius: 12px; background: #dcfce7; display: flex; align-items: center; justify-content: center; color: #15803d;">
                        <iconify-icon icon="solar:whatsapp-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </div>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin: 0;">Siaran WhatsApp</h2>
                        <p style="font-size: 12px; color: var(--text-muted); margin: 0;"><span id="broadcast-count">0</span> Kontak terpilih</p>
                    </div>
                </div>
                <button onclick="closeModal('modalBroadcast')" class="close-btn" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div style="margin-bottom: 20px;">
                    <label for="broadcast-message" style="display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">Pesan Siaran</label>
                    <textarea id="broadcast-message" style="width: 100%; height: 150px; padding: 12px; border: 2px solid var(--border-blue); border-radius: 12px; font-size: 14px; resize: none;" placeholder="Ketik pesan Anda di sini..."></textarea>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Tips: Pesan akan dikirim secara berurutan ke semua nomor yang Anda centang.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="closeModal('modalBroadcast')" class="btn-action btn-danger" style="flex: 1; justify-content: center; border-radius: 50px;">Batal</button>
                    <button type="button" onclick="startBroadcast()" class="btn-action" style="flex: 2; justify-content: center; border-radius: 50px; background: #22c55e;">
                        <iconify-icon icon="solar:send-square-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        Kirim Sekarang
                    </button>
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
                    <label for="add_nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="add_nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label for="add_no_hp">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" id="add_no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
                </div>
                <div class="form-group" id="add_user_field">
                    <label for="add_user_id">Hubungkan ke Akun Pelanggan (Opsional)</label>
                    <select name="user_id" id="add_user_id" class="form-control">
                        <option value="">-- Tidak Terhubung --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->uuid }}">{{ $user->username }} ({{ $user->no_hp }})</option>
                        @endforeach
                    </select>
                    <small style="color: var(--text-muted); font-size: 11px;">Pilih akun jika kontak ini memiliki akun di aplikasi untuk menghitung total transaksi.</small>
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
                    <label for="edit_nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_no_hp">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                </div>
                <div class="form-group" id="edit_user_field">
                    <label for="edit_user_id">Hubungkan ke Akun Pelanggan (Opsional)</label>
                    <select name="user_id" id="edit_user_id" class="form-control">
                        <option value="">-- Tidak Terhubung --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->uuid }}">{{ $user->username }} ({{ $user->no_hp }})</option>
                        @endforeach
                    </select>
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
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
            <div style="background: var(--light-blue); border-radius: 16px; padding: 20px; border: 1px solid var(--border-blue); margin-bottom: 20px;">
                <div style="margin-bottom: 15px;">
                    <span style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Nama Lengkap</span>
                    <p id="view_nama" style="color: var(--text-dark); font-weight: 700; font-size: 16px; margin: 0;"></p>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Nomor HP</span>
                    <p id="view_no_hp" style="color: var(--text-dark); font-weight: 600; font-size: 15px; margin: 0;"></p>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Akun Terhubung</span>
                    <p id="view_username" style="color: var(--text-dark); font-weight: 600; font-size: 15px; margin: 0;"></p>
                </div>
                <div id="view_stats_row" style="margin-bottom: 15px;">
                    <span style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Total Transaksi</span>
                    <p id="view_total_transaksi" style="color: var(--text-dark); font-weight: 700; font-size: 15px; margin: 0;"></p>
                </div>
                <div style="margin-bottom: 0;">
                    <span style="display: block; color: var(--primary-blue); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px;">Tipe Kontak</span>
                    <span id="view_tipe" class="status-badge" style="background: white; color: var(--primary-blue); border: 1px solid var(--primary-blue); font-weight: 700; text-transform: capitalize;"></span>
                </div>
            </div>

            <div id="transaction_history_section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="solar:bill-list-bold-duotone" style="color: var(--primary-blue); font-size: 20px;"></iconify-icon>
                        Riwayat Transaksi
                    </h4>
                    <span id="transaction_count_badge" style="font-size: 11px; background: var(--light-blue); color: var(--primary-blue); padding: 2px 10px; border-radius: 50px; font-weight: 700;">0 Pesanan</span>
                </div>
                <div id="transaction_list" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Transaksi akan muncul di sini -->
                </div>
            </div>

            <div style="margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <a id="view_wa_btn" href="#" target="_blank" class="btn-action" style="justify-content: center; background: #25d366; color: white; border: none; border-radius: 15px; text-decoration: none; padding: 12px;">
                    <iconify-icon icon="ic:baseline-whatsapp" style="font-size: 20px;"></iconify-icon>
                    <span>WhatsApp</span>
                </a>
                <a id="view_call_btn" href="#" class="btn-action" style="justify-content: center; background: var(--primary-blue); color: white; border: none; border-radius: 15px; text-decoration: none; padding: 12px;">
                    <iconify-icon icon="solar:phone-calling-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    <span>Telepon</span>
                </a>
            </div>
        </div>
        <div style="padding: 15px 20px; border-top: 1px solid #f1f5f9;">
            <button onclick="closeModal('viewModal')" class="btn-action" style="width: 100%; justify-content: center; background: #f1f5f9; color: #64748b; border: none; border-radius: 50px; font-weight: 700;">Tutup Detail</button>
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
        if(tab === 'pesanan') {
            document.querySelector('.action-bar').style.display = 'none';
        } else {
            document.querySelector('.action-bar').style.display = 'flex';
            document.getElementById('addBtnText').innerText = 'Tambah ' + (tab === 'customer' ? 'Pelanggan' : 'Supplier');
            document.getElementById('add_tipe').value = tab;
            document.getElementById('modalTitle').innerText = 'Tambah ' + (tab === 'customer' ? 'Pelanggan' : 'Supplier');
        }

        // Toggle user field visibility
        if(tab !== 'pesanan') {
            document.getElementById('add_user_field').style.display = tab === 'customer' ? 'block' : 'none';
            document.getElementById('edit_user_field').style.display = tab === 'customer' ? 'block' : 'none';
        }
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
        document.getElementById('edit_user_id').value = data.user_id || '';
        
        // Show/hide user field based on current data type if needed, 
        // but here it's already handled by activeTab in some way or we can just check data.tipe
        document.getElementById('edit_user_field').style.display = data.tipe === 'customer' ? 'block' : 'none';
        
        openModal('editModal');
    }

    function openViewModal(data) {
        document.getElementById('view_nama').innerText = data.nama;
        document.getElementById('view_no_hp').innerText = data.no_hp;
        document.getElementById('view_tipe').innerText = data.tipe;
        document.getElementById('view_username').innerText = data.user ? data.user.username : 'Belum terhubung ke akun';

        const statsRow = document.getElementById('view_stats_row');
        if (data.tipe === 'customer') {
            statsRow.style.display = 'block';
            document.getElementById('view_total_transaksi').innerText = (data.total_transaksi || 0) + ' Kali Belanja';
        } else {
            statsRow.style.display = 'none';
        }

        // WA Link
        let phone = data.no_hp.replace(/[^0-9]/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }
        document.getElementById('view_wa_btn').href = `https://wa.me/${phone}`;
        document.getElementById('view_call_btn').href = `tel:${data.no_hp}`;

        // Populate Transactions
        const transactionList = document.getElementById('transaction_list');
        const transactionSection = document.getElementById('transaction_history_section');
        const countBadge = document.getElementById('transaction_count_badge');
        transactionList.innerHTML = '';

        if (data.payment_orders && data.payment_orders.length > 0) {
            transactionSection.style.display = 'block';
            countBadge.innerText = data.payment_orders.length + ' Pesanan';
            data.payment_orders.forEach(order => {
                const accordionItem = document.createElement('div');
                accordionItem.style.cssText = 'background: white; border-radius: 12px; border: 1px solid #f1f5f9; margin-bottom: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02);';
                
                const statusColor = {
                    'pending': '#f59e0b',
                    'success': '#10b981',
                    'paid': '#10b981',
                    'settlement': '#10b981',
                    'failed': '#ef4444',
                    'expired': '#6b7280'
                }[order.payment_status] || '#6b7280';

                const orderDate = new Date(order.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'});

                accordionItem.innerHTML = `
                    <div class="order-header" onclick="toggleOrderDetails(this)" style="padding: 12px 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <iconify-icon icon="solar:alt-arrow-right-bold-duotone" class="arrow-icon" style="color: #94a3b8; transition: transform 0.3s;"></iconify-icon>
                            <div>
                                <div style="font-weight: 800; color: var(--primary-blue); font-size: 13px;">#${order.order_code}</div>
                                <div style="font-size: 10px; color: var(--text-muted);">${orderDate}</div>
                            </div>
                        </div>
                        <span style="font-size: 9px; font-weight: 800; padding: 4px 8px; border-radius: 50px; background: ${statusColor}15; color: ${statusColor}; border: 1px solid ${statusColor}30; text-transform: uppercase;">
                            ${order.payment_status}
                        </span>
                    </div>
                    <div class="order-details" style="display: none; padding: 0 15px 15px; border-top: 1px dashed #f1f5f9; animation: slideDown 0.3s ease;">
                        <div style="padding-top: 15px;">
                            <div style="background: #f8fafc; border-radius: 10px; padding: 10px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <iconify-icon icon="solar:user-bold-duotone" style="color: var(--primary-blue); font-size: 14px;"></iconify-icon>
                                        <span style="font-size: 12px; font-weight: 700; color: var(--text-dark);">${order.recipient_name}</span>
                                    </div>
                                    <div style="font-size: 11px; font-weight: 700; color: var(--primary-blue); display: flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="solar:phone-bold-duotone" style="font-size: 12px;"></iconify-icon>
                                        ${order.recipient_phone}
                                    </div>
                                </div>
                                <div style="display: flex; align-items: start; gap: 6px;">
                                    <iconify-icon icon="solar:map-point-bold-duotone" style="color: var(--primary-blue); font-size: 14px; flex-shrink: 0;"></iconify-icon>
                                    <div style="font-size: 10px; color: var(--text-muted); line-height: 1.4;">
                                        ${order.delivery_address}
                                        <div style="color: var(--primary-blue); font-weight: 700; margin-top: 2px;">Jarak: ${order.delivery_distance_km} KM</div>
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                                <div onclick="toggleProductList('${order.order_code}')" style="background: #fdf2f2; padding: 6px 10px; border-radius: 8px; border: 1px solid #fee2e2; cursor: pointer; transition: all 0.2s; position: relative;" onmouseover="this.style.borderColor='#ef4444'" onmouseout="this.style.borderColor='#fee2e2'">
                                    <div style="font-size: 8px; color: #991b1b; text-transform: uppercase; font-weight: 800;">Items (Klik Detail)</div>
                                    <div style="font-size: 12px; font-weight: 800; color: #991b1b; display: flex; justify-content: space-between; align-items: center;">
                                        <span>${order.items_count} Produk</span>
                                        <iconify-icon icon="solar:alt-arrow-down-bold-duotone" style="font-size: 14px;"></iconify-icon>
                                    </div>
                                </div>
                                <div style="background: #f0fdf4; padding: 6px 10px; border-radius: 8px; border: 1px solid #dcfce7;">
                                    <div style="font-size: 8px; color: #166534; text-transform: uppercase; font-weight: 800;">Total</div>
                                    <div style="font-size: 12px; font-weight: 800; color: #166534;">Rp ${new Number(order.total_amount).toLocaleString('id-ID')}</div>
                                </div>
                            </div>

                            <div id="product_list_${order.order_code}" style="display: none; margin-bottom: 12px; animation: slideDown 0.3s ease;">
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    ${order.items.map(item => {
                                        const imageUrl = item.product && item.product.resolved_image_url ? item.product.resolved_image_url : '/images/placeholder-product.png';
                                        return `
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: white; border: 1px solid #f1f5f9; border-radius: 10px;">
                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <div style="width: 40px; height: 40px; background: #f8fafc; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                                    <img src="${imageUrl}" onerror="this.src='/images/placeholder-product.png'" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <div style="font-size: 11px; font-weight: 700; color: var(--text-dark);">${item.product_name}</div>
                                                    <div style="font-size: 10px; color: var(--text-muted);">Rp ${new Number(item.unit_price).toLocaleString('id-ID')} x ${item.quantity}</div>
                                                </div>
                                            </div>
                                            <div style="font-size: 11px; font-weight: 800; color: var(--primary-blue);">
                                                Rp ${new Number(item.subtotal).toLocaleString('id-ID')}
                                            </div>
                                        </div>
                                    `}).join('')}
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted);">
                                <span>Ongkir: Rp ${new Number(order.shipping_fee).toLocaleString('id-ID')}</span>
                                <span style="color: #ef4444; font-weight: 700;">Diskon: ${order.discount_percent * 100}%</span>
                            </div>
                        </div>
                    </div>
                `;
                transactionList.appendChild(accordionItem);
            });
        } else {
            countBadge.innerText = '0 Pesanan';
            transactionList.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 20px; border: 2px dashed #e2e8f0;">
                    <iconify-icon icon="solar:box-minimalistic-broken" style="font-size: 48px; color: #cbd5e1; margin-bottom: 10px;"></iconify-icon>
                    <div style="color: #94a3b8; font-size: 13px; font-weight: 600;">Belum ada riwayat transaksi</div>
                </div>
            `;
        }

        openModal('viewModal');
    }

    function toggleOrderDetails(header) {
        const details = header.nextElementSibling;
        const arrow = header.querySelector('.arrow-icon');
        const isVisible = details.style.display === 'block';
        
        // Tutup detail lain yang sedang terbuka jika mau (opsional)
        // document.querySelectorAll('.order-details').forEach(el => el.style.display = 'none');
        // document.querySelectorAll('.arrow-icon').forEach(el => el.style.transform = 'rotate(0)');

        if (isVisible) {
            details.style.display = 'none';
            arrow.style.transform = 'rotate(0)';
            header.style.background = 'transparent';
        } else {
            details.style.display = 'block';
            arrow.style.transform = 'rotate(90deg)';
            header.style.background = '#f8fafc';
        }
    }

    function toggleProductList(orderCode) {
        const list = document.getElementById('product_list_' + orderCode);
        if (list.style.display === 'none') {
            list.style.display = 'block';
        } else {
            list.style.display = 'none';
        }
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

    // Bulk Actions logic
    const bulkBar = document.getElementById('bulk-action-bar');
    const selectedCount = document.getElementById('selected-count');
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    const checkAll = document.getElementById('checkAllCustomer');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.customer-checkbox:checked');
        if (checked.length > 0) {
            bulkBar.style.display = 'flex';
            selectedCount.innerText = checked.length;
        } else {
            bulkBar.style.display = 'none';
        }
    }

    checkAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            if (cb.parentElement.parentElement.style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    function bulkDelete() {
        const checked = document.querySelectorAll('.customer-checkbox:checked');
        const ids = Array.from(checked).map(cb => cb.value);
        
        Swal.fire({
            title: 'Hapus ' + ids.length + ' Kontak?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Coming Soon!', 'Fitur bulk delete sedang disiapkan.', 'info');
            }
        });
    }

    // Broadcast logic
    function openBroadcastModal() {
        const checked = document.querySelectorAll('.customer-checkbox:checked');
        if (checked.length === 0) {
            Swal.fire('Peringatan', 'Pilih minimal satu kontak untuk siaran.', 'warning');
            return;
        }
        document.getElementById('broadcast-count').innerText = checked.length;
        document.getElementById('modalBroadcast').style.display = 'flex';
    }

    async function startBroadcast() {
        const message = document.getElementById('broadcast-message').value;
        if (!message.trim()) {
            Swal.fire('Peringatan', 'Harap isi pesan siaran Anda.', 'warning');
            return;
        }

        const checked = document.querySelectorAll('.customer-checkbox:checked');
        const contacts = Array.from(checked).map(cb => {
            const row = cb.closest('tr');
            const phone = row.querySelector('td:nth-child(3)').innerText;
            return phone.replace(/[^0-9]/g, '').replace(/^0/, '62');
        });

        closeModal('modalBroadcast');

        Swal.fire({
            title: 'Memulai Siaran...',
            html: `Mengirim ke <b>${contacts.length}</b> kontak.<br><small>Harap jangan tutup halaman ini.</small>`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        for (let i = 0; i < contacts.length; i++) {
            const phone = contacts[i];
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
            
            // Open window
            window.open(url, '_blank');

            // Delay to avoid blocking and spam filters
            if (i < contacts.length - 1) {
                await new Promise(resolve => setTimeout(resolve, 1500));
            }
        }

        Swal.fire({
            icon: 'success',
            title: 'Siaran Selesai!',
            text: `Berhasil memproses ${contacts.length} pengiriman.`,
            confirmButtonColor: '#22c55e'
        });
    }

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
