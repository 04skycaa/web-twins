@extends('layouts.app')

@section('content')
    <div class="transaksi-wrapper">
        @include('transaksi.partials.tabs')

        @if(session('success'))
        <div class="alert alert-success mt-3 mb-3">
            {{ session('success') }}
        </div>
        @endif

        <div class="content-card mt-4">
            <div class="card-header">
                <h4>Manajemen Diskon & Promo</h4>
                <div class="header-actions">
                    <button class="btn-primary-small" onclick="openModal('addModalDiskon')">
                        <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                        Tambah Diskon
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Banner</th>
                            <th>Nama Diskon</th>
                            <th>Kode Promo</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diskons as $diskon)
                        <tr>
                            <td>
                                @if($diskon->image_banner)
                                    <img src="{{ \App\Http\Controllers\LandingController::resolveImageUrl($diskon->image_banner) }}" 
                                         style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                @else
                                    <span style="font-size: 10px; color: #999; font-style: italic;">No Banner</span>
                                @endif
                            </td>
                            <td class="text-bold">{{ $diskon->nama_promo }}</td>
                            <td>{{ $diskon->kode_promo ?? '-' }}</td>
                            <td>{{ $diskon->tipe }}</td>
                            <td class="text-bold text-success">{{ $diskon->tipe == 'persen' ? $diskon->nilai.'%' : 'Rp'.number_format($diskon->nilai, 0, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($diskon->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($diskon->tanggal_selesai)->format('d M') }}</td>
                            <td><span class="badge {{ $diskon->status ? 'success' : 'warning' }}">{{ $diskon->status ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="action-buttons-table">
                                    <button class="btn-icon" 
                                            data-diskon='{{ json_encode($diskon) }}'
                                            onclick="openEditModalDiskon(this)">
                                        <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-icon text-danger" onclick="openDeleteModalDiskon('{{ $diskon->uuid }}')">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data diskon.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Diskon -->
    <div id="addModalDiskon" class="modal-overlay">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h5>Tambah Diskon Baru</h5>
                <button class="close-btn" onclick="closeModal('addModalDiskon')">&times;</button>
            </div>
            <form action="{{ route('transaksi.diskon.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                    <div class="form-group">
                        <label>Banner Promo</label>
                        <input type="file" name="image_banner" class="form-control" accept="image/*">
                        <small style="color: #888;">Format: JPG, PNG. Maks: 2MB</small>
                    </div>
                    <div class="form-group">
                        <label>Nama Promo</label>
                        <input type="text" name="nama_promo" class="form-control" required placeholder="Contoh: Promo Lebaran">
                    </div>
                    <div class="form-row">
                        <div class="form-group half">
                            <label>Tipe</label>
                            <select name="tipe" class="form-control" required>
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div class="form-group half">
                            <label>Nilai</label>
                            <input type="number" name="nilai" class="form-control" required placeholder="Contoh: 10 atau 15000">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group half">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="form-group half">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan promo..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addModalDiskon')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Diskon -->
    <div id="editModalDiskon" class="modal-overlay">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h5>Edit Diskon</h5>
                <button class="close-btn" onclick="closeModal('editModalDiskon')">&times;</button>
            </div>
            <form id="editDiskonForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                    <div class="form-group">
                        <label>Ganti Banner Promo (Opsional)</label>
                        <input type="file" name="image_banner" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Nama Promo</label>
                        <input type="text" name="nama_promo" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group half">
                            <label>Tipe</label>
                            <select name="tipe" id="edit_tipe" class="form-control" required>
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div class="form-group half">
                            <label>Nilai</label>
                            <input type="number" name="nilai" id="edit_nilai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group half">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="edit_tgl_mulai" class="form-control" required>
                        </div>
                        <div class="form-group half">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="edit_tgl_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModalDiskon')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete Diskon -->
    <div id="deleteModalDiskon" class="modal-overlay">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h5>Hapus Diskon</h5>
                <button class="close-btn" onclick="closeModal('deleteModalDiskon')">&times;</button>
            </div>
            <div class="modal-body text-center">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size: 50px; color: #ef4444; margin-bottom: 10px;"></iconify-icon>
                <p>Apakah Anda yakin ingin menghapus diskon ini?</p>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteModalDiskon')">Batal</button>
                <form id="deleteDiskonForm" method="POST" style="display:inline;">
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

        function openEditModalDiskon(el) {
            const data = JSON.parse(el.getAttribute('data-diskon'));
            document.getElementById('editDiskonForm').action = `/transaksi/diskon/${data.uuid}`;
            document.getElementById('edit_nama').value = data.nama_promo;
            document.getElementById('edit_tipe').value = data.tipe;
            document.getElementById('edit_nilai').value = data.nilai;
            document.getElementById('edit_tgl_mulai').value = data.tanggal_mulai.split(' ')[0];
            document.getElementById('edit_tgl_selesai').value = data.tanggal_selesai.split(' ')[0];
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';
            document.getElementById('edit_status').value = data.status ? 'Aktif' : 'Nonaktif';
            openModal('editModalDiskon');
        }

        function openDeleteModalDiskon(uuid) {
            document.getElementById('deleteDiskonForm').action = `/transaksi/diskon/${uuid}`;
            openModal('deleteModalDiskon');
        }
    </script>

    <style>
        .transaksi-wrapper {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .content-card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h4 {
            margin: 0;
            font-size: 16px;
            color: #1e293b;
        }

        .btn-primary-small, .btn-secondary, .btn-primary, .btn-danger {
            border: none; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 13px; font-weight: 600;
        }
        .btn-primary-small {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #0ea5e9;
            color: white;
            padding: 8px 16px;
        }
        .btn-primary-small:hover { opacity: 0.9; }
        .btn-secondary { background: #f1f5f9; color: #475569; padding: 8px 16px; }
        .btn-primary { background: #0ea5e9; color: white; padding: 8px 16px; }
        .btn-danger { background: #ef4444; color: white; padding: 8px 16px; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-primary:hover, .btn-danger:hover { opacity: 0.9; }

        .table-responsive { overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 13px; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        .custom-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
        
        .text-bold { font-weight: 600; }
        .text-success { color: #166534; }
        .text-center { text-align: center; }
        .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .success { background: #dcfce7; color: #166534; }
        .warning { background: #fef9c3; color: #854d0e; }

        .action-buttons-table { display: flex; gap: 8px; }
        .btn-icon {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-icon:hover { background: #e0f2fe; color: #0ea5e9; }
        .btn-icon.text-danger:hover { background: #fee2e2; color: #ef4444; }

        .alert { padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .mt-3 { margin-top: 1rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1.5rem; }

        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        .modal-content { background: #fff; width: 100%; max-width: 500px; border-radius: 12px; transform: scale(0.95); opacity:0; transition: all 0.3s ease; }
        .modal-overlay.show .modal-content { transform: scale(1); opacity:1;}
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
    </style>
@endsection