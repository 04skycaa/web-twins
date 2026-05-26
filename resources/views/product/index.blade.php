@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous">
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js" defer></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js" defer></script>


<script>
    function parseDateTimeString(dateStr) {
        if (!dateStr) return '-';
        // If it contains T and Z (like ISO UTC string: 2026-05-17T12:24:32.000000Z)
        // we parse it with native Date to shift to local WIB time correctly
        if (dateStr.includes('T') && dateStr.endsWith('Z')) {
            const d = new Date(dateStr);
            return d.toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
        }
        
        // Otherwise, if it is a raw SQL datetime like "2026-05-17 19:24:32"
        // we parse it directly to avoid any browser timezone offset shifts
        const parts = dateStr.split(/[\sT]+/);
        if (parts.length >= 2) {
            const datePart = parts[0]; // "2026-05-17"
            const timePart = parts[1]; // "19:24:32"
            
            const dParts = datePart.split('-');
            if (dParts.length === 3) {
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const day = dParts[2];
                const monthIndex = parseInt(dParts[1], 10) - 1;
                const month = months[monthIndex] || '';
                const year = dParts[0];
                
                const tParts = timePart.split(':');
                const hour = tParts[0] || '00';
                const minute = tParts[1] || '00';
                
                return `${day} ${month} ${year} ${hour}:${minute}`;
            }
        }
        
        // Fallback
        const fallbackDate = new Date(dateStr);
        return fallbackDate.toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
    }

    function showLoading(text = 'Sedang Memproses Data...') {
        Swal.fire({
            title: text,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    function hideLoading() {
        Swal.close();
    }

    function openModal(id, zIndex = 20000) {
        console.log('Attempting to open modal:', id);
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.setProperty('display', 'flex', 'important');
            modal.style.setProperty('z-index', zIndex, 'important');
            modal.style.setProperty('visibility', 'visible', 'important');
            modal.style.setProperty('opacity', '1', 'important');
            console.log('Modal opened successfully:', id);
        } else {
            console.error('openModal Error: Element with ID "' + id + '" not found!');
        }
    }

    function closeModal(id) {
        console.log('Closing modal:', id);
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.setProperty('display', 'none', 'important');
            
            // Reset form and UI if closing add modal
            if (id === 'addModal') {
                const form = document.getElementById('addForm');
                if (form) {
                    form.reset();
                    const preview = document.getElementById('imagePreviewContainer');
                    if (preview) {
                        preview.innerHTML = `
                            <div style="text-align: center;">
                                <iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 40px; color: #94a3b8;"></iconify-icon>
                                <p style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Klik untuk Pilih/Foto</p>
                            </div>`;
                        preview.style.border = '2px dashed #cbd5e1';
                        preview.style.background = '#f8fafc';
                    }
                    const result = document.getElementById('croppedImageResult');
                    if (result) result.value = '';
                    
                    // CRITICAL: Clear wholesale price levels
                    const levelBody = document.getElementById('priceLevelBody');
                    if (levelBody) levelBody.innerHTML = '';
                }
            }
        }
    }
</script>


{{-- MODALS CONSOLIDATED AT TOP --}}

<!-- Modal Tambah Produk -->
<div id="addModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 700px; width: 95%; height: 600px; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h3><iconify-icon icon="solar:add-circle-bold-duotone" style="vertical-align: middle; margin-right: 8px;"></iconify-icon> Tambah Produk Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('products.store') }}" method="POST" id="addForm" enctype="multipart/form-data" onsubmit="return validateProductForm('addForm')" novalidate style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
                    <!-- Sisi Kiri: Upload Gambar -->
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 14px; color: #475569;">Foto Produk</label>
                        <div id="imagePreviewContainer" style="width: 100%; aspect-ratio: 1/1; border: 2px dashed #cbd5e1; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; cursor: pointer; transition: all 0.3s;" onclick="document.getElementById('productImageInput').click()">
                            <div style="text-align: center;">
                                <iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 40px; color: #94a3b8;"></iconify-icon>
                                <p style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Klik untuk Pilih/Foto</p>
                            </div>
                        </div>
                        <input type="file" id="productImageInput" name="image" accept="image/*" style="display: none;">
                        <input type="hidden" name="cropped_image" id="croppedImageResult">
                        <div id="imageErrorAdd" class="invalid-feedback" style="text-align: center;">Foto produk wajib diisi</div>
                        <p style="font-size: 11px; color: #64748b; margin-top: 8px; text-align: center;">Rasio 1:1 direkomendasikan</p>
                    </div>

                    <!-- Sisi Kanan: Data Utama -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="form-group">
                            <label for="nama_produk">Nama Produk</label>
                            <input type="text" name="nama_produk" id="addNamaProduk" class="form-control" placeholder="Masukkan nama produk..." required>
                            <div class="invalid-feedback">Nama produk wajib diisi</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label for="barcode">Barcode / SKU</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" name="barcode" id="barcodeAdd" class="form-control" placeholder="Scan/Ketik Barcode atau SKU" style="flex: 1;" required>
                                    <button type="button" class="btn-action" style="width: 44px; height: 44px; padding: 0; background: #FFB300; color: #333; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" onclick="document.getElementById('barcodeFileInputAdd').click()" title="Scan dari Gambar">
                                        <iconify-icon icon="solar:gallery-bold-duotone" style="font-size: 24px;"></iconify-icon>
                                    </button>
                                </div>
                                <input type="file" id="barcodeFileInputAdd" accept="image/*" style="display: none;" onchange="handleBarcodeImageScan(event, 'barcodeAdd')">
                                <div class="invalid-feedback">Barcode / SKU wajib diisi</div>
                            </div>
                            <div class="form-group">
                                <label for="kategori_id">Kategori</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="kategori_id" class="form-control" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->uuid }}">{{ $category->nama_category }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih kategori</div>
                                    <button type="button" class="btn-filter" style="width: 44px; flex-shrink: 0;" onclick="openAddCategoryModal()">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 20px;"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->isOwner())
                        <div class="form-group">
                            <label for="store_ids_add" style="margin-bottom: 8px; display: block; font-weight: 600; font-size: 14px; color: #475569;">Pilih Outlet</label>
                            <select name="store_ids[]" id="store_ids_add" multiple required placeholder="-- Pilih Minimal 1 Outlet --">
                                @foreach($stores ?? [] as $store)
                                    <option value="{{ $store->uuid }}">{{ $store->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Silakan pilih minimal 1 outlet</div>
                        </div>
                        @endif
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
                    <div class="form-group">
                        <label for="harga_modal">Harga Modal (Rp)</label>
                        <input type="number" name="harga_modal" class="form-control" placeholder="0" required>
                        <div class="invalid-feedback">Harga modal wajib diisi</div>
                    </div>
                    <div class="form-group">
                        <label for="harga_jual">Harga Jual Satuan (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="0" required>
                        <div class="invalid-feedback">Harga jual wajib diisi</div>
                    </div>
                </div>

                <!-- Grosir Section -->
                <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0; color: #1e293b; font-size: 15px;">Harga Grosir / Bertingkat <span style="font-size: 11px; color: #64748b; font-weight: 400;">(Opsional)</span></h4>
                        <button type="button" class="btn-action" style="padding: 6px 12px; font-size: 12px; background: #f0fdf4; color: #166534;" onclick="addPriceLevelRow('priceLevelBody')">
                            + Tambah Level
                        </button>
                    </div>
                    <div style="background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; font-size: 12px; color: #64748b;">
                                    <th style="padding-bottom: 8px; padding-right: 15px;">Minimal Pembelian (Pcs)</th>
                                    <th style="padding-bottom: 8px; padding-right: 15px;">Harga Satuan Grosir (Rp)</th>
                                    <th style="width: 40px; padding-bottom: 8px;"></th>
                                </tr>
                            </thead>
                            <tbody id="priceLevelBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="margin-top: 10px; display: flex; gap: 12px; padding: 20px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 2; justify-content: center; background: var(--primary-blue); color: white;">
                    <iconify-icon icon="solar:check-circle-bold-duotone" style="margin-right: 8px;"></iconify-icon> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="editModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 700px; width: 95%; height: 600px; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h3><iconify-icon icon="solar:pen-new-square-bold-duotone" style="vertical-align: middle; margin-right: 8px;"></iconify-icon> Edit Data Produk</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm('editForm')" novalidate style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            @method('PUT')
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
                    <!-- Sisi Kiri: Upload Gambar -->
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 14px; color: #475569;">Foto Produk</label>
                        <div id="editImagePreviewContainer" style="width: 100%; aspect-ratio: 1/1; border: 2px dashed #cbd5e1; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; cursor: pointer;" onclick="document.getElementById('editProductImageInput').click()">
                            <iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 40px; color: #94a3b8;"></iconify-icon>
                        </div>
                        <input type="file" id="editProductImageInput" name="image" accept="image/*" style="display: none;">
                        <input type="hidden" name="cropped_image" id="editCroppedImageResult">
                        <div id="imageErrorEdit" class="invalid-feedback" style="text-align: center;">Foto produk wajib diisi</div>
                    </div>

                    <!-- Sisi Kanan: Data Utama -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="form-group">
                            <label for="edit_nama">Nama Produk</label>
                            <input type="text" name="nama_produk" id="edit_nama" class="form-control" required>
                            <div class="invalid-feedback">Nama produk wajib diisi</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label for="edit_barcode">Barcode / SKU</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" name="barcode" id="edit_barcode" class="form-control" placeholder="Scan/Ketik Barcode atau SKU" style="flex: 1;" required>
                                    <button type="button" class="btn-action" style="width: 44px; height: 44px; padding: 0; background: #FFB300; color: #333; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" onclick="document.getElementById('barcodeFileInputEdit').click()" title="Scan dari Gambar">
                                        <iconify-icon icon="solar:gallery-bold-duotone" style="font-size: 24px;"></iconify-icon>
                                    </button>
                                </div>
                                <input type="file" id="barcodeFileInputEdit" accept="image/*" style="display: none;" onchange="handleBarcodeImageScan(event, 'edit_barcode')">
                                <div class="invalid-feedback">Barcode / SKU wajib diisi</div>
                            </div>
                            <div class="form-group">
                                <label for="edit_kategori">Kategori</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="kategori_id" id="edit_kategori" class="form-control" required>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->uuid }}">{{ $category->nama_category }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih kategori</div>
                                    <button type="button" class="btn-filter" style="width: 44px; flex-shrink: 0;" onclick="openAddCategoryModal()">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 20px;"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
                    <div class="form-group">
                        <label for="edit_modal">Harga Modal (Rp)</label>
                        <input type="number" name="harga_modal" id="edit_modal" class="form-control" required>
                        <div class="invalid-feedback">Harga modal wajib diisi</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_jual">Harga Jual Satuan (Rp)</label>
                        <input type="number" name="harga_jual" id="edit_jual" class="form-control" required>
                        <div class="invalid-feedback">Harga jual wajib diisi</div>
                    </div>
                </div>

                <!-- Grosir Section Edit -->
                <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0; color: #1e293b; font-size: 15px;">Harga Grosir / Bertingkat</h4>
                        <button type="button" class="btn-action" style="padding: 6px 12px; font-size: 12px; background: #f0fdf4; color: #166534;" onclick="addPriceLevelRow('editPriceLevelBody')">
                            + Tambah Level
                        </button>
                    </div>
                    <div style="background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; font-size: 12px; color: #64748b;">
                                    <th>Minimal Qty</th>
                                    <th>Harga Grosir (Rp)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="editPriceLevelBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="margin-top: 10px; display: flex; gap: 12px; padding: 20px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 2; justify-content: center; background: var(--primary-blue); color: white;">
                    <iconify-icon icon="solar:check-circle-bold-duotone" style="margin-right: 8px;"></iconify-icon> Update Produk
                </button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 600px; width: 95%; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
        <div class="modal-header" style="flex-shrink: 0;">
            <h3><iconify-icon icon="solar:eye-bold-duotone" style="vertical-align: middle; margin-right: 8px;"></iconify-icon> Detail Produk</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" id="viewDetailContent" style="padding: 24px; flex: 1; overflow-y: auto;">
            {{-- Content will be injected via JS --}}
        </div>
        <div style="padding: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: center; background: #fff; flex-shrink: 0;">
            <button type="button" class="btn-action" style="background: var(--primary-blue); color: white; padding: 10px 40px; min-width: 150px; justify-content: center; border-radius: 12px; font-weight: 600;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Update Stok & Expired -->
<div id="editAlertModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 style="display: flex; align-items: center; gap: 10px;">
                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                Update Katalog & Stok
            </h3>
            <button class="close-modal" onclick="closeModal('editAlertModal')">&times;</button>
        </div>
        <form id="editAlertForm" method="POST" onsubmit="showLoading('Sedang Memperbarui Stok...')">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div id="editAlertProductInfo" style="margin-bottom: 24px; padding: 16px; background: #f8fbff; border-radius: 16px; border: 1px solid #d0e7ff; display: flex; gap: 16px; align-items: center;">
                    <img id="editAlertImage" src="" style="width: 80px; height: 80px; border-radius: 12px; object-fit: cover; background: #fff; border: 1px solid #eee;">
                    <div>
                        <div id="editAlertName" style="font-size: 16px; font-weight: 700; color: var(--primary-blue); margin-bottom: 4px;">-</div>
                        <div id="editAlertBarcode" style="font-size: 13px; color: #64748b;">-</div>
                        <div id="editAlertStore" style="font-size: 12px; color: #0056b3; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                            <span id="editAlertStoreName">-</span>
                        </div>
                        <div style="font-size: 10px; color: #888; margin-top: 2px;">Masuk: <span id="editAlertDateMasuk">-</span></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="alert_stok">Stok Saat Ini (Pcs)</label>
                        <input type="number" name="stok" id="alert_stok" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="alert_kadaluarsa">Tanggal Kadaluarsa</label>
                        <input type="date" name="kadaluarsa" id="alert_kadaluarsa" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 10px;">
                    <div class="form-group">
                        <label for="alert_stok_minimum">Min. Stok Notifikasi (Pcs)</label>
                        <input type="number" name="stok_minimum" id="alert_stok_minimum" class="form-control" placeholder="Default: 10">
                    </div>
                    <div class="form-group">
                        <label>Status Aktif di Outlet</label>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                            <label class="switch">
                                <input type="checkbox" name="status_aktif" id="alert_status_aktif" value="1">
                                <span class="slider round"></span>
                            </label>
                            <span id="statusLabel" style="font-size: 14px; font-weight: 600; color: #2E7D32;">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 24px; display: flex; gap: 12px; padding: 20px;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('editAlertModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center; background: var(--primary-blue); color: white;">
                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tambah Transfer Modal -->
<div id="transferModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 750px; width: 95%; height: 600px; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
        <div class="modal-header">
            <h3>Buat Transfer Stok Baru</h3>
            <button class="close-modal" onclick="closeModal('transferModal')">&times;</button>
        </div>
        <form action="{{ route('products.transfer.store') }}" method="POST" id="transferForm" onsubmit="return validateProductForm('transferForm')" novalidate style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div class="form-group">
                        <label style="white-space: nowrap;">Toko Asal</label>
                        @if(Auth::user()->isOwner())
                            <select name="store_id" id="sourceStoreSelect" class="form-control" required onchange="handleSourceStoreChange(this.value)">
                                <option value="">-- Pilih Toko Asal --</option>
                                @foreach($stores as $s)
                                    <option value="{{ $s->uuid }}" {{ Auth::user()->store_id == $s->uuid ? 'selected' : '' }}>{{ $s->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Outlet asal wajib dipilih</div>
                        @else
                            <input type="text" class="form-control" value="{{ Auth::user()->outlet->nama ?? 'Cabang' }}" readonly style="background: #f8fafc;">
                            <input type="hidden" name="store_id" id="sourceStoreSelect" value="{{ Auth::user()->store_id }}">
                        @endif
                    </div>
                    <div class="form-group">
                        <label style="white-space: nowrap;">Toko Tujuan</label>
                        <select name="tujuan_store_id" class="form-control" required>
                            <option value="">-- Pilih Toko Tujuan --</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->uuid }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Outlet tujuan wajib dipilih</div>
                    </div>
                    <div class="form-group">
                        <label>Petugas Pengirim</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly style="background: #f8fafc; font-weight: 600; color: var(--primary-blue);">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label>Jenis Pembayaran</label>
                    <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Kredit (Hutang)</span>
                        <label class="switch">
                            <input type="checkbox" name="transfer_payment_type_toggle" id="transferPaymentMethodToggle" checked onchange="updateTransferPaymentLabel()">
                            <span class="slider round"></span>
                        </label>
                        <span style="font-size: 13px; font-weight: 600; color: #2E7D32;" id="transferPaymentLabel">Tunai (Kas)</span>
                    </div>
                    <input type="hidden" name="payment_type" id="transferPaymentMethodValue" value="Tunai">
                </div>

                <div id="transferPaymentOptions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; padding: 15px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="transfer_metode_pembayaran_select" class="form-control" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($payment_methods ?? [] as $pm)
                                <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="transferDpAmountGroup" style="display: none;">
                        <label style="color: #C53030;">Jumlah Uang Muka (Opsional)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600; z-index: 5;">Rp</span>
                            <input type="number" name="dp_amount" id="transfer_dp_amount" class="form-control" style="padding-left: 48px;" placeholder="0" min="0">
                        </div>
                        <label style="color: #C53030; margin-top: 10px; display: block;">Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" id="transfer_jatuh_tempo" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0; color: #334155;">Daftar Produk yang Dipindah</h4>
                        <button type="button" class="btn-action" style="padding: 6px 14px; font-size: 12px;" onclick="addTransferRow()">
                            <iconify-icon icon="solar:add-circle-bold-duotone" style="margin-right: 6px;"></iconify-icon> Tambah Produk
                        </button>
                    </div>
                    <div class="table-scroll-container" style="overflow-x: auto; max-height: 230px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 16px; background: white;">
                        <table class="fitur-table" style="font-size: 13px; min-width: 600px; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                            <thead style="position: sticky; top: 0; z-index: 11; background: #f8fafc;">
                                <tr style="background: #f8fafc;">
                                    <th style="width: 45%; min-width: 200px;">Nama Produk / Barcode</th>
                                    <th style="width: 15%; min-width: 80px;">Qty</th>
                                    <th style="width: 25%; min-width: 120px;">Harga Transfer</th>
                                    <th style="width: 15%; min-width: 100px;">Subtotal</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="transferItemsTable">
                                {{-- Rows injected via JS --}}
                            </tbody>
                            <tfoot style="position: sticky; bottom: 0; z-index: 11; background: #f8fafc;">
                                <tr style="background: #f8fafc; font-weight: 700;">
                                    <td colspan="3" style="text-align: right; padding: 12px; border-top: 2px solid #e2e8f0;">TOTAL TRANSFER</td>
                                    <td colspan="2" id="transferGrandTotal" style="padding: 12px; color: var(--primary-blue); font-size: 16px; border-top: 2px solid #e2e8f0;">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label for="transfer_catatan">Catatan / Keterangan</label>
                    <textarea name="catatan" id="transfer_catatan" class="form-control" rows="2" placeholder="Alasan pemindahan barang..."></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 16px; padding: 0 20px 20px; justify-content: center;">
                <button type="button" class="btn-action" style="min-width: 140px; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('transferModal')">Batal</button>
                <button type="submit" class="btn-action" style="min-width: 200px; justify-content: center; background: var(--primary-blue); color: white;">
                    <iconify-icon icon="solar:transfer-horizontal-bold-duotone" style="margin-right: 8px; font-size: 18px;"></iconify-icon> Kirim Transfer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Transfer Modal v2 -->
<div id="transferDetailModal_v2" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <h3>Detail Transfer Stok</h3>
            <button class="close-modal" onclick="closeModal('transferDetailModal_v2')">&times;</button>
        </div>
        <div class="modal-body" id="transferDetailBody_v2" style="padding: 20px;">
            {{-- Content injected via JS --}}
        </div>
        <div style="margin-top: 24px; display: flex; justify-content: center; padding-bottom: 20px;">
            <button type="button" class="btn-action" style="background: var(--primary-blue); color: white; min-width: 150px; justify-content: center;" onclick="closeModal('transferDetailModal_v2')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div id="addCategoryModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Tambah Kategori Baru</h3>
            <button class="close-modal" onclick="closeModal('addCategoryModal')">&times;</button>
        </div>
        <form id="addCategoryForm" action="{{ route('products.category.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama_category" class="form-control" placeholder="Misal: Minuman, Makanan" required>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; padding: 20px; border-top: 1px solid #eee;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('addCategoryModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; background: var(--primary-blue); color: white; justify-content: center;">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Tambah Opname Modal -->
<div id="addOpnameModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 900px; width: 95%; height: 680px; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
        <div class="modal-header">
            <h3>Input Opname Stok</h3>
            <button class="close-modal" onclick="closeModal('addOpnameModal')">&times;</button>
        </div>
        <form action="{{ route('products.opname.store') }}" method="POST" id="opnameForm" onsubmit="return validateProductForm('opnameForm')" novalidate style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            <div id="opnameMethod"></div>
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Pilih Outlet / Toko</label>
                        @if(Auth::user()->isOwner())
                            <select name="store_id" id="opname_store_id" class="form-control" required onchange="loadProductsByStore(this.value)" data-user-store="{{ Auth::user()->store_id }}">
                                <option value="">-- Pilih Toko --</option>
                                @foreach($stores ?? [] as $store)
                                    <option value="{{ $store->uuid }}" {{ Auth::user()->store_id == $store->uuid ? 'selected' : '' }}>{{ $store->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Outlet wajib dipilih</div>
                        @else
                            <input type="hidden" name="store_id" id="opname_store_id" value="{{ Auth::user()->store_id }}">
                            <input type="text" class="form-control" value="{{ Auth::user()->outlet->nama ?? 'Cabang' }}" readonly style="background: #f8f9fa;">
                        @endif
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; color: var(--primary-blue); display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                        Item Produk
                    </h4>
                    <button type="button" class="btn-action" style="padding: 6px 12px; font-size: 12px; background: #eff6ff; color: var(--primary-blue); border: 1px dashed var(--primary-blue);" onclick="addOpnameRow()">
                        <iconify-icon icon="solar:add-circle-bold-duotone" style="margin-right: 4px;"></iconify-icon> Tambah Baris
                    </button>
                </div>

                <div class="table-scroll-container" style="overflow-x: auto; max-height: 230px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 16px; background: white;">
                    <table class="fitur-table" style="font-size: 13px; min-width: 750px; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                        <thead style="position: sticky; top: 0; z-index: 11; background: #f8fafc;">
                            <tr style="background: #f8fafc;">
                                <th style="width: 35%; min-width: 240px;">Nama Produk / Barcode</th>
                                <th style="width: 12%; min-width: 110px; text-align: center;">Sistem</th>
                                <th style="width: 12%; min-width: 110px; text-align: center;">Fisik</th>
                                <th style="width: 12%; min-width: 110px; text-align: center;">Selisih</th>
                                <th style="min-width: 180px;">Alasan Selisih / Keterangan</th>
                                <th style="width: 50px; text-align: center;">#</th>
                            </tr>
                        </thead>
                        <tbody id="opnameItemsTable">
                            {{-- Rows will be added here via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 12px; padding: 20px;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b;" onclick="closeModal('addOpnameModal')">Batal</button>
                <button type="submit" name="action" value="save" class="btn-action" style="flex: 1; justify-content: center; background: #64748b; color: white;" onclick="this.form.dataset.submitAction = 'save'">
                    <iconify-icon icon="solar:diskette-bold-duotone" style="margin-right: 8px;"></iconify-icon>
                    Simpan Sesi Opname
                </button>
                <button type="submit" name="action" value="finalize" class="btn-action" style="flex: 1.5; justify-content: center; background: #2E7D32; color: white;" onclick="this.form.dataset.submitAction = 'finalize'">
                    <iconify-icon icon="solar:check-read-bold-duotone" style="margin-right: 8px;"></iconify-icon>
                    Finalisasi Opname
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tambah Restok Modal -->
<div id="restokModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 800px; width: 95%; height: 680px; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
        <div class="modal-header">
            <h3>Tambah Restok Baru</h3>
            <button class="close-modal" onclick="closeModal('restokModal')">&times;</button>
        </div>
        <form action="{{ route('products.restok.store') }}" method="POST" id="restokForm" onsubmit="return validateProductForm('restokForm')" novalidate style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div class="form-group">
                        <label>Target Outlet</label>
                        @if(Auth::user()->isOwner())
                            <select name="store_id" class="form-control" required>
                                <option value="">-- Pilih Toko --</option>
                                @foreach($stores ?? [] as $store)
                                    <option value="{{ $store->uuid }}" {{ Auth::user()->store_id == $store->uuid ? 'selected' : '' }}>{{ $store->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Outlet wajib dipilih</div>
                        @else
                            <input type="hidden" name="store_id" value="{{ Auth::user()->store_id }}">
                            <input type="text" class="form-control" value="{{ Auth::user()->outlet->nama ?? 'Cabang' }}" readonly style="background: #f8f9fa; font-weight: 600;">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="restok_supplier_id">Pilih Supplier</label>
                        <select name="contact_id" id="restok_supplier_id" class="form-control supplier-select" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->uuid }}">{{ $supplier->nama }} ({{ $supplier->no_hp }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Supplier wajib dipilih</div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pembayaran</label>
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #64748b;">Kredit (Hutang)</span>
                            <label class="switch">
                                <input type="checkbox" name="payment_type_toggle" id="paymentMethodToggle" checked onchange="updatePaymentLabel()">
                                <span class="slider round"></span>
                            </label>
                            <span style="font-size: 13px; font-weight: 600; color: #2E7D32;" id="paymentLabel">Tunai (Kas)</span>
                        </div>
                        <input type="hidden" name="payment_type" id="paymentMethodValue" value="Tunai">
                    </div>
                </div>

                <div id="paymentOptions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; padding: 15px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div class="form-group">
                        <label id="pm_label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran_select" class="form-control" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($payment_methods ?? [] as $pm)
                                <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="dpAmountGroup" style="display: none;">
                        <label style="color: #C53030;">Jumlah DP (Uang Muka)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600; z-index: 5;">Rp</span>
                            <input type="number" name="dp_amount" id="dp_amount" class="form-control" style="padding-left: 48px;" placeholder="" min="0">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0; color: #334155;">Daftar Produk</h4>
                        <button type="button" class="btn-action" style="padding: 6px 14px; font-size: 12px;" onclick="addRestokRow()">
                            <iconify-icon icon="solar:add-circle-bold-duotone" style="margin-right: 6px;"></iconify-icon> Tambah Baris
                        </button>
                    </div>
                    <div class="table-scroll-container" style="overflow-x: auto; max-height: 230px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 16px; background: white;">
                        <table class="fitur-table" style="font-size: 13px; min-width: 750px; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                            <thead style="position: sticky; top: 0; z-index: 11; background: #f8fafc;">
                                <tr style="background: #f8fafc;">
                                    <th style="width: 35%; min-width: 260px;">Nama Produk / Barcode</th>
                                    <th style="width: 7%; min-width: 70px;">Qty</th>
                                    <th style="width: 15%; min-width: 120px;">Harga Beli</th>
                                    <th style="width: 15%; min-width: 120px;">Harga Jual Baru</th>
                                    <th style="width: 15%; min-width: 120px;">Tgl Expired</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="restokItemsTable">
                                {{-- Rows injected via JS --}}
                            </tbody>
                            <tfoot style="position: sticky; bottom: 0; z-index: 11; background: #f8fafc;">
                                <tr style="background: #f8fafc; font-weight: 700;">
                                    <td colspan="2" style="text-align: right; padding: 12px; border-top: 2px solid #e2e8f0;">TOTAL PEMBELIAN</td>
                                    <td colspan="4" id="restokGrandTotal" style="padding: 12px; color: var(--primary-blue); font-size: 16px; border-top: 2px solid #e2e8f0;">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label for="restok_catatan">Catatan (Opsional)</label>
                    <textarea name="catatan" id="restok_catatan" class="form-control" rows="2" placeholder="Keterangan tambahan..."></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 16px; padding: 0 20px 20px; justify-content: center;">
                <button type="button" class="btn-action" style="min-width: 140px; background: #f1f5f9; color: #64748b; justify-content: center;" onclick="closeModal('restokModal')">Batal</button>
                <button type="submit" class="btn-action" style="min-width: 200px; justify-content: center; background: var(--primary-blue); color: white;">
                    <iconify-icon icon="solar:diskette-bold-duotone" style="margin-right: 8px; font-size: 18px;"></iconify-icon> Simpan Restok
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Restok Modal -->
<div id="purchaseDetailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 800px; width: 95%; max-height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
        <div class="modal-header" style="flex-shrink: 0; padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Detail Pembelian / Restok</h3>
            <button class="close-modal" onclick="closeModal('purchaseDetailModal')">&times;</button>
        </div>
        <div id="purchaseDetailContent" class="modal-body" style="padding: 24px; overflow-y: auto; flex: 1;">
            {{-- Content injected via JS --}}
        </div>
        <div style="flex-shrink: 0; display: flex; justify-content: center; padding: 16px 24px 24px; border-top: 1px solid #f1f5f9; background: #fafafa;">
            <button type="button" class="btn-action" style="background: var(--primary-blue); color: white; padding: 10px 40px; min-width: 150px; justify-content: center; border-radius: 12px;" onclick="closeModal('purchaseDetailModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Pay Debt Modal -->
<div id="payDebtModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 450px; width: 95%; border-radius: 24px;">
        <div class="modal-header">
            <h3>Bayar Hutang Restok</h3>
            <button class="close-modal" onclick="closeModal('payDebtModal')">&times;</button>
        </div>
        <form action="{{ route('products.restok.pay') }}" method="POST" id="payDebtForm">
            @csrf
            <input type="hidden" name="transaction_id" id="pay_transaction_id">
            <div class="modal-body" style="padding: 24px;">
                <div style="background: #FFF5F5; padding: 16px; border-radius: 16px; margin-bottom: 20px; border: 1px solid #FED7D7;">
                    <div style="font-size: 12px; color: #C53030; font-weight: 600; margin-bottom: 4px;">SISA HUTANG</div>
                    <div style="font-size: 24px; font-weight: 800; color: #C53030;" id="pay_sisa_label">Rp 0</div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="pay_metode_pembayaran" class="form-control" style="transition: all 0.2s ease; border-radius: 12px;">
                        <option value="">-- Pilih Metode --</option>
                        @foreach($payment_methods ?? [] as $pm)
                            <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                        @endforeach
                    </select>
                    <div id="pm_error_msg" style="color: #dc2626; font-size: 12px; margin-top: 6px; display: none; font-weight: 500; padding-left: 2px;">Pilih Metode Pembayaran Anda</div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Jumlah Bayar</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600; z-index: 5;">Rp</span>
                        <input type="number" name="nominal" id="pay_nominal" class="form-control" style="padding-left: 48px; transition: all 0.2s ease; border-radius: 12px;" placeholder="">
                    </div>
                    <div id="nominal_error_msg" style="color: #dc2626; font-size: 12px; margin-top: 6px; display: none; font-weight: 500; padding-left: 2px;">Masukkan Jumlah Bayar Anda</div>
                </div>
            </div>
            <div style="padding: 0 24px 24px; display: flex; gap: 12px;">
                <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center; border-radius: 12px;" onclick="closeModal('payDebtModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 2; background: #E53E3E; color: white; justify-content: center; border-radius: 12px;">
                    <iconify-icon icon="solar:check-circle-bold-duotone" style="margin-right: 8px;"></iconify-icon> Konfirmasi Bayar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Opname Modal -->
<div id="opnameDetailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 900px; width: 95%;">
        <div class="modal-header">
            <h3>Detail Sesi Opname</h3>
            <button class="close-modal" onclick="closeModal('opnameDetailModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 24px; padding: 15px; background: #f8fbff; border-radius: 12px; border: 1px solid #d0e7ff;">
                <div>
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">📅 Tanggal Sesi</div>
                    <div id="det_op_tanggal" style="font-weight: 700; color: var(--primary-blue);">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">🏪 Outlet / Toko</div>
                    <div id="det_op_toko" style="font-weight: 700; color: var(--primary-blue);">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">👤 Petugas Lapangan</div>
                    <div id="det_op_petugas" style="font-weight: 700; color: var(--primary-blue);">-</div>
                </div>
            </div>

            <div style="overflow-x: auto; max-height: 400px; border: 1px solid #eee; border-radius: 12px;">
                <table class="fitur-table" style="font-size: 13px;">
                    <thead style="position: sticky; top: 0; z-index: 10; background: #f8f9fa;">
                        <tr>
                            <th>Produk</th>
                            <th style="text-align: center;">Sistem</th>
                            <th style="text-align: center;">Fisik</th>
                            <th style="text-align: center;">Selisih</th>
                            <th>Alasan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="opnameDetailRows">
                        {{-- Rows will be injected here via JS --}}
                    </tbody>
                </table>
            </div>

            <div id="opnameFinalizeArea" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; display: none;">
                <div style="background: #FFF9C4; padding: 12px; border-radius: 8px; border: 1px solid #FBC02D; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <iconify-icon icon="solar:info-circle-bold-duotone" style="font-size: 24px; color: #827717;"></iconify-icon>
                    <span style="font-size: 13px; color: #827717; font-weight: 600;">Double-check: Stok di database akan disesuaikan secara permanen setelah finalisasi.</span>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b;" onclick="closeModal('opnameDetailModal')">Batal</button>
                    <button id="btnFinalizeOpnameAction" type="button" class="btn-action" style="flex: 1; justify-content: center; background: #2E7D32;">
                        <iconify-icon icon="solar:check-read-bold-duotone" style="margin-right: 8px;"></iconify-icon>
                        Finalisasi & Apply Adjustment
                    </button>
                </div>
            </div>

            
            <div id="opnameDetailCloseArea" style="margin-top: 24px; display: flex; justify-content: center;">
                <button type="button" class="btn-action" style="background: var(--primary-blue); color: white; padding: 10px 40px; min-width: 150px; justify-content: center;" onclick="closeModal('opnameDetailModal')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" crossorigin="anonymous"></script>
{{-- Hidden container for Barcode Scanner initialization (Html5Qrcode) --}}
<div id="barcode-scanner-container" style="display: none;"></div>

<div class="fitur-container">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab == 'produk' ? 'active' : '' }}" data-tab="produk" onclick="switchTab('produk', event)">
            <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
            <span>Produk</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab == 'stok' ? 'active' : '' }}" data-tab="stok" onclick="switchTab('stok', event)">
            <iconify-icon icon="solar:checklist-bold-duotone"></iconify-icon>
            <span>Katalog & Stok</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab == 'restok' ? 'active' : '' }}" data-tab="restok" onclick="switchTab('restok', event)">
            <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
            <span>Restok</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab == 'transfer' ? 'active' : '' }}" data-tab="transfer" onclick="switchTab('transfer', event)">
            <iconify-icon icon="solar:transfer-vertical-bold-duotone"></iconify-icon>
            <span>Transfer Stok</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill {{ $active_tab == 'opname' ? 'active' : '' }}" data-tab="opname" onclick="switchTab('opname', event)">
            <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
            <span>Stok Opname</span>
        </a>
    </div>

    <div id="ajax-content-area">
    @fragment('dashboard-content')
    {{-- We no longer use @fragment for tab switching as we render everything for instant visibility toggling --}}

    @php
        $tabs = ['produk', 'stok', 'restok', 'transfer', 'opname'];
    @endphp

    @foreach($tabs as $tab)
    <div id="section-{{ $tab }}" class="view-section {{ $active_tab == $tab ? 'active' : '' }}">
        {{-- TAB-SPECIFIC ACTION BAR --}}
        <div class="action-bar mobile-action-bar">
            <div class="left-actions-group mobile-action-bar" style="width: 100%; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <!-- Kiri: Search & Filter -->
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; flex: 1;">
                    <div class="search-wrapper mobile-search-shrink" style="max-width: 400px; width: 100%;">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="searchInput-{{ $tab }}" class="search-input" 
                        placeholder="Cari di {{ $tab }}..." 
                        oninput="realtimeSearch('{{ $tab }}')">
                </div>

                @if($tab == 'produk' || $tab == 'stok' || $tab == 'opname')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Kategori" data-filter-key="category_id">
                            <iconify-icon icon="solar:filter-bold-duotone" style="font-size: 24px;" class="{{ request('category_id') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['category_id' => null, 'tab' => $tab]) }}" onclick="applyFilter('category_id', '', event)">Semua Kategori</a>
                            @foreach($categories as $cat)
                                <a href="{{ request()->fullUrlWithQuery(['category_id' => $cat->uuid, 'tab' => $tab]) }}" onclick="applyFilter('category_id', '{{ $cat->uuid }}', event)">{{ $cat->nama_category }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($tab == 'restok')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Supplier" data-filter-key="supplier_id">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" style="font-size: 24px;" class="{{ request('supplier_id') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['supplier_id' => null, 'tab' => $tab]) }}" onclick="applyFilter('supplier_id', '', event)">Semua Supplier</a>
                            @foreach($suppliers as $sup)
                                <a href="{{ request()->fullUrlWithQuery(['supplier_id' => $sup->uuid, 'tab' => $tab]) }}" onclick="applyFilter('supplier_id', '{{ $sup->uuid }}', event)">{{ $sup->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($tab == 'transfer')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Status Transfer" data-filter-key="status">
                            <iconify-icon icon="solar:checklist-bold-duotone" style="font-size: 24px;" class="{{ request('status') ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'tab' => $tab]) }}" onclick="applyFilter('status', '', event)">Semua Status</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Pending', 'tab' => $tab]) }}" onclick="applyFilter('status', 'Pending', event)">Menunggu</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Disetujui', 'tab' => $tab]) }}" onclick="applyFilter('status', 'Disetujui', event)">Disetujui</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Dikirim', 'tab' => $tab]) }}" onclick="applyFilter('status', 'Dikirim', event)">Dikirim</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'Selesai', 'tab' => $tab]) }}" onclick="applyFilter('status', 'Selesai', event)">Selesai</a>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->isOwner())
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Toko" data-filter-key="store_id">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ request('store_id') && request('store_id') != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="{{ request()->fullUrlWithQuery(['store_id' => 'all', 'tab' => $tab]) }}" onclick="applyFilter('store_id', 'all', event)">Semua Toko</a>
                            @foreach($stores as $s)
                                <a href="{{ request()->fullUrlWithQuery(['store_id' => $s->uuid, 'tab' => $tab]) }}" onclick="applyFilter('store_id', '{{ $s->uuid }}', event)">{{ $s->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                </div>

                <!-- Kanan: Extract, Hapus, Tambah -->
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                    <div class="dropdown">
                    <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content" style="right: 0; left: auto;">
                        <a href="javascript:void(0)" onclick="triggerExport('excel')">Excel</a>
                        <a href="javascript:void(0)" onclick="triggerExport('pdf')">PDF</a>
                    </div>
                </div>

                @if($tab == 'produk')
                    <div id="normalActionGroup-{{ $tab }}" style="display: flex; gap: 12px;">
                        <button type="button" class="btn-action btn-danger" onclick="toggleMassDeleteMode(true)">
                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            <span>Hapus</span>
                        </button>
                        <button type="button" class="btn-action" onclick="openAddProductModal()">
                            <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                            <span>Tambah</span>
                        </button>
                    </div>
                    <div id="massDeleteActionGroup-{{ $tab }}" style="display: none; gap: 12px;">
                        <button type="button" class="btn-action" style="background: #999;" onclick="toggleMassDeleteMode(false)">
                            <span>Batal</span>
                        </button>
                        <button type="button" class="btn-action btn-danger" onclick="confirmMassDelete()">
                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            <span id="massDeleteBtnText">Hapus Terpilih (0)</span>
                        </button>
                    </div>
                @elseif($tab == 'restok')
                    <button type="button" class="btn-action" onclick="openRestokModal()">
                        <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                        <span>Tambah</span>
                    </button>
                @elseif($tab == 'transfer')
                    <button type="button" class="btn-action" onclick="openTransferModal()">
                        <iconify-icon icon="solar:transfer-horizontal-bold-duotone"></iconify-icon>
                        <span>Buat Transfer</span>
                    </button>
                @elseif($tab == 'opname')
                    <button type="button" class="btn-action" onclick="openAddOpnameModal()">
                        <iconify-icon icon="solar:clipboard-add-bold-duotone"></iconify-icon>
                        <span>Tambah Opname</span>
                    </button>
                @endif
                </div>
            </div>
        </div>

        <div class="main-content-box mobile-pb" style="position: relative;">
            <div class="table-loading-overlay">
                <div class="loading-spinner" style="width: 30px; height: 30px; border-width: 3px;"></div>
            </div>
            @fragment('tab-content-' . $tab)
                @if($tab == 'produk')
                    @include('product.partials.table_produk')
                @elseif($tab == 'stok')
                    @include('product.partials.table_stok')
                @elseif($tab == 'restok')
                    @include('product.partials.table_restok')
                @elseif($tab == 'transfer')
                    @include('product.partials.table_transfer')
                @elseif($tab == 'opname')
                    @include('product.partials.table_opname')
                @endif
            @endfragment
        </div>
    </div>
    @endforeach
    @endfragment
    </div>




<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('store_ids_add')) {
            new TomSelect('#store_ids_add', {
                plugins: ['checkbox_options'],
                maxOptions: null,
                hideSelected: false,
                closeAfterSelect: false
            });
        }
    });

    // --- Global Data Maps & State ---
    const allProductsMap = {};
    const stockAlertsMap = {};
    const productsList = {!! json_encode(isset($all_products) ? $all_products : (($active_tab == 'produk' || $active_tab == 'stok') && isset($products) ? $products->items() : []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

    function syncDataMaps() {
        // Method 1: From productsList (Blade initial load)
        if (Array.isArray(productsList)) {
            productsList.forEach(p => { if (p && p.uuid) allProductsMap[p.uuid] = p; });
        }
        
        // Method 2: From js-data-transfer (AJAX fragment updates)
        const transferEl = document.getElementById('js-data-transfer');
        if (transferEl) {
            try {
                const rawProducts = JSON.parse(transferEl.dataset.products || '[]');
                const rawAlerts = JSON.parse(transferEl.dataset.alerts || '[]');
                const productItems = Array.isArray(rawProducts) ? rawProducts : (rawProducts.data || []);
                const alertItems = Array.isArray(rawAlerts) ? rawAlerts : (rawAlerts.data || []);
                productItems.forEach(p => { if (p && p.uuid) allProductsMap[p.uuid] = p; });
                alertItems.forEach(a => { if (a && a.uuid) stockAlertsMap[a.uuid] = a; });
            } catch (e) { console.error('Error syncing maps:', e); }
        }
    }

    // Initialize maps on load
    document.addEventListener('DOMContentLoaded', () => {
        syncDataMaps();
    });

    // --- Global Helpers ---
    function openAddProductModal() {
        openModal('addModal');
        setTimeout(() => {
            const input = document.getElementById('barcodeAdd');
            if (input) {
                input.focus();
                input.select();
            }
        }, 400);
    }

    // --- Price Level Helpers (Grosir) ---
    function addPriceLevelRow(containerId, data = null) {
        const tbody = document.getElementById(containerId);
        const i = tbody.children.length;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="padding: 6px 15px 6px 0;">
                <input type="number" name="price_levels[${i}][jmlh]" class="form-control" style="font-size: 13px; height: 36px;" value="${data ? data.jmlh : ''}" placeholder="10" required>
            </td>
            <td style="padding: 6px 15px 6px 0;">
                <input type="number" name="price_levels[${i}][harga]" class="form-control" style="font-size: 13px; height: 36px;" value="${data ? data.harga : ''}" placeholder="Harga per unit" required>
            </td>
            <td style="padding: 6px 0; text-align: right;">
                <button type="button" class="btn-filter" style="width: 36px; height: 36px; color: #D9534F; border-color: #FFEBEE; display: flex; align-items: center; justify-content: center; margin-left: 8px;" onclick="this.closest('tr').remove()">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    }

    // --- Dropdown Logic ---
    function toggleDropdown(event) {
        const dropdown = event.currentTarget.closest('.dropdown');
        const content = dropdown.querySelector('.dropdown-content');
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => {
            if (d !== content) d.classList.remove('show');
        });
        
        content.classList.toggle('show');
    }

    // --- Mass Delete Logic ---
    let isMassDeleteMode = false;
    function toggleMassDeleteMode(active) {
        isMassDeleteMode = active;
        document.getElementById('normalActionGroup').style.display = active ? 'none' : 'flex';
        document.getElementById('massDeleteActionGroup').style.display = active ? 'flex' : 'none';
        document.querySelectorAll('.mass-delete-checkbox').forEach(cb => cb.style.display = active ? 'table-cell' : 'none');
        if (!active) {
            document.getElementById('selectAllCheckbox').checked = false;
            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
            updateMassDeleteCount();
        }
    }
    function toggleSelectAll() {
        const check = document.getElementById('selectAllCheckbox').checked;
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = check);
        updateMassDeleteCount();
    }
    function updateMassDeleteCount() {
        const count = document.querySelectorAll('.product-checkbox:checked').length;
        document.getElementById('massDeleteBtnText').innerText = `Konfirmasi Hapus (${count})`;
    }
    function confirmMassDelete() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        if (selected.length === 0) return Swal.fire('Pilih Produk', 'Centang produk yang ingin dihapus.', 'warning');
        Swal.fire({
            title: `Hapus ${selected.length} Produk?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Hapus!'
        }).then(r => {
            if (r.isConfirmed) {
                showLoading(); // Show loading feedback
                const form = document.createElement('form');
                form.method = 'POST'; 
                form.action = '{{ route("products.mass_destroy") }}';
                form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                selected.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                    form.appendChild(input);
                });
                document.body.appendChild(form); form.submit();
            }
        });
    }

    function confirmDelete(uuid, nama) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: `Yakin ingin menghapus ${nama}? Tindakan ini tidak bisa dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Hapus!'
        }).then(r => {
            if (r.isConfirmed) {
                showLoading(); // Show loading feedback
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/products/${uuid}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // --- Filter logic (AJAX based) ---
    function setCategory(id) {
        const input = document.getElementById('hiddenCategoryId');
        if (input) {
            input.value = id;
            updateTableContent();
        }
    }

    function setStore(id) {
        const input = document.getElementById('hiddenStoreId');
        if (input) {
            input.value = id;
            updateTableContent();
        }
    }

    function setTransferStore(id) {
        const input = document.getElementById('hiddenTransferStoreId');
        if (input) {
            input.value = id;
            updateTableContent();
        }
    }

    function setTransferStatus(status) {
        const input = document.getElementById('hiddenTransferStatus');
        if (input) {
            input.value = status;
            updateTableContent();
        }
    }

    function setTimeFilter(val) {
        const input = document.getElementById('hiddenTimeFilter');
        if (input) {
            input.value = val;
            updateTableContent();
        }
    }

    function setPaymentFilter(val) {
        const input = document.getElementById('hiddenPaymentFilter');
        if (input) {
            input.value = val;
            updateTableContent();
        }
    }

    function setSupplierFilter(id) {
        const input = document.getElementById('hiddenSupplierId');
        if (input) {
            input.value = id;
            updateTableContent();
        }
    }

    // --- Product Actions ---
    async function openViewModal(productUuid, alertData = null) {
        let product = typeof productUuid === 'string' ? allProductsMap[productUuid] : productUuid;
        
        // Data check: If product is missing or is an incomplete object (missing critical mapped fields)
        const isIncomplete = product && (!product.nama_category || !product.price_levels || !product.stores);

        if (!product || isIncomplete) {
            try {
                const response = await fetch(`/products/detail/${typeof productUuid === 'string' ? productUuid : product.uuid}`);
                if (!response.ok) throw new Error('Failed to fetch');
                product = await response.json();
                allProductsMap[product.uuid] = product;
            } catch (err) {
                console.error('ViewModal Fetch Error:', err);
                Swal.fire('Error', 'Gagal memuat data produk dari server.', 'error');
                return;
            }
        }

        if (!product) {
            Swal.fire('Error', 'Data produk tidak ditemukan.', 'error');
            return;
        }
        
        const modalBody = document.getElementById('viewDetailContent');
        const priceModal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.harga_modal || 0);
        const priceSell = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.harga_jual || 0);
        
        let plHtml = '';
        if (product.price_levels && product.price_levels.length > 0) {
            plHtml = `
                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <div style="font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 10px;">Harga Grosir / Bertingkat:</div>
                    <div style="background: #f8fafc; border-radius: 8px; padding: 12px;">
                        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e2e8f0; text-align: left; color: #94a3b8; font-size: 11px; text-transform: uppercase;">
                                    <th style="padding-bottom: 6px;">Minimal Qty</th>
                                    <th style="padding-bottom: 6px;">Harga Per Pcs</th>
                                </tr>
                            </thead>
                            <tbody>`;
            product.price_levels.forEach(pl => {
                plHtml += `
                    <tr>
                        <td style="font-weight: 600; padding: 4px 0;">&ge; ${pl.jmlh} Pcs</td>
                        <td style="font-weight: 700; color: #C62828;">Rp ${new Intl.NumberFormat('id-ID').format(pl.harga)}</td>
                    </tr>`;
            });
            plHtml += `</tbody></table></div></div>`;
        }

        let branchStockHtml = '';
        const isOwner = {{ Auth::user()->isOwner() ? 'true' : 'false' }};
        if (isOwner && product.stores && product.stores.length > 0) {
            branchStockHtml = `
                <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="solar:globus-bold-duotone" style="font-size: 18px; color: var(--primary-blue);"></iconify-icon>
                        Rincian Stok per Cabang
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                        ${product.stores.map(ps => `
                            <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;">${ps.store ? ps.store.nama : 'Cabang'}</div>
                                    <div style="font-size: 11px; color: #64748b;">Lokasi Stok</div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 20px; font-weight: 800; color: var(--primary-blue);">${ps.stok}</span>
                                    <span style="font-size: 12px; color: #94a3b8;">Pcs</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else if (!isOwner && alertData) {
             branchStockHtml = `
                <div style="background: #f0f7ff; padding: 12px; border-radius: 12px; border: 1px solid #d0e7ff; display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <div>
                        <div style="font-size: 11px; color: #0056b3; text-transform: uppercase; font-weight: 700;">Stok di Outlet Anda</div>
                        <div style="font-size: 18px; font-weight: 800; color: #0056b3;">${alertData.stok || 0} <span style="font-size: 12px; font-weight: 400;">Pcs</span></div>
                    </div>
                    <iconify-icon icon="solar:shop-2-bold-duotone" style="font-size: 32px; color: #0056b3; opacity: 0.3;"></iconify-icon>
                </div>
             `;
        }

        const imgUrl = product.resolved_image_url || 
                       (product.image_url && product.image_url.startsWith('http') ? product.image_url : 
                       (product.image_url ? `/storage/${product.image_url}` : 'https://placehold.co/200x200?text=No+Image'));

        const catName = product.nama_category || (product.category ? product.category.nama_category : 'Tanpa Kategori');

        modalBody.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; gap: 20px; align-items: start;">
                    <img src="${imgUrl}" 
                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; border: 1px solid #f1f5f9; background: #fff;" 
                         onerror="this.src='https://placehold.co/200x200?text=No+Image'">
                    <div style="flex: 1;">
                        <div style="font-size: 18px; font-weight: 700; color: var(--primary-blue);">${product.nama_produk || 'Produk'}</div>
                        <div style="color: #64748b; font-size: 14px; margin-top: 4px;">Barcode: ${product.barcode || '-'}</div>
                        <div style="margin-top: 12px; display: inline-block; padding: 4px 12px; background: #eff6ff; color: var(--primary-blue); border-radius: 50px; font-size: 12px; font-weight: 600;">
                            ${catName}
                        </div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; border-radius: 12px; padding: 15px;">
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Harga Modal</div>
                        <div style="font-size: 16px; font-weight: 700; color: #D9534F;">${priceModal}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Harga Jual</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--primary-blue);">${priceSell}</div>
                    </div>
                </div>
                ${branchStockHtml}
                ${plHtml}
            </div>
        `;
        openModal('viewModal');
    }

    function openViewModalFromAlert(alertUuid) {
        const alertData = stockAlertsMap[alertUuid];
        if (!alertData || !alertData.product) {
            console.error('openViewModalFromAlert: Alert data missing for UUID:', alertUuid);
            Swal.fire('Error', 'Data detail tidak ditemukan.', 'error');
            return;
        }
        openViewModal(alertData.product, alertData);
    }

    function openOpnameDetailModal(uuid) {
        showLoading('Memuat Detail...');
        
        fetch(`/products/opname-detail/${uuid}`)
            .then(r => r.json())
            .then(d => {
                hideLoading();
                document.getElementById('det_op_tanggal').innerText = new Date(d.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('det_op_toko').innerText = d.store ? d.store.nama : '-';
                document.getElementById('det_op_petugas').innerText = d.user ? (d.user.name || d.user.username) : '-';
                
                const tbody = document.getElementById('opnameDetailRows');
                tbody.innerHTML = '';
                
                d.details.forEach(it => {
                    let diffColor = '#2E7D32'; 
                    let diffIcon = 'solar:check-circle-bold';
                    if (it.selisih != 0) {
                        diffColor = '#D9534F'; 
                        diffIcon = 'solar:danger-bold';
                    }
                    
                    const diffText = it.selisih > 0 ? `+${it.selisih}` : it.selisih;
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div>
                                    <div style="font-weight: 600;">${it.product ? it.product.nama_produk : 'Produk Terhapus'}</div>
                                    <div style="font-size: 11px; color: #888;">${it.product ? (it.product.barcode || '-') : '-'}</div>
                                </div>
                                <iconify-icon icon="${diffIcon}" style="font-size: 18px; color: ${diffColor};"></iconify-icon>
                            </div>
                        </td>
                        <td style="text-align: center; font-weight: 600;">${it.stok_sistem}</td>
                        <td style="text-align: center; font-weight: 600;">${it.stok_fisik}</td>
                        <td style="text-align: center;">
                             <span style="font-weight: 800; color: ${diffColor}; background: ${diffColor}10; padding: 2px 8px; border-radius: 4px;">${diffText}</span>
                        </td>
                        <td>
                            <div style="font-weight: 500;">${it.keterangan || '-'}</div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                
                const isOwner = {{ Auth::user()->isOwner() ? 'true' : 'false' }};
                const finalizeArea = document.getElementById('opnameFinalizeArea');
                const closeArea = document.getElementById('opnameDetailCloseArea');
                const finalizeBtn = document.getElementById('btnFinalizeOpnameAction');
                
                if (finalizeArea && closeArea) {
                    if (isOwner && d.status === 'Pending') {
                        finalizeArea.style.display = 'block';
                        closeArea.style.display = 'none';
                        finalizeBtn.onclick = () => confirmFinalizeOpname(uuid);
                    } else {
                        finalizeArea.style.display = 'none';
                        closeArea.style.display = 'flex';
                    }
                }
                
                openModal('opnameDetailModal');
            })
            .catch(() => {
                hideLoading();
                Swal.fire('Error', 'Gagal memuat detail opname.', 'error');
            });
    }

    function confirmFinalizeOpname(uuid) {
        Swal.fire({
            title: 'Finalisasi Opname?',
            text: "Stok produk akan di-update sesuai data fisik yang diinput petugas. Tindakan ini tidak bisa dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2E7D32',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Finalisasi & Update Stok',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/products/opname/${uuid}/finalize`;
                form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    async function openEditModal(productUuid) {
        let product = allProductsMap[productUuid];
        
        const isIncomplete = product && (!product.nama_category || !product.price_levels);

        if (!product || isIncomplete) {
            try {
                const response = await fetch(`/products/detail/${productUuid}`);
                if (!response.ok) throw new Error('Failed to fetch');
                product = await response.json();
                allProductsMap[productUuid] = product;
            } catch (err) {
                Swal.fire('Error', 'Gagal memuat data edit dari server.', 'error');
                return;
            }
        }
        const form = document.getElementById('editForm');
        form.action = `/products/${product.uuid}`;
        document.getElementById('edit_nama').value = product.nama_produk || '';
        document.getElementById('edit_barcode').value = product.barcode || '';
        document.getElementById('edit_kategori').value = product.kategori_id || '';
        document.getElementById('edit_modal').value = product.harga_modal || 0;
        document.getElementById('edit_jual').value = product.harga_jual || 0;
        
        const resultInput = document.getElementById('editCroppedImageResult');
        if (resultInput) resultInput.value = '';

        const tbody = document.getElementById('editPriceLevelBody');
        tbody.innerHTML = '';
        if (product.price_levels && product.price_levels.length > 0) {
            product.price_levels.forEach(level => addPriceLevelRow('editPriceLevelBody', level));
        }

        const previewContainer = document.getElementById('editImagePreviewContainer');
        if (previewContainer) {
            if (product.resolved_image_url) {
                previewContainer.innerHTML = `<img src="${product.resolved_image_url}" style="width:100%; height:100%; object-fit:cover; display:block;">`;
                previewContainer.style.border = 'none';
                previewContainer.style.background = 'white';
            } else {
                previewContainer.innerHTML = `
                    <div style="text-align: center;">
                        <iconify-icon icon="solar:camera-add-bold-duotone" style="font-size: 40px; color: #94a3b8;"></iconify-icon>
                        <p style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Klik untuk Pilih/Foto</p>
                    </div>`;
                previewContainer.style.border = '2px dashed #cbd5e1';
                previewContainer.style.background = '#f8fafc';
            }
        }

        openModal('editModal');
    }

    function confirmDelete(uuid, name) {
        Swal.fire({
            title: 'Hapus Produk?', text: `Yakin hapus ${name}?`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#D9534F', confirmButtonText: 'Ya, Hapus!'
        }).then(r => {
            if (r.isConfirmed) {
                showLoading('Sedang Menghapus Produk...');
                const form = document.createElement('form');
                form.method = 'POST'; form.action = `/products/${uuid}`;
                form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form); form.submit();
            }
        });
    }

    // --- Smart Multi-Pass Image Scanner Logic ---
    const processImage = (file, filter = 'none', cropCenter = false, threshold = 0, angle = 0, stripCrop = false) => {
        return new Promise((r) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let w = img.width, h = img.height, sx = 0, sy = 0, sw = w, sh = h;
                if (cropCenter) { sx = w*0.15; sy = h*0.15; sw = w*0.7; sh = h*0.7; }
                else if (stripCrop) { sy = h*0.35; sh = h*0.3; }
                const MAX = 1000; const scale = Math.min(1, MAX/Math.max(sw, sh));
                const dw = sw*scale, dh = sh*scale, p = 150;
                canvas.width = dw+p*2; canvas.height = dh+p*2;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = 'white'; ctx.fillRect(0,0,canvas.width,canvas.height);
                ctx.save(); ctx.translate(canvas.width/2, canvas.height/2);
                if (angle) ctx.rotate(angle * Math.PI/180);
                if (filter !== 'none') ctx.filter = filter;
                ctx.drawImage(img, sx, sy, sw, sh, -dw/2, -dh/2, dw, dh); ctx.restore();
                if (threshold > 0) {
                    const idata = ctx.getImageData(0,0,canvas.width,canvas.height); const data = idata.data;
                    for (let i=0; i<data.length; i+=4) {
                        const gray = (data[i]+data[i+1]+data[i+2])/3;
                        const v = gray > threshold ? 255 : 0; data[i]=data[i+1]=data[i+2]=v;
                    }
                    ctx.putImageData(idata, 0, 0);
                }
                canvas.toBlob(r, 'image/png');
            };
            img.src = (file instanceof Blob) ? URL.createObjectURL(file) : file;
        });
    };

    function quaggaScan(blob) {
        return new Promise((r, j) => {
            const reader = new FileReader();
            reader.onload = () => {
                Quagga.decodeSingle({
                    src: reader.result, numOfWorkers: 0,
                    decoder: { readers: ["ean_reader", "upc_reader", "upc_e_reader", "code_128_reader", "code_39_reader"] },
                    locate: true
                }, res => (res && res.codeResult) ? r(res.codeResult.code) : j());
            };
            reader.readAsDataURL(blob);
        });
    }

    async function tryBothScanners(file, decoder) {
        // Try Html5Qrcode first
        try {
            const res = await decoder.scanFile(file, true);
            if (res) return res;
        } catch(e) {}

        // Fallback to Quagga
        try {
            return await quaggaScan(file);
        } catch(e) {
            return null;
        }
    }

    async function handleBarcodeImageScan(event, targetInputId) {
        const file = event.target.files[0];
        if (!file) return;

        showLoading('Menganalisis Gambar (Unified Scan)...');
        
        try {
            const tempDecoder = new Html5Qrcode("barcode-scanner-container");
            let result = null;

            // PASS 1: Original
            result = await tryBothScanners(file, tempDecoder);

            // PASS 2: High Contrast Grayscale
            if (!result) {
                const b = await processImage(file, 'contrast(1.6) grayscale(1)');
                result = await tryBothScanners(new File([b], "p2.png"), tempDecoder);
            }

            // PASS 3: Brightness + Sharpness
            if (!result) {
                const b = await processImage(file, 'brightness(1.1) contrast(1.3)', true);
                result = await tryBothScanners(new File([b], "p3.png"), tempDecoder);
            }

            // PASS 4: Rotation passes
            if (!result) {
                for (let angle of [-8, 8, -15, 15]) {
                    const b = await processImage(file, 'none', false, 0, angle);
                    result = await tryBothScanners(new File([b], `p4_${angle}.png`), tempDecoder);
                    if (result) break;
                }
            }
            
            if (result) {
                hideLoading();
                const input = document.getElementById(targetInputId);
                input.value = result;
                if (targetInputId === 'barcodeAdd') lookupProductByBarcode(result);

                Swal.fire({
                    icon: 'success',
                    title: 'Barcode Terbaca!',
                    text: result,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                throw new Error("No barcode found");
            }
        } catch (err) {
            hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membaca',
                text: 'Barcode sulit dibaca. Pastikan gambar jelas, tidak silau, dan posisi barcode mendatar.',
            });
        }
        event.target.value = ''; 
    }

    function lookupProductByBarcode(barcode) {
        if (!barcode) return;
        const cleanBarcode = barcode.toString().trim();
        if (cleanBarcode === '') return;

        showLoading('Mencari di Database Global...');
        
        // Use encodeURIComponent to handle special characters or hidden scanner codes
        fetch(`https://world.openfoodfacts.org/api/v0/product/${encodeURIComponent(cleanBarcode)}.json`)
            .then(r => r.json())
            .then(d => {
                hideLoading();
                if (d.status === 1 && d.product) {
                    const p = d.product;
                    
                    // Ambil Brand, Nama, dan Volume
                    const brand = p.brands ? p.brands.split(',')[0].trim() : '';
                    const name = p.product_name || '';
                    const volume = p.quantity || '';
                    
                    // Gabungkan menjadi satu nama lengkap yang rapi
                    const fullName = `${brand} ${name} ${volume}`.trim().replace(/\s+/g, ' ');
                    
                    const inputNama = document.getElementById('addNamaProduk');
                    if (inputNama) {
                        inputNama.value = fullName;
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Ditemukan!',
                            html: `Detail Produk:<br><b>${fullName}</b>`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info Produk',
                        text: 'Data produk tidak ditemukan di database global, silakan isi manual.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            })
            .catch(err => {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Gagal menghubungi database produk global.'
                });
            });
    }

    function openAddCategoryModal() {
        openModal('addCategoryModal', 30000); // Higher z-index to stay on top
    }

    // --- AJAX Category Submission ---
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-lookup for hardware scanners or manual typing in the Add Modal
        const barcodeInput = document.getElementById('barcodeAdd');
        if (barcodeInput) {
            let timeout = null;
            barcodeInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const val = this.value.trim();
                if (val.length >= 8) { // Typical barcode length
                    timeout = setTimeout(() => lookupProductByBarcode(val), 800);
                }
            });
            // Handle ENTER from hardware scanners
            barcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupProductByBarcode(this.value.trim());
                }
            });
        }

        const catForm = document.getElementById('addCategoryForm');
        if (catForm) {
            catForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerText;

                submitBtn.disabled = true;
                submitBtn.innerText = 'Menyimpan...';

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const newCat = data.category;
                        // Update all category dropdowns
                        document.querySelectorAll('select[name="kategori_id"]').forEach(select => {
                            const opt = new Option(newCat.nama_category, newCat.uuid);
                            select.add(opt);
                            select.value = newCat.uuid; // Auto select in current modal
                        });

                        closeModal('addCategoryModal');
                        this.reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Kategori baru telah ditambahkan dan dipilih.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menyimpan kategori');
                    }
                })
                .catch(err => {
                    Swal.fire('Gagal!', err.message || 'Terjadi kesalahan sistem.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalText;
                });
            });
        }
    });

    function openEditAlertModal(alertUuid) {
        const alertData = stockAlertsMap[alertUuid];
        if (!alertData) {
            console.error('openEditAlertModal: Alert data not found for UUID:', alertUuid);
            Swal.fire('Error', 'Data stok tidak ditemukan.', 'error');
            return;
        }

        const product = alertData.product || {};
        const form = document.getElementById('editAlertForm');
        if (!form) return;
        
        form.action = `/products/store-data/${alertUuid}`;
        
        const alertImg = document.getElementById('editAlertImage');
        if (alertImg) {
            const imgUrl = product.resolved_image_url || (product.image_url ? `/storage/${product.image_url}` : 'https://placehold.co/200x200?text=No+Image');
            alertImg.src = imgUrl;
        }
        
        const alertName = document.getElementById('editAlertName');
        if (alertName) alertName.innerText = product.nama_produk || 'Produk Tidak Diketahui';
        
        const alertBarcode = document.getElementById('editAlertBarcode');
        if (alertBarcode) alertBarcode.innerText = product.barcode || '-';
        
        const alertStore = document.getElementById('editAlertStoreName');
        if (alertStore) {
            const storeName = (alertData.store ? alertData.store.nama : null) || alertData.store_name || '-';
            alertStore.innerText = storeName;
        }
        
        const alertDate = document.getElementById('editAlertDateMasuk');
        if (alertDate) alertDate.innerText = alertData.tanggal_masuk || '-';
        
        const inputStok = document.getElementById('alert_stok');
        if (inputStok) inputStok.value = alertData.stok || 0;

        const inputExp = document.getElementById('alert_kadaluarsa');
        if (inputExp) inputExp.value = alertData.kadaluarsa ? alertData.kadaluarsa.split(' ')[0] : '';

        const inputMin = document.getElementById('alert_stok_minimum');
        if (inputMin) inputMin.value = alertData.stok_minimum || 10;
        
        const statusCheck = document.getElementById('alert_status_aktif');
        if (statusCheck) {
            statusCheck.checked = alertData.status_aktif !== false;
            const statusLabel = document.getElementById('statusLabel');
            if (statusLabel) {
                statusLabel.innerText = statusCheck.checked ? 'Aktif' : 'Nonaktif';
                statusLabel.style.color = statusCheck.checked ? '#2E7D32' : '#64748b';
            }
        }
        openModal('editAlertModal');
    }

    function openAddProductModal() {
        openModal('addModal');
        setTimeout(() => {
            const input = document.getElementById('barcodeAdd');
            if (input) {
                input.focus();
                input.select();
            }
        }, 400);
    }

    // --- Generic Cropper Logic ---
    let cropper = null;
    let currentActiveInput = null;

    function initCropper(inputElement, previewContainer, resultInput) {
        if (!inputElement || !previewContainer || !resultInput) return;
        
        inputElement.addEventListener('change', e => {
            const f = e.target.files[0]; 
            if (!f) return;
            
            // Validate file type
            if (!f.type.startsWith('image/')) {
                Swal.fire('Error', 'File yang dipilih bukan gambar.', 'error');
                return;
            }

            currentActiveInput = { preview: previewContainer, result: resultInput };
            const reader = new FileReader();
            
            showLoading('Menyiapkan Pemotong Foto...');
            
            reader.onload = ev => {
                const cropImg = document.getElementById('cropperImage');
                cropImg.src = ev.target.result; 
                
                // Wait for image to load before initializing cropper
                cropImg.onload = () => {
                    hideLoading();
                    openModal('cropperModal', 999999);
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(cropImg, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                };
            };
            reader.readAsDataURL(f);
        });
    }

    // untuk tambah produk
    initCropper(
        document.getElementById('productImageInput'), 
        document.getElementById('imagePreviewContainer'), 
        document.getElementById('croppedImageResult')
    );

    // untuk edit produk
    if (document.getElementById('editProductImageInput')) {
        initCropper(
            document.getElementById('editProductImageInput'), 
            document.getElementById('editImagePreviewContainer'), 
            document.getElementById('editCroppedImageResult')
        );
    }

    function closeCropperModal() { 
        document.getElementById('cropperModal').style.display='none'; 
        if (cropper) cropper.destroy(); 
    }

    function applyCrop() {
        if (!cropper || !currentActiveInput) {
            Swal.fire('Gagal', 'Sesi pemotong foto tidak aktif.', 'error');
            return;
        }
        
        showLoading('Menerapkan Potongan Foto...');
        
        // Use higher quality for premium feel
        const canvas = cropper.getCroppedCanvas({ 
            width: 800, 
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        const b64 = canvas.toDataURL('image/jpeg', 0.9);
        
        currentActiveInput.result.value = b64;
        currentActiveInput.preview.innerHTML = `<img src="${b64}" style="width:100%; height:100%; object-fit:cover; display:block;">`;
        
        // Remove dashed border and background for a cleaner look when image is present
        currentActiveInput.preview.style.border = 'none';
        currentActiveInput.preview.style.background = 'white';
        
        // Clear photo error state
        const errorId = currentActiveInput.preview.id === 'imagePreviewContainer' ? 'imageErrorAdd' : 'imageErrorEdit';
        const errorEl = document.getElementById(errorId);
        if (errorEl) errorEl.style.display = 'none';
        
        setTimeout(() => {
            hideLoading();
            closeCropperModal();
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')) });
        @endif
        @if($errors->any())
            Swal.fire({ 
                icon: 'error', 
                title: 'Kesalahan Input', 
                html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{!! addslashes(e($error)) !!}</li>@endforeach</ul>'
            });
        @endif
    });

    // Global Form Submit Listener removed to prevent 'ghost' loading triggers.
    // We now use specific 'onsubmit' handlers on each form for better control.

    let abortController = null;
    window.currentTab = '{{ $active_tab }}';
    window.fullDataCache = {};
    window.isFetchingFullData = {};
    window.initialTbodyHtml = {};

    function loadFullDataInBackground(tab) {
        if (window.fullDataCache[tab] || window.isFetchingFullData[tab]) return;
        
        window.isFetchingFullData[tab] = true;
        const params = new URLSearchParams(window.location.search);
        params.set('tab', tab);
        params.set('limit', 'all');
        const url = window.location.pathname + '?' + params.toString();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const tableMap = {
                    'produk': 'produkTable',
                    'stok': 'stokTable',
                    'restok': 'restokTable',
                    'transfer': 'transferTable',
                    'opname': document.getElementById('rugiTable') ? 'rugiTable' : 'opnameTable'
                };
                
                const tableId = tableMap[tab];
                const fetchedTable = doc.getElementById(tableId);
                
                if (fetchedTable) {
                    const tbody = fetchedTable.querySelector('tbody');
                    if (tbody) {
                        window.fullDataCache[tab] = Array.from(tbody.querySelectorAll('tr'));
                    }
                }
                window.isFetchingFullData[tab] = false;
                
                // Re-trigger search if user already typed
                const searchInput = document.getElementById(`searchInput-${tab}`);
                if (searchInput && searchInput.value) {
                    realtimeSearch(tab);
                }
            })
            .catch(() => {
                window.isFetchingFullData[tab] = false;
            });
    }

    function realtimeSearch(tab = 'produk') {
        const inputId = `searchInput-${tab}`;
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const filter = input.value.toLowerCase();
        
        const tableMap = {
            'produk': 'produkTable',
            'stok': 'stokTable',
            'restok': 'restokTable',
            'transfer': 'transferTable',
            'opname': document.getElementById('rugiTable') ? 'rugiTable' : 'opnameTable'
        };
        const tableId = tableMap[tab];
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const pagination = document.querySelector(`#section-${tab} .pagination-container`);

        if (!window.initialTbodyHtml[tab]) {
            window.initialTbodyHtml[tab] = tbody.innerHTML;
        }

        if (filter === '') {
            tbody.innerHTML = window.initialTbodyHtml[tab];
            if (pagination) pagination.style.display = 'block';
            return;
        }

        if (window.fullDataCache[tab]) {
            const allTrs = window.fullDataCache[tab];
            const fragment = document.createDocumentFragment();
            let matchCount = 0;

            for (let i = 0; i < allTrs.length; i++) {
                let found = false;
                const td = allTrs[i].getElementsByTagName("td");
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                if (found) {
                    fragment.appendChild(allTrs[i].cloneNode(true));
                    matchCount++;
                    if (matchCount > 100) break;
                }
            }

            if (matchCount === 0) {
                const emptyTr = document.createElement('tr');
                const colCount = table.querySelector('thead tr').children.length;
                emptyTr.innerHTML = `<td colspan="${colCount}" style="text-align: center; padding: 40px; color: #999;">Tidak ada data ditemukan untuk "${input.value}"</td>`;
                fragment.appendChild(emptyTr);
            }

            tbody.innerHTML = '';
            tbody.appendChild(fragment);

            if (pagination) {
                pagination.style.display = 'none';
            }
        } else {
            const tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let found = false;
                const td = tr[i].getElementsByTagName("td");
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }
    }

    let selectedProducts = [];
    function toggleMassDeleteMode(active) {
        document.getElementById('normalActionGroup').style.display = active ? 'none' : 'flex';
        document.getElementById('massDeleteActionGroup').style.display = active ? 'flex' : 'none';
        
        document.querySelectorAll('.mass-delete-checkbox').forEach(el => {
            el.style.display = active ? 'table-cell' : 'none';
        });
        
        if (!active) {
            selectedProducts = [];
            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
            updateMassDeleteCount();
        }
    }

    function toggleSelectAll() {
        const isChecked = document.getElementById('selectAllCheckbox').checked;
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            cb.checked = isChecked;
        });
        updateMassDeleteCount();
    }

    function updateMassDeleteCount() {
        selectedProducts = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            selectedProducts.push(cb.value);
        });
        const btnText = document.getElementById('massDeleteBtnText');
        if (btnText) btnText.innerText = `Hapus Terpilih (${selectedProducts.length})`;
    }

    function confirmMassDelete() {
        if (selectedProducts.length === 0) {
            Swal.fire('Peringatan', 'Pilih minimal satu produk untuk dihapus.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Hapus Massal?',
            text: `Yakin ingin menghapus ${selectedProducts.length} produk terpilih? Tindakan ini tidak bisa dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Menghapus Produk...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('products.mass-delete') }}";
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="uuids" value="${selectedProducts.join(',')}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }





    window.currentTab = '{{ $active_tab }}';

    function switchTab(tabName, event) {
        if (event) event.preventDefault();
        window.currentTab = tabName;
        loadFullDataInBackground(tabName);
        
        // Hide all sections
        document.querySelectorAll('.view-section').forEach(s => {
            s.classList.remove('active');
        });
        
        // Show target section
        const target = document.getElementById('section-' + tabName);
        if (target) {
            target.classList.add('active');
        }
        
        // Update pills using data-tab attribute for exact matching
        document.querySelectorAll('.tab-pill').forEach(p => {
            if (p.getAttribute('data-tab') === tabName) {
                p.classList.add('active');
            } else {
                p.classList.remove('active');
            }
        });
        
        // Update URL and ensure we keep the query parameters clean
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({ tab: tabName }, '', url);
        
        // No need to fetch via AJAX here because all tabs are loaded on initial request now!
    }

    // Handle initial load and back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.tab) {
            switchTab(event.state.tab);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab) {
            const pill = document.querySelector(`.tab-pill[data-tab="${tab}"]`);
            if (pill) {
                pill.click(); 
            } else {
                loadFullDataInBackground(tab);
            }
        } else {
            loadFullDataInBackground(window.currentTab || 'produk');
        }
    });

    const pageCache = new Map();

    async function updateTableContent(url = null) {
        if (abortController) abortController.abort();
        abortController = new AbortController();

        // Close all dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));

        if (!url) {
            const params = new URLSearchParams(window.location.search);
            params.set('tab', window.currentTab || 'produk');
            url = window.location.pathname + '?' + params.toString();
        }

        const urlObj = new URL(url, window.location.origin);
        // Selalu gunakan window.currentTab saat navigasi ajax agar tidak terlempar ke tab lain
        // akibat query string 'tab' bawaan dari link pagination Laravel.
        const tab = window.currentTab || 'produk';
        urlObj.searchParams.set('tab', tab);
        const finalUrl = urlObj.toString();

        const activeSection = document.getElementById(`section-${tab}`);
        
        // Instant check: if we already have it in cache, show it immediately!
        if (pageCache.has(finalUrl)) {
            applyNewTableHtml(pageCache.get(finalUrl), tab);
            window.history.pushState({ tab: tab }, '', finalUrl);
            window.currentTab = tab;
            return;
        }

        try {
            const response = await fetch(finalUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: abortController.signal
            });
            const html = await response.text();
            
            if (html.includes('menu-nav') || html.includes('sidebar')) {
                window.location.reload();
                return;
            }

            pageCache.set(finalUrl, html); // Cache it for next time
            applyNewTableHtml(html, tab);

            window.history.pushState({ tab: tab }, '', finalUrl);
            window.currentTab = tab;
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('AJAX Navigation Failed:', error);
            if (!url.includes('javascript:')) window.location.href = finalUrl;
        }
    }

    function applyNewTableHtml(html, tab) {
        const activeSection = document.getElementById(`section-${tab}`);
        const targetContainer = activeSection ? activeSection.querySelector('.main-content-box') : null;
        if (targetContainer) {
            const currentOverlay = targetContainer.querySelector('.table-loading-overlay');
            targetContainer.innerHTML = '';
            if (currentOverlay) targetContainer.appendChild(currentOverlay);
            targetContainer.insertAdjacentHTML('beforeend', html);
            
            syncDataMaps();
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            // Subtle scroll only if needed
            const rect = targetContainer.getBoundingClientRect();
            if (rect.top < 0) {
                window.scrollTo({ top: window.pageYOffset + rect.top - 100, behavior: 'auto' });
            }

            // After successful apply, pre-fetch next page links to make next click instant
            prefetchAdjacentPages(activeSection);
        } else {
            const contentArea = document.getElementById('ajax-content-area');
            if (contentArea) contentArea.innerHTML = html;
        }
    }

    function prefetchAdjacentPages(container) {
        const nextLink = container.querySelector('.pagination .page-item:not(.active):not(.disabled) a, .twins-pagination .twins-page-item:not(.active):not(.disabled) a');
        if (nextLink && !pageCache.has(nextLink.href)) {
            fetch(nextLink.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => pageCache.set(nextLink.href, html))
                .catch(() => {});
        }
    }

    function applyFilter(key, value, event) {
        if (event) event.preventDefault();
        const params = new URLSearchParams(window.location.search);
        const tab = window.currentTab || 'produk';
        params.set('tab', tab);
        
        if (value && value !== 'all' && value !== '') {
            params.set(key, value);
        } else {
            params.delete(key);
        }

        // Close all dropdowns for clean UI
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));

        // Also preserve current search if applicable
        const searchInput = document.getElementById(`searchInput-${tab}`);
        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        }

        // Clear cache for other tabs if global filters (store, search) change
        if (key === 'store_id' || key === 'search') {
            pageCache.clear();
        }

        const newUrl = window.location.pathname + '?' + params.toString();
        
        // Update icon color in ALL sections immediately for visual feedback
        document.querySelectorAll('.view-section').forEach(section => {
            const btnIcon = section.querySelector(`button[data-filter-key="${key}"] iconify-icon`);
            if (btnIcon) {
                if (value && value !== 'all' && value !== '') btnIcon.classList.add('text-primary-blue');
                else btnIcon.classList.remove('text-primary-blue');
            }
        });

        updateTableContent(newUrl);
    }

    // Intercept Pagination for High Performance
    document.addEventListener('click', function(e) {
        // Pagination links (intercept for AJAX)
        const paginationLink = e.target.closest('.pagination a, .twins-pagination a');
        if (paginationLink && !paginationLink.href.includes('javascript:')) {
            e.preventDefault();
            updateTableContent(paginationLink.href);
            return;
        }
    });

    // Aggressive Prefetching: Start loading next pages as soon as user hovers over them
    document.addEventListener('mouseover', function(e) {
        const link = e.target.closest('.pagination a, .twins-pagination a');
        if (link && link.href && !pageCache.has(link.href) && !link.href.includes('javascript:')) {
            fetch(link.href, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                priority: 'low' 
            }).then(r => r.text()).then(html => {
                if (!html.includes('sidebar')) pageCache.set(link.href, html);
            }).catch(() => {});
        }
    });

    // Idle Prefetching: Load all pagination links when browser is idle
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            const activeSection = document.querySelector('.view-section.active');
            if (activeSection) prefetchAdjacentPages(activeSection);
        });
    } else {
        setTimeout(() => {
            const activeSection = document.querySelector('.view-section.active');
            if (activeSection) prefetchAdjacentPages(activeSection);
        }, 2000);
    }

    // Duplicate filter functions removed (already defined above)

    function applyDateFilter() {
        const start = document.getElementById('inputStartDate').value;
        const end = document.getElementById('inputEndDate').value;
        
        document.getElementById('hiddenStartDate').value = start;
        document.getElementById('hiddenEndDate').value = end;
        
        updateTableContent();
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    }

    // --- Export Handling ---
    function triggerExport(type) {
        const params = new URLSearchParams(window.location.search);
        
        // Pastikan active_tab terkirim dengan benar
        const tab = window.currentTab || '{{ $active_tab }}';
        params.set('active_tab', tab);

        // Ambil nilai dari kotak pencarian (jika ada)
        const searchInput = document.getElementById(`searchInput-${tab}`);
        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        }

        const baseUrl = type === 'excel' ? "{{ route('products.export.excel') }}" : "{{ route('products.export.pdf') }}";
        const finalUrl = baseUrl + '?' + params.toString();

        if (type === 'pdf') {
            window.open(finalUrl, '_blank');
        } else {
            window.location.href = finalUrl;
        }
    }

    function clearDateFilter() {
        document.getElementById('inputStartDate').value = '';
        document.getElementById('inputEndDate').value = '';
        document.getElementById('hiddenStartDate').value = '';
        document.getElementById('hiddenEndDate').value = '';
        
        updateTableContent();
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    }
    async function scanFromProductImage() {
        const b64 = document.getElementById('croppedImageResult').value;
        if (!b64) return;
        showLoading('Menganalisis Foto...');
        if (!html5Qrcode) html5Qrcode = new Html5Qrcode("reader");
        try {
            const blob = await (await fetch(b64)).blob();
            const res = await tryBothScanners(await processImage(blob), new Html5Qrcode("barcode-scanner-container"));
            hideLoading();
            if (res) {
                const input = document.getElementById('barcodeAdd');
                if (input) input.value = res;
                lookupProductByBarcode(res);
            } else throw 0;
        } catch(e) { 
            hideLoading();
            Swal.fire('Gagal', 'Barcode tidak terdeteksi.', 'warning'); 
        }
    }

    function openAddOpnameModal() {
        const form = document.getElementById('opnameForm');
        form.action = "{{ route('products.opname.store') }}";
        document.getElementById('opnameMethod').innerHTML = '';
        document.querySelector('#addOpnameModal h3').innerText = 'Input Opname Stok';
        document.getElementById('opnameItemsTable').innerHTML = '';
        
        const storeSelect = document.getElementById('opname_store_id');
        if (storeSelect) {
            storeSelect.value = storeSelect.dataset.userStore || storeSelect.value;
            if (storeSelect.disabled) storeSelect.disabled = false;
        }

        const storeId = storeSelect ? storeSelect.value : null;
        if (storeId) {
            loadProductsByStore(storeId);
        } else {
            addOpnameRow(); 
        }
        openModal('addOpnameModal');
    }

    async function loadProductsByStore(storeId) {
        if (!storeId) {
            document.getElementById('opnameItemsTable').innerHTML = '';
            addOpnameRow();
            return;
        }

        showLoading('Memuat Produk Outlet...');
        try {
            const res = await fetch(`/products/by-store/${storeId}`);
            const products = await res.json();
            hideLoading();

            const tbody = document.getElementById('opnameItemsTable');
            tbody.innerHTML = '';
            
            if (products.length === 0) {
                addOpnameRow();
                return;
            }

            products.forEach((p, i) => {
                const row = document.createElement('tr');
                row.id = `opname_row_${i}`;
                row.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div>
                                <div style="font-weight: 600;">${p.nama_produk}</div>
                                <div style="font-size: 11px; color: #888;">${p.barcode || '-'}</div>
                            </div>
                            <span id="status_icon_${i}"></span>
                        </div>
                        <input type="hidden" name="items[${i}][product_id]" value="${p.uuid}">
                    </td>
                    <td>
                        <input type="number" name="items[${i}][stok_sistem]" id="sistem_${i}" class="form-control" value="${p.current_stok}" readonly style="background: #f8f9fa; text-align: center; padding: 8px 4px;">
                    </td>
                    <td>
                        <input type="number" name="items[${i}][stok_fisik]" class="form-control" placeholder="0" required style="text-align: center; padding: 8px 4px;" oninput="updateOpnameRowStatus(this, ${i})">
                    </td>
                    <td style="text-align: center; font-weight: 700;" id="selisih_cell_${i}">-</td>
                    <td><input type="text" name="items[${i}][alasan_selisih]" class="form-control" placeholder="Keterangan..."></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-filter" style="color: #D9534F;" onclick="this.closest('tr').remove()">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </td>`;
                tbody.appendChild(row);
            });
        } catch (e) {
            hideLoading();
            console.error(e);
            Swal.fire('Error', 'Gagal memuat produk outlet.', 'error');
        }
    }

    async function openEditOpnameModal(uuid) {
        showLoading('Memuat Draft...');
        try {
            const res = await fetch(`/products/opname-detail/${uuid}`);
            const d = await res.json();
            hideLoading();

            const form = document.getElementById('opnameForm');
            form.action = `/products/opname/${uuid}`;
            document.getElementById('opnameMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.querySelector('#addOpnameModal h3').innerText = 'Edit Draft Opname';
            
            const storeSelect = document.getElementById('opname_store_id');
            storeSelect.value = d.store_id;
            if (storeSelect.disabled !== undefined) storeSelect.disabled = true; // Disable store change on edit

            const tbody = document.getElementById('opnameItemsTable');
            tbody.innerHTML = '';
            
            d.details.forEach((it, i) => {
                const row = document.createElement('tr');
                row.id = `opname_row_${i}`;
                
                // Logic: Handle sales since draft saved
                const originalSystemStock = parseFloat(it.stok_sistem) || 0;
                const currentSystemStock = parseFloat(it.current_system_stock) || 0;
                const usedSystemStock = currentSystemStock; // Use latest reality
                
                const isMatch = usedSystemStock == it.stok_fisik;
                const iconHtml = `<iconify-icon icon="${isMatch ? 'solar:check-circle-bold' : 'solar:danger-bold'}" style="font-size: 18px; color: ${isMatch ? '#2E7D32' : '#D9534F'};"></iconify-icon>`;
                const selisihValue = it.stok_fisik !== null ? it.stok_fisik - usedSystemStock : 0;
                
                // Set initial background
                if (it.stok_fisik !== null) {
                    row.style.setProperty('background-color', isMatch ? '#f0fdf4' : '#fff1f2', 'important');
                }

                const stockChanged = originalSystemStock !== currentSystemStock;

                row.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div>
                                <div style="font-weight: 600;">${it.product ? it.product.nama_produk : 'Produk Terhapus'}</div>
                                <div style="font-size: 11px; color: #888; display: flex; align-items: center; gap: 5px;">
                                    ${it.product ? (it.product.barcode || '-') : '-'}
                                    ${stockChanged ? `<span title="Stok sistem berubah sejak disimpan (Tadinya: ${originalSystemStock})" style="background: #FFF3E0; color: #E65100; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; cursor: help;">SYSTEM UPDATED</span>` : ''}
                                </div>
                            </div>
                            <span id="status_icon_${i}">${it.stok_fisik !== null ? iconHtml : ''}</span>
                        </div>
                        <input type="hidden" name="items[${i}][product_id]" value="${it.product_id}">
                    </td>
                    <td>
                        <input type="number" name="items[${i}][stok_sistem]" id="sistem_${i}" class="form-control" value="${usedSystemStock}" readonly style="background: #f8f9fa; text-align: center; padding: 8px 4px;">
                    </td>
                    <td>
                        <input type="number" name="items[${i}][stok_fisik]" class="form-control" value="${it.stok_fisik !== null ? it.stok_fisik : ''}" ${it.stok_fisik === null ? '' : 'required'} style="text-align: center; padding: 8px 4px;" oninput="updateOpnameRowStatus(this, ${i})">
                    </td>
                    <td style="text-align: center; font-weight: 700; color: ${it.stok_fisik !== null ? (selisihValue != 0 ? '#D9534F' : '#2E7D32') : 'inherit'}" id="selisih_cell_${i}">
                        ${it.stok_fisik !== null ? (selisihValue > 0 ? '+' : '') + selisihValue : '-'}
                    </td>
                    <td><input type="text" name="items[${i}][alasan_selisih]" class="form-control" value="${it.keterangan || ''}" placeholder="Keterangan..."></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-filter" style="color: #D9534F;" onclick="this.closest('tr').remove()">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </td>`;
                tbody.appendChild(row);
            });
            
            openModal('addOpnameModal');
        } catch (e) {
            hideLoading();
            Swal.fire('Error', 'Gagal memuat detail draft.', 'error');
        }
    }

    function updateOpnameRowStatus(input, index) {
        input.classList.remove('is-invalid');
        const statusIcon = document.getElementById(`status_icon_${index}`);
        const selisihCell = document.getElementById(`selisih_cell_${index}`);
        const row = document.getElementById(`opname_row_${index}`);
        
        if (input.value === '') {
            statusIcon.innerHTML = '';
            selisihCell.innerText = '-';
            selisihCell.style.color = 'inherit';
            if (row) row.style.setProperty('background-color', 'transparent', 'important');
            return;
        }

        const fisik = parseFloat(input.value) || 0;
        const sistem = parseFloat(document.getElementById(`sistem_${index}`).value) || 0;
        const selisih = fisik - sistem;
        
        selisihCell.innerText = (selisih > 0 ? '+' : '') + selisih;
        
        if (fisik === sistem) {
            statusIcon.innerHTML = '<iconify-icon icon="solar:check-circle-bold" style="font-size: 18px; color: #2E7D32;"></iconify-icon>';
            selisihCell.style.color = '#2E7D32';
            if (row) row.style.setProperty('background-color', '#f0fdf4', 'important'); // Light green
        } else {
            statusIcon.innerHTML = '<iconify-icon icon="solar:danger-bold" style="font-size: 18px; color: #D9534F;"></iconify-icon>';
            selisihCell.style.color = '#D9534F';
            if (row) row.style.setProperty('background-color', '#fff1f2', 'important'); // Light red
        }
    }

    function addOpnameRow() {
        const tbody = document.getElementById('opnameItemsTable');
        const i = tbody.children.length; 
        const row = document.createElement('tr');
        row.id = `opname_row_${i}`;
        
        const list = productsList;
        let opts = '<option value="">-- Pilih Produk --</option>';
        list.forEach(p => opts += `<option value="${p.uuid}" data-stok="${p.current_stok}">${p.nama_produk} (${p.barcode || 'N/A'})</option>`);
        
        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="flex: 1;">
                        <select name="items[${i}][product_id]" class="product-select" required onchange="updateSistemStok(this, ${i})">
                            ${opts}
                        </select>
                    </div>
                    <span id="status_icon_${i}"></span>
                </div>
                <div class="invalid-feedback">Pilih produk yang akan diopname</div>
            </td>
            <td>
                <input type="number" name="items[${i}][stok_sistem]" id="sistem_${i}" class="form-control" placeholder="0" required readonly style="background: #f8f9fa; text-align: center; padding: 8px 4px;">
            </td>
            <td>
                <input type="number" name="items[${i}][stok_fisik]" class="form-control" placeholder="0" required style="text-align: center; padding: 8px 4px;" oninput="updateOpnameRowStatus(this, ${i})">
            </td>
            <td style="text-align: center; font-weight: 700;" id="selisih_cell_${i}">-</td>
            <td><input type="text" name="items[${i}][alasan_selisih]" id="alasan_${i}" class="form-control" placeholder="Keterangan..."></td>
            <td style="text-align: center;">
                <button type="button" class="btn-filter" style="color: #D9534F;" onclick="this.closest('tr').remove()">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            </td>`;
        tbody.appendChild(row);
        initProductSelect(row.querySelector('.product-select'));
    }

    function updateSistemStok(select, idx) {
        const selected = select.options[select.selectedIndex];
        const stok = selected.dataset.stok || 0;
        document.getElementById(`sistem_${idx}`).value = stok;
    }

    function confirmShipRequest(uuid) {
        Swal.fire({
            title: 'Kirim Barang?',
            text: "Barang akan ditandai sebagai sedang dikirim ke cabang.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#1976D2',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}/ship`, 'POST');
            }
        });
    }

    function confirmReceiveRequest(uuid) {
        Swal.fire({
            title: 'Terima Barang?',
            text: "Klik Ya jika barang sudah sampai. Stok cabang akan bertambah otomatis.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#2E7D32',
            confirmButtonText: 'Ya, Terima'
        }).then((result) => {
            if (result.isConfirmed) {
                submitHiddenForm(`/products/request/${uuid}/receive`, 'POST');
            }
        });
    }

    function confirmDeleteOpname(uuid, date) {
        Swal.fire({
            title: 'Hapus Riwayat Opname?',
            text: `Yakin hapus data opname tanggal ${date}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D9534F',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(); // Show loading feedback
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/products/opname/${uuid}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function submitHiddenForm(action, method = 'POST') {
        showLoading(); // Show loading feedback
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            ${method !== 'POST' ? `<input type="hidden" name="_method" value="${method}">` : ''}
        `;
        document.body.appendChild(form);
        form.submit();
    }


    window.onclick = e => { 
        if (e.target.classList.contains('modal-overlay')) {
            console.log('Overlay clicked, closing modal:', e.target.id);
            e.target.style.setProperty('display', 'none', 'important');
        }
        
        // Close dropdowns if clicking outside
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    }

    // RESTOK LOGIC
    function openRestokModal() {
        document.getElementById('restokItemsTable').innerHTML = '';
        addRestokRow();
        
        // Initialize Supplier Select with TomSelect for a better experience
        const supplierSelect = document.getElementById('restok_supplier_id');
        if (supplierSelect) {
            if (!supplierSelect.tomselect) {
                new TomSelect(supplierSelect, {
                    create: false,
                    placeholder: "-- Pilih Supplier --",
                    onDropdownOpen: function() {
                        this.dropdown.style.zIndex = "99999999";
                    }
                });
            } else {
                supplierSelect.tomselect.clear();
            }
        }

        document.getElementById('restokModal').style.display = 'flex';
        calculateRestokTotal();
    }

    const rupiahFormatter = new Intl.NumberFormat('id-ID');

    function openPayDebtModal(transactionId, sisa) {
        document.getElementById('pay_transaction_id').value = transactionId;
        document.getElementById('pay_sisa_label').innerText = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('pay_nominal').value = sisa;
        document.getElementById('pay_nominal').max = sisa;
        openModal('payDebtModal');
    }

    // Update form submission to use AJAX for better experience with beautiful premium validation
    document.addEventListener('DOMContentLoaded', function() {
        const payForm = document.getElementById('payDebtForm');
        if (payForm) {
            const pmSelect = document.getElementById('pay_metode_pembayaran');
            const nominalInput = document.getElementById('pay_nominal');

            function clearError(inputEl, errorEl) {
                inputEl.style.borderColor = '';
                inputEl.style.backgroundColor = '';
                inputEl.style.boxShadow = '';
                errorEl.style.display = 'none';
            }

            function setError(inputEl, errorEl, message) {
                inputEl.style.borderColor = '#dc2626';
                inputEl.style.backgroundColor = '#fef2f2';
                inputEl.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.15)';
                errorEl.innerText = message;
                errorEl.style.display = 'block';
            }

            if (pmSelect) {
                pmSelect.addEventListener('change', () => clearError(pmSelect, document.getElementById('pm_error_msg')));
            }
            if (nominalInput) {
                nominalInput.addEventListener('input', () => clearError(nominalInput, document.getElementById('nominal_error_msg')));
            }

            payForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let hasError = false;

                // Validate Payment Method
                const pmVal = pmSelect ? pmSelect.value : '';
                if (!pmVal) {
                    setError(pmSelect, document.getElementById('pm_error_msg'), 'Pilih Metode Pembayaran Anda');
                    hasError = true;
                } else {
                    clearError(pmSelect, document.getElementById('pm_error_msg'));
                }

                // Validate Nominal
                const nominalVal = nominalInput ? parseFloat(nominalInput.value) : 0;
                const maxVal = nominalInput ? parseFloat(nominalInput.max) : 0;
                if (!nominalInput || nominalInput.value.trim() === '') {
                    setError(nominalInput, document.getElementById('nominal_error_msg'), 'Masukkan Jumlah Bayar Anda');
                    hasError = true;
                } else if (nominalVal <= 0) {
                    setError(nominalInput, document.getElementById('nominal_error_msg'), 'Jumlah Bayar harus lebih besar dari Rp 0');
                    hasError = true;
                } else if (maxVal > 0 && nominalVal > maxVal) {
                    setError(nominalInput, document.getElementById('nominal_error_msg'), `Jumlah Bayar tidak boleh melebihi sisa hutang (Rp ${maxVal.toLocaleString('id-ID')})`);
                    hasError = true;
                } else {
                    clearError(nominalInput, document.getElementById('nominal_error_msg'));
                }

                if (hasError) {
                    return false;
                }

                showLoading('Memproses Pembayaran...');
                
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Gagal memproses pembayaran.', 'error');
                    }
                })
                .catch(err => {
                    hideLoading();
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            });
        }
    });

    function deleteRestok(uuid) {
        Swal.fire({
            title: 'Hapus Restok ini?',
            text: "Stok akan dikurangi dan catatan keuangan akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Menghapus Restok...');
                fetch(`/products/restok/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Gagal menghapus restok.', 'error');
                    }
                })
                .catch(err => {
                    hideLoading();
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            }
        });
    }

    function viewPurchaseDetail(uuid) {
        showLoading('Memuat Detail...');

        fetch(`/products/restok/${uuid}`)
            .then(res => res.json())
            .then(data => {
                hideLoading();
                const content = document.getElementById('purchaseDetailContent');
                
                let itemsHtml = '';
                data.details.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td>${item.product ? item.product.nama_produk : 'Produk Terhapus'}<br><small class="text-muted">${item.product ? (item.product.barcode || '-') : '-'}</small></td>
                            <td>${item.jmlh}</td>
                            <td>Rp ${rupiahFormatter.format(item.harga_modal)}</td>
                            <td>Rp ${rupiahFormatter.format(item.harga_jual)}</td>
                            <td>Rp ${rupiahFormatter.format(item.jmlh * item.harga_modal)}</td>
                        </tr>
                    `;
                });

                const totalVal = parseFloat(data.total) || 0;
                const bayarVal = parseFloat(data.bayar) || 0;
                const sisaVal = Math.max(0, totalVal - bayarVal);
                const percentVal = totalVal > 0 ? Math.round((bayarVal / totalVal) * 100) : 100;
                const isLunasVal = sisaVal <= 0;

                let historyHtml = '';
                const dpAmount = data.debt ? Math.max(0, totalVal - (parseFloat(data.debt.nominal) || 0)) : totalVal;
                const dpMethod = data.payment_method ? data.payment_method.nama_metode : 'Kas';
                const listInstallments = (data.debt ? (data.debt.detail_debts || data.debt.detailDebts) : null) || [];
                const hasInstallments = listInstallments.length > 0;

                if (dpAmount > 0 || hasInstallments) {
                    historyHtml += `
                        <div style="margin-top: 20px;">
                            <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Riwayat Pembayaran Cicilan / Pelunasan:</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                    `;

                    // 1. Render Pembayaran Awal / DP if any
                    if (dpAmount > 0) {
                        const dpDate = data.debt ? (data.debt.updated_at || data.debt.created_at || data.tanggal) : (data.updated_at || data.created_at || data.tanggal);
                        historyHtml += `
                                <div style="background: #f8fafc; border-radius: 8px; padding: 10px 15px; border-left: 3px solid #2e7d32; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">Rp ${rupiahFormatter.format(dpAmount)}</div>
                                        <div style="color: #64748b; font-size: 11px; margin-top: 2px;">Pembayaran Awal / DP (${dpMethod})</div>
                                    </div>
                                    <div style="text-align: right; color: #64748b; font-size: 11px;">
                                        <div>${parseDateTimeString(dpDate)}</div>
                                    </div>
                                </div>
                        `;
                    }

                    // 2. Render subsequent installments
                    if (hasInstallments) {
                        listInstallments.forEach(payment => {
                            const dateFormatted = parseDateTimeString(payment.updated_at || payment.created_at || payment.tanggal);
                            const method = payment.payment_method ? payment.payment_method.nama_metode : 'Kas';
                            historyHtml += `
                                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px 15px; border-left: 3px solid #2e7d32; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b;">Rp ${rupiahFormatter.format(payment.bayar)}</div>
                                            <div style="color: #64748b; font-size: 11px; margin-top: 2px;">Angsuran / Cicilan (${method})</div>
                                        </div>
                                        <div style="text-align: right; color: #64748b; font-size: 11px;">
                                            <div>${dateFormatted}</div>
                                            <div style="font-style: italic; margin-top: 2px; color: #94a3b8;">Sisa: Rp ${rupiahFormatter.format(payment.sisa)}</div>
                                        </div>
                                    </div>
                            `;
                        });
                    }

                    historyHtml += `
                            </div>
                        </div>
                    `;
                }

                const displayTrxDate = data.debt ? (data.debt.updated_at || data.debt.created_at || data.tanggal) : (data.updated_at || data.created_at || data.tanggal);
                const isCashFromStart = !data.debt;

                // Define context-specific labels and styling
                let paymentStatusHtml = '';
                if (isCashFromStart) {
                    paymentStatusHtml = `
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Status Pembayaran</div>
                            <div style="font-size: 18px; font-weight: 700; color: #2E7D32;">Lunas (Cash / Tunai)</div>
                        </div>
                    `;
                } else {
                    paymentStatusHtml = `
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Sisa Tagihan (Hutang)</div>
                            <div style="font-size: 18px; font-weight: 700; color: ${isLunasVal ? '#2E7D32' : '#dc2626'};">${isLunasVal ? 'Rp 0 (Lunas)' : `Rp ${rupiahFormatter.format(sisaVal)}`}</div>
                        </div>
                    `;
                }

                content.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Nama Supplier</div>
                            <div style="font-size: 18px; font-weight: 700; color: #1e293b;">${data.contact ? data.contact.nama : 'Umum'}</div>
                        </div>
                        ${paymentStatusHtml}
                    </div>

                    <div style="background: #f8fafc; border-radius: 12px; padding: 15px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Total Belanja</div>
                            <div style="font-weight: 600; color: #1e293b;">Rp ${rupiahFormatter.format(totalVal)}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Tanggal Transaksi</div>
                            <div style="font-weight: 600; color: #1e293b;">${parseDateTimeString(displayTrxDate)}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Outlet / Toko</div>
                            <div style="font-weight: 600; color: #1e293b;">${data.store ? data.store.nama : '-'}</div>
                        </div>
                    </div>

                    <!-- Restok Progress & Status Section -->
                    <div style="background: #f8fafc; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 11px; font-weight: 700; color: #475569;">Progress Pembayaran</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 11px; font-weight: 700; color: ${isLunasVal ? '#2E7D32' : '#c62828'};">${percentVal}%</span>
                                <span class="status-badge" style="font-size: 10px; padding: 2px 8px; ${isLunasVal ? 'background: #E8F5E9; color: #2E7D32;' : 'background: #FFF3E0; color: #E65100;'}">
                                    ${isCashFromStart ? 'Lunas (Cash)' : (isLunasVal ? 'Lunas' : 'Belum Lunas')}
                                </span>
                            </div>
                        </div>
                        <div style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden; display: flex; width: 100%;">
                            <div style="background: ${isLunasVal ? '#2E7D32' : '#ef4444'}; width: ${percentVal}%; height: 100%; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 10px;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b; margin-top: 8px; font-weight: 500;">
                            <span style="color: #2E7D32;">Terbayar: Rp ${rupiahFormatter.format(bayarVal)}</span>
                            <span style="color: ${isCashFromStart ? '#64748b' : '#c62828'};">Sisa: Rp ${rupiahFormatter.format(sisaVal)}</span>
                        </div>
                    </div>

                    <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Daftar Produk:</div>
                    <div class="table-container" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <table class="fitur-table" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>H. Beli</th>
                                    <th>H. Jual</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>
                    </div>
                    ${historyHtml}
                    ${data.catatan ? `<div style="margin-top: 15px; font-size: 12px; color: #64748b; background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 3px solid var(--primary-blue);"><strong>Catatan:</strong> ${data.catatan}</div>` : ''}
                `;
                
                openModal('purchaseDetailModal');
            })
            .catch(err => {
                hideLoading();
                Swal.fire('Error', 'Gagal memuat detail pembelian.', 'error');
            });
    }

    function updatePaymentLabel() {
        const toggle = document.getElementById('paymentMethodToggle');
        const label = document.getElementById('paymentLabel');
        const hidden = document.getElementById('paymentMethodValue');
        const paymentOptions = document.getElementById('paymentOptions');
        const dpAmountGroup = document.getElementById('dpAmountGroup');
        const pmLabel = document.getElementById('pm_label');

        if (toggle.checked) {
            label.innerText = 'Tunai (Kas)';
            label.style.color = '#2E7D32';
            hidden.value = 'Tunai';
            if (paymentOptions) {
                paymentOptions.style.background = '#f8fafc';
                paymentOptions.style.borderColor = '#e2e8f0';
            }
            if (dpAmountGroup) dpAmountGroup.style.display = 'none';
            if (pmLabel) pmLabel.innerText = 'Metode Pembayaran';
            if (pmLabel) pmLabel.style.color = '#334155';
        } else {
            label.innerText = 'Kredit (Hutang)';
            label.style.color = '#C53030';
            hidden.value = 'Kredit';
            if (paymentOptions) {
                paymentOptions.style.background = '#FFF5F5';
                paymentOptions.style.borderColor = '#FED7D7';
            }
            if (dpAmountGroup) dpAmountGroup.style.display = 'block';
            if (pmLabel) pmLabel.innerText = 'Metode Pembayaran DP';
            if (pmLabel) pmLabel.style.color = '#C53030';
        }
    }

    function addRestokRow() {
        const tbody = document.getElementById('restokItemsTable');
        const i = tbody.children.length;
        const row = document.createElement('tr');
        
        let productOptions = '<option value="">-- Pilih Produk --</option>';
        productsList.forEach(p => {
            productOptions += `<option value="${p.uuid}" data-price="${p.harga_modal}" data-sell="${p.harga_jual}">${p.nama_produk} (${p.barcode || 'N/A'})</option>`;
        });

        row.innerHTML = `
            <td>
                <select name="items[${i}][product_id]" class="product-select" required onchange="handleRestokProductChange(this, ${i})">
                    ${productOptions}
                </select>
                <div class="invalid-feedback">Nama produk wajib diisi</div>
            </td>
            <td>
                <input type="number" name="items[${i}][qty]" class="form-control" placeholder="Qty" min="1" required oninput="calculateRestokTotal()" style="min-width: 70px;">
                <div class="invalid-feedback">Qty wajib diisi</div>
            </td>
            <td>
                <input type="number" name="items[${i}][harga_beli]" class="form-control" placeholder="Harga Beli" required oninput="calculateRestokTotal()" style="min-width: 120px;">
                <div class="invalid-feedback">Harga beli wajib diisi</div>
            </td>
            <td>
                <input type="number" name="items[${i}][harga_jual_baru]" class="form-control" placeholder="Harga Jual" required style="min-width: 120px;">
                <div class="invalid-feedback">Harga jual wajib diisi</div>
            </td>
            <td>
                <input type="date" name="items[${i}][kadaluarsa]" class="form-control" required style="min-width: 120px;">
                <div class="invalid-feedback">Tanggal kadaluarsa wajib diisi</div>
            </td>
            <td>
                <button type="button" class="btn-filter" onclick="removeRestokRow(this)" style="color: #D9534F;">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        initProductSelect(row.querySelector('.product-select'));
    }

    function initProductSelect(select) {
        if (select.tomselect) {
            select.tomselect.destroy();
        }
        new TomSelect(select, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            maxOptions: 100,
            onDropdownOpen: function() {
                this.dropdown.style.zIndex = "99999999";
            },
            onChange: function() {
                // Hapus status tidak valid saat nilai dipilih
                this.wrapper.classList.remove('is-invalid');
                const next = this.wrapper.nextElementSibling;
                if (next && next.classList.contains('invalid-feedback')) {
                    next.style.display = 'none';
                }
            }
        });
    }

    function handleRestokProductChange(select, index) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price') || 0;
        const sell = option.getAttribute('data-sell') || 0;
        
        const row = select.closest('tr');
        row.querySelector(`input[name="items[${index}][harga_beli]"]`).value = price;
        row.querySelector(`input[name="items[${index}][harga_jual_baru]"]`).value = sell;
        calculateRestokTotal();
    }

    function removeRestokRow(btn) {
        btn.closest('tr').remove();
        calculateRestokTotal();
        if (document.getElementById('restokItemsTable').children.length === 0) {
            addRestokRow();
        }
    }

    function calculateRestokTotal() {
        const rows = document.getElementById('restokItemsTable').querySelectorAll('tr');
        let grandTotal = 0;
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name*="[harga_beli]"]').value) || 0;
            grandTotal += (qty * price);
        });
        document.getElementById('restokGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    // Filter functions consolidated above (setTransferStatus, setTransferStore, etc)

    function applyDateFilter(tab = '') {
        const start = document.getElementById('inputStartDate' + tab).value;
        const end = document.getElementById('inputEndDate' + tab).value;
        document.getElementById('hiddenStartDate').value = start;
        document.getElementById('hiddenEndDate').value = end;
        updateTableContent();
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    }

    let currentStoreProducts = [];

    function openTransferModal() {
        const table = document.getElementById('transferItemsTable');
        if (!table) return;
        
        table.innerHTML = '';
        currentStoreProducts = []; 
        
        const sourceSelect = document.getElementById('sourceStoreSelect');
        if (sourceSelect) {
            handleSourceStoreChange(sourceSelect.value);
        }

        openModal('transferModal', 50000);
    }

    async function handleSourceStoreChange(storeId) {
        if (!storeId) {
            currentStoreProducts = [];
            return;
        }

        // Rule 4: Filter destination options to exclude source
        const destSelect = document.querySelector('select[name="tujuan_store_id"]');
        if (destSelect) {
            Array.from(destSelect.options).forEach(opt => {
                if (opt.value === storeId) {
                    opt.disabled = true;
                    opt.style.display = 'none';
                } else {
                    opt.disabled = false;
                    opt.style.display = 'block';
                }
            });
            if (destSelect.value === storeId) destSelect.value = '';
        }

        try {
            const response = await fetch(`/products/by-store/${storeId}`);
            currentStoreProducts = await response.json();
            
            // Refresh existing rows if any
            const table = document.getElementById('transferItemsTable');
            if (table) {
                table.innerHTML = '';
                addTransferRow();
            }
        } catch (error) {
            console.error('Error loading products for store:', error);
            Swal.fire('Error', 'Gagal memuat produk untuk toko ini', 'error');
        }
    }

    function addTransferRow() {
        const table = document.getElementById('transferItemsTable');
        if (!table) return;
        const i = table.rows.length;
        const row = table.insertRow();
        
        let productOptions = '<option value="">-- Pilih Produk --</option>';
        if (currentStoreProducts.length > 0) {
            currentStoreProducts.forEach(p => {
                productOptions += `<option value="${p.uuid}" data-stok="${p.stok}" data-harga="${p.harga_modal || 0}">${p.nama_produk} (${p.barcode || 'N/A'}) (Stok: ${p.stok})</option>`;
            });
        } else {
            productOptions = '<option value="">-- Tidak ada produk tersedia --</option>';
        }

        row.innerHTML = `
            <td>
                <select name="items[${i}][product_id]" class="product-select" required onchange="handleTransferProductChange(this, ${i})">
                    ${productOptions}
                </select>
                <div class="invalid-feedback">Pilih produk yang akan dipindah</div>
            </td>
            <td>
                <input type="number" name="items[${i}][qty]" id="transfer_qty_${i}" class="form-control" placeholder="Qty" min="1" step="0.01" required style="min-width: 80px;" oninput="validateTransferQty(this); calculateTransferTotal()">
                <div class="invalid-feedback">Qty wajib diisi</div>
            </td>
            <td>
                <input type="number" name="items[${i}][harga]" id="transfer_harga_${i}" class="form-control" placeholder="Harga" min="0" step="0.01" required oninput="calculateTransferTotal()">
            </td>
            <td id="transfer_subtotal_${i}" style="font-weight: 600; color: #334155; text-align: right;">
                Rp 0
            </td>
            <td>
                <button type="button" class="btn-filter" onclick="removeTransferRow(this)" style="color: #D9534F;">
                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                </button>
            </td>
        `;
        initProductSelect(row.querySelector('.product-select'));
    }

    function removeTransferRow(btn) {
        const row = btn.closest('tr');
        if (document.getElementById('transferItemsTable').rows.length > 1) {
            row.remove();
            calculateTransferTotal();
        }
    }

    function handleTransferProductChange(select, index) {
        const option = select.options[select.selectedIndex];
        const stok = parseFloat(option.getAttribute('data-stok')) || 0;
        const harga = parseFloat(option.getAttribute('data-harga')) || 0;
        
        const qtyInput = document.getElementById(`transfer_qty_${index}`);
        const hargaInput = document.getElementById(`transfer_harga_${index}`);
        
        if (qtyInput) {
            qtyInput.max = stok;
            if (parseFloat(qtyInput.value) > stok) {
                qtyInput.value = stok;
            }
        }
        
        if (hargaInput && option.value) {
            hargaInput.value = harga;
        }
        
        calculateTransferTotal();
    }

    function validateTransferQty(input) {
        const max = parseFloat(input.max);
        if (!isNaN(max) && parseFloat(input.value) > max) {
            input.value = max;
            
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Jumlah transfer telah disesuaikan ke batas maksimal stok yang tersedia (${max}).`,
                confirmButtonColor: 'var(--primary-blue)',
                confirmButtonText: 'Oke, Mengerti'
            });
        }
    }

    function calculateTransferTotal() {
        const table = document.getElementById('transferItemsTable');
        if (!table) return;
        
        let grandTotal = 0;
        
        for (let i = 0; i < table.rows.length; i++) {
            const row = table.rows[i];
            const qtyInput = row.querySelector('input[name^="items["][name$="][qty]"]');
            const hargaInput = row.querySelector('input[name^="items["][name$="][harga]"]');
            
            if (qtyInput && hargaInput) {
                const match = qtyInput.name.match(/items\[(\d+)\]\[qty\]/);
                if (match) {
                    const index = match[1];
                    const subtotalTd = document.getElementById(`transfer_subtotal_${index}`);
                    
                    const qty = parseFloat(qtyInput.value) || 0;
                    const harga = parseFloat(hargaInput.value) || 0;
                    const subtotal = qty * harga;
                    
                    grandTotal += subtotal;
                    
                    if (subtotalTd) {
                        subtotalTd.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                    }
                }
            }
        }
        
        const grandTotalElement = document.getElementById('transferGrandTotal');
        if (grandTotalElement) {
            grandTotalElement.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
    }

    function updateTransferPaymentLabel() {
        const toggle = document.getElementById('transferPaymentMethodToggle');
        const label = document.getElementById('transferPaymentLabel');
        const hiddenInput = document.getElementById('transferPaymentMethodValue');
        const dpGroup = document.getElementById('transferDpAmountGroup');
        const methodSelect = document.getElementById('transfer_metode_pembayaran_select');

        if (toggle && toggle.checked) {
            if (label) {
                label.textContent = 'Tunai (Kas)';
                label.style.color = '#2E7D32';
            }
            if (hiddenInput) hiddenInput.value = 'Tunai';
            if (dpGroup) dpGroup.style.display = 'none';
            if (methodSelect) methodSelect.setAttribute('required', 'required');
        } else {
            if (label) {
                label.textContent = 'Kredit (Hutang)';
                label.style.color = '#C53030';
            }
            if (hiddenInput) hiddenInput.value = 'Kredit';
            if (dpGroup) dpGroup.style.display = 'block';
            if (methodSelect) methodSelect.removeAttribute('required');
        }
    }

    // AJAX Handler for Transfer Form
    document.addEventListener('DOMContentLoaded', function() {
        const transferForm = document.getElementById('transferForm');
        if (transferForm) {
            transferForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Integrasi validasi ke AJAX
                if (!validateProductForm('transferForm')) return;
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                
                // Show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<iconify-icon icon="line-md:loading-twotone-loop" style="margin-right: 8px;"></iconify-icon> Memproses...';
                
                showLoading('Memproses Transfer...');

                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Transfer stok berhasil dikirim.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeModal('transferModal');
                            location.reload(); // Refresh to update stock data
                        });
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan saat memproses transfer.');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Transfer Error:', error);
                    // Tidak menampilkan popup gagal sesuai permintaan, feedback sudah ada di box merah
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                });
            });
        }
    });

    async function viewTransferDetail(uuid) {
        showLoading('Memuat Detail...');
        try {
            console.log('Fetching detail for transfer:', uuid);
            const response = await fetch(`/products/restok/${uuid}`);
            const data = await response.json();
            hideLoading();
            console.log('Transfer Data Received:', data);
            
            if (!data || !data.details) {
                console.error('Invalid data received from server:', data);
                Swal.fire('Error', 'Data detail tidak ditemukan', 'error');
                return;
            }

            let itemsHtml = '';
            data.details.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td style="padding: 12px;">
                            <div style="font-weight: 600; color: #334155;">${item.product ? item.product.nama_produk : 'Produk Terhapus'}</div>
                            <div style="font-size: 11px; color: #64748b;">${item.product ? (item.product.barcode || '-') : '-'}</div>
                        </td>
                        <td style="padding: 12px; text-align: center; font-weight: 700; color: var(--primary-blue);">${item.jmlh}</td>
                    </tr>
                `;
            });

            const body = document.getElementById('transferDetailBody_v2');
            if (!body) {
                console.error('CRITICAL: transferDetailBody_v2 element NOT FOUND!');
                return;
            }

            body.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <div style="font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Informasi Transfer</div>
                        <div style="font-size: 14px; font-weight: 600; color: #334155;">Tanggal: ${new Date(data.tanggal).toLocaleString('id-ID', {day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</div>
                        <div style="margin-top: 8px;">
                            <span style="font-size: 11px; padding: 4px 12px; border-radius: 20px; font-weight: 700; background: ${data.status == 'Selesai' ? '#E6FFFA' : '#FFF5F5'}; color: ${data.status == 'Selesai' ? '#2F855A' : '#C53030'}; border: 1px solid ${data.status == 'Selesai' ? '#B2F5EA' : '#FEB2B2'};">
                                ${data.status || 'Pending'}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Lokasi & Petugas</div>
                        <div style="font-size: 13px; color: #475569;">
                            <iconify-icon icon="solar:shop-2-bold" style="vertical-align: middle; margin-right: 4px; color: var(--primary-blue);"></iconify-icon>
                            <strong>${data.store ? data.store.nama : '-'}</strong> 
                            <iconify-icon icon="solar:arrow-right-bold" style="margin: 0 4px; vertical-align: middle; color: #94a3b8;"></iconify-icon> 
                            <strong>${(data.tujuanStore || data.tujuan_store) ? (data.tujuanStore || data.tujuan_store).nama : '-'}</strong>
                        </div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 6px;">
                            <iconify-icon icon="solar:user-bold" style="vertical-align: middle; margin-right: 4px;"></iconify-icon>
                            Oleh: ${data.user ? data.user.username : '-'}
                        </div>
                    </div>
                </div>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 12px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="solar:box-minimalistic-bold" style="color: var(--primary-blue);"></iconify-icon>
                        Daftar Produk yang Dipindah
                    </div>
                    <div class="table-container" style="background: white; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
                        <table class="fitur-table" style="margin: 0; font-size: 13px;">
                            <thead>
                                <tr style="background: #f1f5f9;">
                                    <th style="padding: 12px;">Produk</th>
                                    <th style="padding: 12px; text-align: center;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>
                    </div>
                </div>

                ${data.catatan ? `
                <div style="margin-top: 15px; padding: 12px; background: #fffbeb; border-radius: 8px; border: 1px solid #fef3c7; font-size: 13px; color: #92400e;">
                    <iconify-icon icon="solar:info-circle-bold" style="vertical-align: middle; margin-right: 6px;"></iconify-icon>
                    <strong>Catatan:</strong> ${data.catatan}
                </div>
                ` : ''}
            `;
            openModal('transferDetailModal_v2');
        } catch (error) {
            hideLoading();
            console.error('Error fetching transfer detail:', error);
            Swal.fire('Error', 'Gagal memuat detail transfer', 'error');
        }
    }

    function approveTransfer(uuid) {
        Swal.fire({
            title: 'Setujui Transfer?',
            text: "Permintaan transfer stok ini akan disetujui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0081C9',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(); // Show global loading
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/transfer/approve/${uuid}`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function shipTransfer(uuid) {
        Swal.fire({
            title: 'Kirim Barang?',
            text: "Konfirmasi bahwa barang sedang dikirim. Stok outlet asal akan dikurangi.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E65100',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(); // Show global loading
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/transfer/ship/${uuid}`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmReceiveTransfer(uuid) {
        Swal.fire({
            title: 'Konfirmasi Penerimaan',
            text: "Apakah Anda yakin barang sudah diterima dan ingin menambah stok di toko ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2F855A',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Saya Terima!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(); // Show global loading
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/transfer/confirm/${uuid}`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function validateProductForm(formId) {
        const form = document.getElementById(formId);
        const requiredInputs = form.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            let val = input.value;
            let targetEl = input;
            
            // Tangani TomSelect
            if (input.tomselect) {
                targetEl = input.tomselect.wrapper;
            }

            // Opname Logic: Allow null physical stock for drafts
            if (formId === 'opnameForm') {
                const isDraft = form.dataset.submitAction === 'save';
                if (isDraft && input.name.includes('[stok_fisik]')) {
                    // Skip validation for physical stock if saving as draft
                    return;
                }
            }

            // A more robust lookup for invalid-feedback
            let feedback = targetEl.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                const parent = targetEl.closest('.form-group') || targetEl.closest('td');
                if (parent) {
                    feedback = parent.querySelector('.invalid-feedback');
                }
            }

            if (!val || !val.trim()) {
                targetEl.classList.add('is-invalid');
                if (feedback) {
                    feedback.style.display = 'block';
                }
                isValid = false;
            } else {
                targetEl.classList.remove('is-invalid');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            }
        });

        // Validasi Foto (Khusus form produk)
        if (formId === 'addForm' || formId === 'editForm') {
            const isEdit = formId === 'editForm';
            const photoInputId = isEdit ? 'editCroppedImageResult' : 'croppedImageResult';
            const previewId = isEdit ? 'editImagePreviewContainer' : 'imagePreviewContainer';
            const errorId = isEdit ? 'imageErrorEdit' : 'imageErrorAdd';
            
            const photoInput = document.getElementById(photoInputId);
            const photoContainer = document.getElementById(previewId);
            const hasExistingImage = photoContainer ? photoContainer.querySelector('img') : false;
            
            if (photoInput && photoContainer) {
                if (!photoInput.value && !hasExistingImage) {
                    photoContainer.style.borderColor = '#dc3545';
                    const errEl = document.getElementById(errorId);
                    if (errEl) errEl.style.display = 'block';
                    isValid = false;
                } else {
                    photoContainer.style.borderColor = '#cbd5e1';
                    const errEl = document.getElementById(errorId);
                    if (errEl) errEl.style.display = 'none';
                }
            }
        }

        if (isValid) {
            let msg = 'Sedang Memproses Data...';
            if (formId === 'editForm') msg = 'Sedang Memperbarui Produk...';
            if (formId === 'restokForm') msg = 'Sedang Menyimpan Data Restok...';
            if (formId === 'transferForm') msg = 'Sedang Memproses Transfer Stok...';
            if (formId === 'opnameForm') msg = 'Sedang Menyimpan Data Opname...';
            showLoading(msg);
            return true;
        } else {

            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                if (firstInvalid.classList.contains('ts-wrapper')) {
                    // TomSelect focus handle
                } else {
                    firstInvalid.focus();
                }
            }
            return false;
        }
    }

    // --- Global Notifications (SweetAlert2) ---
    document.addEventListener('DOMContentLoaded', function() {
        // Clear error state on input
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
            input.addEventListener('change', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: @json(session('error'))
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        @endif
    });
    // --- Client-side Pagination for All Tabs ---
    let stokState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let restokState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let transferState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let opnameState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let rugiState = { currentPage: 1, rowsPerPage: 10, filtered: [] };
    let produkState = { currentPage: 1, rowsPerPage: 10, filtered: [] };

    function initAllPaginators() {
        if (document.getElementById('produkTable')) {
            produkState.filtered = Array.from(document.querySelectorAll('#produkTable tbody tr.produk-row'));
            produkState.currentPage = 1;
            renderPaginator('produk', produkState, 'produkPagination', 'produkTable', 'produk-row');
        }
        if (document.getElementById('stokTable')) {
            stokState.filtered = Array.from(document.querySelectorAll('#stokTable tbody tr.stok-row'));
            stokState.currentPage = 1;
            renderPaginator('stok', stokState, 'stokPagination', 'stokTable', 'stok-row');
        }
        if (document.getElementById('restokTable')) {
            restokState.filtered = Array.from(document.querySelectorAll('#restokTable tbody tr.restok-row'));
            restokState.currentPage = 1;
            renderPaginator('restok', restokState, 'restokPagination', 'restokTable', 'restok-row');
        }
        if (document.getElementById('transferTable')) {
            transferState.filtered = Array.from(document.querySelectorAll('#transferTable tbody tr.transfer-row'));
            transferState.currentPage = 1;
            renderPaginator('transfer', transferState, 'transferPagination', 'transferTable', 'transfer-row');
        }
        if (document.getElementById('opnameTable')) {
            opnameState.filtered = Array.from(document.querySelectorAll('#opnameTable tbody tr.opname-row'));
            opnameState.currentPage = 1;
            renderPaginator('opname', opnameState, 'opnamePagination', 'opnameTable', 'opname-row');
        }
        if (document.getElementById('rugiTable')) {
            rugiState.filtered = Array.from(document.querySelectorAll('#rugiTable tbody tr.rugi-row'));
            rugiState.currentPage = 1;
            renderPaginator('rugi', rugiState, 'rugiPagination', 'rugiTable', 'rugi-row');
        }
    }

    function renderPaginator(type, state, containerId, tableId, rowClass) {
        const totalRows = state.filtered.length;
        const totalPages = Math.ceil(totalRows / state.rowsPerPage) || 1;
        
        if (state.currentPage > totalPages) state.currentPage = totalPages;
        if (state.currentPage < 1) state.currentPage = 1;

        const startIndex = (state.currentPage - 1) * state.rowsPerPage;
        const endIndex = startIndex + state.rowsPerPage;

        // Hide all rows
        document.querySelectorAll(`#${tableId} tbody tr.${rowClass}`).forEach(row => {
            row.style.display = 'none';
        });

        // Show paginated rows
        state.filtered.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            }
        });

        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '<ul class="twins-pagination">';
        html += `<li class="twins-page-item ${state.currentPage === 1 ? 'disabled' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePaginatorPage('${type}', ${state.currentPage - 1})"><iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon></a></li>`;

        let startPage = Math.max(1, state.currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);
        
        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="twins-page-item ${i === state.currentPage ? 'active' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePaginatorPage('${type}', ${i})">${i}</a></li>`;
        }

        html += `<li class="twins-page-item ${state.currentPage === totalPages ? 'disabled' : ''}"><a href="javascript:void(0)" class="twins-page-link" onclick="changePaginatorPage('${type}', ${state.currentPage + 1})"><iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon></a></li>`;
        html += '</ul>';

        container.innerHTML = html;
        container.style.display = totalPages > 1 ? 'flex' : 'none';
    }

    function changePaginatorPage(type, newPage) {
        if (type === 'produk') { produkState.currentPage = newPage; renderPaginator('produk', produkState, 'produkPagination', 'produkTable', 'produk-row'); }
        if (type === 'stok') { stokState.currentPage = newPage; renderPaginator('stok', stokState, 'stokPagination', 'stokTable', 'stok-row'); }
        if (type === 'restok') { restokState.currentPage = newPage; renderPaginator('restok', restokState, 'restokPagination', 'restokTable', 'restok-row'); }
        if (type === 'transfer') { transferState.currentPage = newPage; renderPaginator('transfer', transferState, 'transferPagination', 'transferTable', 'transfer-row'); }
        if (type === 'opname') { opnameState.currentPage = newPage; renderPaginator('opname', opnameState, 'opnamePagination', 'opnameTable', 'opname-row'); }
        if (type === 'rugi') { rugiState.currentPage = newPage; renderPaginator('rugi', rugiState, 'rugiPagination', 'rugiTable', 'rugi-row'); }
    }

    function realtimeSearch(tab) {
        if (tab === 'produk') {
            const search = document.getElementById('searchInput-produk').value.toLowerCase();
            const urlParams = new URLSearchParams(window.location.search);
            const categoryFilter = urlParams.get('category_id') || '';
            const storeFilter = urlParams.get('store_id') || '';
            
            const rows = Array.from(document.querySelectorAll('#produkTable tbody tr.produk-row'));
            
            let matched = [];
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const barcode = row.getAttribute('data-barcode');
                const category = row.getAttribute('data-category');
                const stores = row.getAttribute('data-store') ? row.getAttribute('data-store').split(',') : [];
                
                const matchSearch = name.includes(search) || barcode.includes(search);
                const matchCategory = categoryFilter === '' || category === categoryFilter;
                const matchStore = storeFilter === '' || storeFilter === 'all' || stores.includes(storeFilter);
                
                if (matchSearch && matchCategory && matchStore) {
                    matched.push(row);
                } else {
                    row.style.display = 'none';
                }
            });
            
            produkState.filtered = matched;
            produkState.currentPage = 1;
            renderPaginator('produk', produkState, 'produkPagination', 'produkTable', 'produk-row');
        } else if (tab === 'restok') {
            const search = document.getElementById('searchInput-restok').value.toLowerCase();
            const urlParams = new URLSearchParams(window.location.search);
            const supplierFilter = urlParams.get('supplier_id') || '';
            
            const rows = Array.from(document.querySelectorAll('#restokTable tbody tr.restok-row'));
            let matched = [];
            
            rows.forEach(row => {
                const supplier = row.getAttribute('data-supplier') || '';
                const tanggal = row.getAttribute('data-tanggal') || '';
                const user = row.getAttribute('data-user') || '';
                
                const matchSearch = supplier.includes(search) || tanggal.includes(search) || user.includes(search);
                const matchSupplier = supplierFilter === '' || row.getAttribute('data-supplier-id') === supplierFilter;
                
                if (matchSearch && matchSupplier) {
                    matched.push(row);
                } else {
                    row.style.display = 'none';
                }
            });
            
            restokState.filtered = matched;
            restokState.currentPage = 1;
            renderPaginator('restok', restokState, 'restokPagination', 'restokTable', 'restok-row');
        } else if (tab === 'stok') {
            const search = document.getElementById('searchInput-stok').value.toLowerCase();
            
            const rows = Array.from(document.querySelectorAll('#stokTable tbody tr.stok-row'));
            let matched = [];
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const store = row.getAttribute('data-store') || '';
                const tanggal = row.getAttribute('data-tanggal') || '';
                
                const matchSearch = name.includes(search) || store.includes(search) || tanggal.includes(search);
                
                if (matchSearch) {
                    matched.push(row);
                } else {
                    row.style.display = 'none';
                }
            });
            
            stokState.filtered = matched;
            stokState.currentPage = 1;
            renderPaginator('stok', stokState, 'stokPagination', 'stokTable', 'stok-row');
        } else if (tab === 'transfer') {
            const search = document.getElementById('searchInput-transfer').value.toLowerCase();
            
            const rows = Array.from(document.querySelectorAll('#transferTable tbody tr.transfer-row'));
            let matched = [];
            
            rows.forEach(row => {
                const dari = row.getAttribute('data-dari') || '';
                const tujuan = row.getAttribute('data-tujuan') || '';
                const tanggal = row.getAttribute('data-tanggal') || '';
                const user = row.getAttribute('data-user') || '';
                const status = row.getAttribute('data-status') || '';
                
                const matchSearch = dari.includes(search) || tujuan.includes(search) || tanggal.includes(search) || user.includes(search) || status.includes(search);
                
                if (matchSearch) {
                    matched.push(row);
                } else {
                    row.style.display = 'none';
                }
            });
            
            transferState.filtered = matched;
            transferState.currentPage = 1;
            renderPaginator('transfer', transferState, 'transferPagination', 'transferTable', 'transfer-row');
        } else if (tab === 'opname') {
            const search = document.getElementById('searchInput-opname').value.toLowerCase();
            
            // Opname
            const opnameRows = Array.from(document.querySelectorAll('#opnameTable tbody tr.opname-row'));
            if(opnameRows.length > 0) {
                let matchedOpname = [];
                opnameRows.forEach(row => {
                    const tanggal = row.getAttribute('data-tanggal') || '';
                    const store = row.getAttribute('data-store') || '';
                    const user = row.getAttribute('data-user') || '';
                    
                    const matchSearch = tanggal.includes(search) || store.includes(search) || user.includes(search);
                    if (matchSearch) {
                        matchedOpname.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });
                opnameState.filtered = matchedOpname;
                opnameState.currentPage = 1;
                renderPaginator('opname', opnameState, 'opnamePagination', 'opnameTable', 'opname-row');
            }
            
            // Rugi
            const rugiRows = Array.from(document.querySelectorAll('#rugiTable tbody tr.rugi-row'));
            if(rugiRows.length > 0) {
                let matchedRugi = [];
                rugiRows.forEach(row => {
                    const produk = row.getAttribute('data-produk') || '';
                    const store = row.getAttribute('data-store') || '';
                    const tanggal = row.getAttribute('data-tanggal') || '';
                    
                    const matchSearch = produk.includes(search) || store.includes(search) || tanggal.includes(search);
                    if (matchSearch) {
                        matchedRugi.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });
                rugiState.filtered = matchedRugi;
                rugiState.currentPage = 1;
                renderPaginator('rugi', rugiState, 'rugiPagination', 'rugiTable', 'rugi-row');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initAllPaginators();
    });

</script>
{{-- Duplicate modal removed --}}

<!-- Modal Cropper -->
<div id="cropperModal" class="modal-overlay" style="display: none; justify-content: center; align-items: center; background: rgba(0,0,0,0.85); z-index: 999999 !important; backdrop-filter: blur(8px);">
    <div class="modal-content" style="max-width: 600px; width: 95%; background: white; border-radius: 28px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div class="modal-header" style="background: #ffffff; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: var(--light-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-blue);">
                    <iconify-icon icon="solar:crop-minimalistic-bold-duotone" style="font-size: 24px;"></iconify-icon>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Sesuaikan Foto</h3>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">Geser dan atur posisi foto produk (1:1)</p>
                </div>
            </div>
            <button type="button" onclick="closeCropperModal()" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer; transition: all 0.2s;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; background: #0f172a; display: flex; justify-content: center; align-items: center; height: 400px; position: relative;">
            <img id="cropperImage" src="" style="max-width: 100%; display: block;">
            
            <!-- Floating Controls -->
            <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 50px; backdrop-filter: blur(4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); z-index: 10;">
                <button type="button" class="btn-cropper-tool" onclick="cropper.zoom(0.1)" title="Zoom In" style="width: 36px; height: 36px; border-radius: 50%; border: none; background: white; color: #334155; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="solar:magnifer-zoom-in-bold" style="font-size: 20px;"></iconify-icon>
                </button>
                <button type="button" class="btn-cropper-tool" onclick="cropper.zoom(-0.1)" title="Zoom Out" style="width: 36px; height: 36px; border-radius: 50%; border: none; background: white; color: #334155; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="solar:magnifer-zoom-out-bold" style="font-size: 20px;"></iconify-icon>
                </button>
                <div style="width: 1px; background: #e2e8f0; height: 24px; margin: 6px 4px;"></div>
                <button type="button" class="btn-cropper-tool" onclick="cropper.rotate(-90)" title="Rotate Left" style="width: 36px; height: 36px; border-radius: 50%; border: none; background: white; color: #334155; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="solar:restart-bold" style="font-size: 20px; transform: scaleX(-1);"></iconify-icon>
                </button>
                <button type="button" class="btn-cropper-tool" onclick="cropper.rotate(90)" title="Rotate Right" style="width: 36px; height: 36px; border-radius: 50%; border: none; background: white; color: #334155; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="solar:restart-bold" style="font-size: 20px;"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="modal-footer" style="padding: 24px; border-top: 1px solid #f1f5f9; display: flex; gap: 16px; background: #ffffff;">
            <button type="button" class="btn-action" style="flex: 1; background: #f1f5f9; color: #64748b; justify-content: center; height: 48px; border-radius: 12px; font-weight: 600;" onclick="closeCropperModal()">Batal</button>
            <button type="button" class="btn-action" style="flex: 2; background: var(--primary-blue); color: white; justify-content: center; height: 48px; border-radius: 12px; font-weight: 700; box-shadow: 0 10px 15px -3px rgba(0, 129, 201, 0.3);" onclick="applyCrop()">
                <iconify-icon icon="solar:check-circle-bold" style="margin-right: 8px; font-size: 20px;"></iconify-icon> Gunakan Foto Ini
            </button>
        </div>
    </div>
</div>



{{-- GLOBAL LOADING REMOVED PER USER REQUEST --}}

@endsection
