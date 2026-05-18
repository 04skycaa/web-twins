<div x-show="tab === 'bulanan'" class="space-y-8 mb-8">
    {{-- ─── Hero Card: Ringkasan Keuangan Bulanan ─────────────────────────── --}}
    <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="p-6 md:p-8">
            {{-- Header: Label + Laba Bersih + Badge --}}
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-50 p-2 md:p-3 text-blue-600">
                        <iconify-icon icon="solar:calendar-mark-bold" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-lg md:text-xl">Laporan Bulanan</p>
                        <h2 class="mt-1 text-xs md:text-sm text-gray-500">Ringkasan Keuangan Bulanan</h2>
                    </div>
                </div>
                <div class="text-right flex flex-col items-end gap-2">
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-bold">Laba Bersih</div>
                    <div id="monthly-laba-bersih-badge"
                        class="mt-1 text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900">Rp 0</div>
                    <span id="monthly-surplus-badge"
                        class="inline-block rounded-full px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 hidden">
                        ✅ Surplus
                    </span>
                    <span id="monthly-defisit-badge"
                        class="inline-block rounded-full px-3 py-1 text-xs font-bold bg-rose-100 text-rose-700 hidden">
                        ⚠️ Defisit
                    </span>
                </div>
            </div>

            {{-- Mini-Cards Grid: 6 metric --}}
            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="p-4 md:p-5 rounded-xl bg-blue-50 border border-blue-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-blue-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <iconify-icon icon="ic:sharp-store" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">Penjualan Toko</div>
                        <div id="monthly-offline-omset-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>

                <div class="p-4 md:p-5 rounded-xl bg-sky-50 border border-sky-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-sky-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-sky-100 text-sky-600">
                        <iconify-icon icon="mdi:cloud-check" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">Penjualan Online</div>
                        <div id="monthly-online-omset-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>

                <div class="p-4 md:p-5 rounded-xl bg-indigo-50 border border-indigo-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-indigo-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                        <iconify-icon icon="mdi:cash" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">Total Omset</div>
                        <div id="monthly-total-omset-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>

                <div class="p-4 md:p-5 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-amber-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <iconify-icon icon="mdi:warehouse" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">HPP</div>
                        <div id="monthly-hpp-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>

                <div class="p-4 md:p-5 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-emerald-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <iconify-icon icon="mdi:cash-plus" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">Pemasukan</div>
                        <div id="monthly-pemasukan-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>

                <div class="p-4 md:p-5 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-4 shadow-sm hover:shadow-lg transition-shadow text-rose-600">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-rose-100 text-rose-600">
                        <iconify-icon icon="mdi:cash-minus" class="text-lg md:text-xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm md:text-base font-bold">Pengeluaran</div>
                        <div id="monthly-pengeluaran-value" class="mt-1 text-base md:text-lg font-semibold text-current">Rp 0</div>
                    </div>
                </div>
            </div>

            {{-- Laba Kotor + Laba Bersih + Rugi pills --}}
            <div class="mt-6 flex flex-wrap items-center gap-3 text-sm md:text-base">
                <span class="rounded-full bg-gray-50 text-gray-800 px-3 md:px-4 py-1 md:py-1.5">Laba Kotor: <strong id="monthly-laba-kotor-value" class="text-current">Rp 0</strong></span>
                <span class="rounded-full bg-gray-50 text-gray-800 px-3 md:px-4 py-1 md:py-1.5">Laba Bersih: <strong id="monthly-laba-bersih-value" class="text-current">Rp 0</strong></span>
                <span class="rounded-full bg-gray-50 text-gray-800 px-3 md:px-4 py-1 md:py-1.5">Rugi: <strong id="monthly-rugi-value" class="text-rose-600">Rp 0</strong></span>
            </div>
        </div>
    </article>

    {{-- ─── Operator + Hutang/Piutang Row ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
                <div>
                    <h3 class="font-bold text-gray-800">Data Operator</h3>
                    <p class="mt-1 text-xs text-gray-500">Total uang masuk dan keluar per operator</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">Data Function Supabase</span>
            </div>
            <div class="p-6" id="monthly-operator-list">
                <div class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm animate-pulse">
                    <div class="border-b border-gray-50 px-5 py-4">
                        <div class="h-4 w-40 rounded bg-gray-200"></div>
                        <div class="mt-2 h-3 w-64 rounded bg-gray-100"></div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="h-16 rounded-2xl bg-gray-100"></div>
                        <div class="h-16 rounded-2xl bg-gray-100"></div>
                        <div class="h-16 rounded-2xl bg-gray-100"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
                <div>
                    <h3 class="font-bold text-gray-800">Data Hutang &amp; Piutang</h3>
                    <p class="mt-1 text-xs text-gray-500">Total yang belum lunas secara keseluruhan</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">Debt Summary</span>
            </div>
            <div class="p-6">
                <div id="monthly-debt-list" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse">
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                        <div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div>
                    </div>
                    <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse">
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                        <div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ─── Data Transaksi Harian ──────────────────────────────────────────── --}}
    <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
            <div>
                <h3 class="font-bold text-gray-800">Data Transaksi</h3>
                <p class="mt-1 text-xs text-gray-500">Rincian transaksi bulanan per jenis — klik untuk lihat grafik</p>
            </div>
            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-600">Breakdown Bulanan</span>
        </div>
        <div class="p-6" id="monthly-transaction-list">
            <details class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm animate-pulse" open>
                <summary class="flex list-none items-center justify-between gap-4 px-5 py-5">
                    <div class="min-w-0">
                        <div class="h-4 w-36 rounded bg-gray-200"></div>
                        <div class="mt-2 h-3 w-24 rounded bg-gray-100"></div>
                    </div>
                    <div class="text-right">
                        <div class="h-4 w-28 rounded bg-gray-200"></div>
                        <div class="mt-2 h-3 w-16 rounded bg-gray-100 ml-auto"></div>
                    </div>
                </summary>
                <div class="border-t border-gray-100 px-5 py-5 space-y-4">
                    <div class="h-14 rounded-2xl bg-gray-100"></div>
                    <div class="h-14 rounded-2xl bg-gray-100"></div>
                    <div class="h-14 rounded-2xl bg-gray-100"></div>
                </div>
            </details>
        </div>
    </section>
</div>
