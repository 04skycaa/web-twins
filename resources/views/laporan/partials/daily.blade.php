<section x-show="tab === 'harian'" x-transition.opacity aria-live="polite" class="space-y-8 relative">
    <!-- overlay kept for compatibility but progressive loading uses inline placeholders -->
    <div id="daily-loading-overlay"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-50/75 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
        <div class="flex items-center gap-3 rounded-3xl border border-gray-200 bg-white px-5 py-4 shadow-lg">
            <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <div>
                <div class="text-sm font-semibold text-gray-800">Memuat laporan harian</div>
                <div class="text-xs text-gray-500">Menyiapkan ringkasan, cashbox, dan operator...</div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
        <!-- Card Total Omset -->
        <div class="stat-card col-span-2 md:col-span-1" style="background: #f0f7ff;">
            <div class="stat-header">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <iconify-icon icon="solar:money-bag-bold"></iconify-icon>
                </div>
                <div class="stat-label">Total Omset</div>
            </div>
            <div class="stat-value" id="omset-value" style="color: #0f172a;">Rp 0</div>
            <div style="margin-top: 15px; border-top: 1px dashed rgba(59, 130, 246, 0.2); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span style="color: #64748b; font-size: 0.75rem; font-weight: 600;">Laba Kotor</span>
                <span id="laba-kotor-value" style="font-weight: 800; color: #10b981; font-size: 0.85rem;">Rp 0</span>
            </div>
        </div>

        <!-- Card Pemasukan -->
        <div class="stat-card col-span-1" style="background: #f0fdf4;">
            <div class="stat-header">
                <div class="icon-box" style="background: #dcfce7; color: #10b981;">
                    <iconify-icon icon="solar:wad-of-money-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pemasukan</div>
            </div>
            <div class="stat-value" id="pemasukan-value" style="color: #0f172a;">Rp 0</div>
            <div style="margin-top: 15px; border-top: 1px dashed rgba(16, 185, 129, 0.2); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span style="color: #64748b; font-size: 0.75rem; font-weight: 600;">Status</span>
                <span style="font-weight: 800; color: #10b981; font-size: 0.85rem;">Masuk</span>
            </div>
        </div>

        <!-- Card Pengeluaran -->
        <div class="stat-card col-span-1" style="background: #fffaf0;">
            <div class="stat-header">
                <div class="icon-box" style="background: #ffedd5; color: #f97316;">
                    <iconify-icon icon="solar:card-transfer-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pengeluaran</div>
            </div>
            <div class="stat-value" id="pengeluaran-value" style="color: #0f172a;">Rp 0</div>
            <div style="margin-top: 15px; border-top: 1px dashed rgba(249, 115, 22, 0.2); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span style="color: #64748b; font-size: 0.75rem; font-weight: 600;">Status</span>
                <span style="font-weight: 800; color: #f97316; font-size: 0.85rem;">Keluar</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Distribusi Cashbox</h3>
                <p class="text-xs text-gray-500 mt-1">Aliran uang riil per metode pembayaran</p>
            </div>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Cashbox</span>
        </div>
        <div class="p-6">
            <div id="daily-cashbox-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 animate-pulse">
                    <div class="h-4 w-40 rounded bg-gray-200"></div>
                    <div class="mt-3 h-20 rounded-2xl bg-gray-100"></div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 animate-pulse">
                    <div class="h-4 w-40 rounded bg-gray-200"></div>
                    <div class="mt-3 h-20 rounded-2xl bg-gray-100"></div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 animate-pulse">
                    <div class="h-4 w-40 rounded bg-gray-200"></div>
                    <div class="mt-3 h-20 rounded-2xl bg-gray-100"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Aktivitas per Operator</h3>
                <p class="text-xs text-gray-500 mt-1">Uang di laci dan info stok per operator</p>
            </div>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Laci & Stok</span>
        </div>
        <div class="p-6">
            <div id="operator-list" class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 animate-pulse">
                    <div class="h-4 w-48 rounded bg-gray-200"></div>
                    <div class="mt-3 h-14 rounded-2xl bg-gray-100"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Transaksi Online</h3>
                <p class="text-xs text-gray-500 mt-1">Pesanan yang masuk dari kanal online</p>
            </div>
            <span class="px-3 py-1 bg-sky-50 text-sky-600 text-xs font-bold rounded-full">Online</span>
        </div>
        <div class="overflow-hidden">
            <div id="daily-online-list" class="divide-y divide-gray-100">
                <div class="px-6 py-5">
                    <div class="h-4 w-48 rounded bg-gray-200"></div>
                    <div class="mt-3 space-y-2">
                        <div class="h-12 rounded-2xl bg-gray-100"></div>
                        <div class="h-12 rounded-2xl bg-gray-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
