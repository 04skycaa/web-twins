{{-- Tab: Perilaku Produk --}}
<div id="view-produk" style="display: none;">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 mb-6">
        <div class="stat-card col-span-2 md:col-span-1" style="background: #f0f7ff;">
            <div class="stat-header">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <iconify-icon icon="solar:money-bag-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Omset</div>
            </div>
            <div class="stat-value" id="prod-total-omset">Rp 0</div>
        </div>
        <div class="stat-card" style="background: #f0fdf4;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Laba</div>
            </div>
            <div class="stat-value" id="prod-total-laba">Rp 0</div>
        </div>
        <div class="stat-card" style="background: #fffaf0;">
            <div class="stat-header">
                <div class="icon-box" style="background: #fff7ed; color: #f97316;">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Frekuensi</div>
            </div>
            <div class="stat-value" id="prod-total-freq">0 item</div>
        </div>
    </div>

    {{-- Product List --}}
    <div id="product-list" class="perilaku-list">
        <div class="perilaku-empty">
            <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
            <p>Pilih toko dan tahun untuk memuat data produk</p>
        </div>
    </div>
</div>
