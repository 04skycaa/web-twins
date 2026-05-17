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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <article
            class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-white/20 rounded-2xl">
                    <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                </div>
                <h2 class="text-blue-100 font-medium">Total Omset Hari Ini</h2>
            </div>
            <p class="text-4xl font-bold tracking-tight" id="omset-value" aria-label="Nominal Omset">
                Rp
                0</p>
            <div class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center text-sm text-blue-100">
                <span>Laba Kotor</span>
                <span class="font-bold" id="laba-kotor-value">Rp 0</span>
            </div>
        </article>

        <div class="grid grid-cols-2 gap-4">
            <article
                class="bg-emerald-500 rounded-[1.75rem] p-6 text-white shadow-lg shadow-emerald-100/60 border border-white/10">
                <p class="text-xs text-emerald-100/90 font-medium mb-1 uppercase tracking-[0.18em]">Pemasukan</p>
                <p class="text-2xl font-bold" id="pemasukan-value">Rp 0</p>
                <div class="mt-4 flex justify-end">
                    <iconify-icon icon="solar:wad-of-money-bold" class="text-4xl opacity-25"></iconify-icon>
                </div>
            </article>
            <article
                class="bg-amber-500 rounded-[1.75rem] p-6 text-white shadow-lg shadow-amber-100/60 border border-white/10">
                <p class="text-xs text-amber-100/90 font-medium mb-1 uppercase tracking-[0.18em]">Pengeluaran</p>
                <p class="text-2xl font-bold" id="pengeluaran-value">Rp 0</p>
                <div class="mt-4 flex justify-end">
                    <iconify-icon icon="solar:card-transfer-bold" class="text-4xl opacity-25"></iconify-icon>
                </div>
            </article>
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
