{{-- Tab: Perilaku Customer --}}
<div id="view-customer" style="display: none;">
    {{-- Filter Kanal --}}
    <div class="sub-tab-navigation" style="margin-bottom: 20px;">
        <button class="sub-tab-pill active" id="kanal-semua" onclick="switchKanal('semua')">Semua</button>
        <button class="sub-tab-pill" id="kanal-offline" onclick="switchKanal('offline')">Offline</button>
        <button class="sub-tab-pill" id="kanal-online" onclick="switchKanal('online')">Online</button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-2 gap-3 md:gap-4 mb-6">
        <div class="stat-card" style="background: #f0f7ff;">
            <div class="stat-header">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <iconify-icon icon="solar:money-bag-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Omset Tahunan</div>
            </div>
            <div class="stat-value" id="cust-total-omset">Rp 0</div>
        </div>
        <div class="stat-card" style="background: #f0fdf4;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Jumlah Customer</div>
            </div>
            <div class="stat-value" id="cust-total-count">0 Customer</div>
        </div>
    </div>

    {{-- Customer List --}}
    <div id="customer-list" class="perilaku-list">
        <div class="perilaku-empty">
            <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
            <p>Pilih toko dan tahun untuk memuat data customer</p>
        </div>
    </div>
</div>
