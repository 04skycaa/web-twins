<section x-show="tab === 'performa'" x-transition.opacity class="space-y-8 mb-8" style="display: none;">

    {{-- ─── Hero: Grafik Doughnut + Metric Selector ──────────────────────── --}}
    <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <iconify-icon icon="solar:chart-2-bold-duotone" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Grafik Performa Toko</h3>
                    <p class="mt-1 text-xs text-gray-500">Termasuk Penjualan Online — Tahun ini</p>
                </div>
            </div>
            {{-- Metric Selector --}}
            <select id="performa-metric-selector"
                class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                onchange="updatePerformaChart(this.value)">
                <option value="laba_bersih">Laba Bersih</option>
                <option value="laba_kotor">Laba Kotor</option>
                <option value="omset">Total Omset</option>
            </select>
        </div>

        <div class="p-6" id="performa-chart-container">
            {{-- Loading state --}}
            <div id="performa-chart-loading" class="flex flex-col items-center justify-center py-16 gap-4">
                <div class="h-48 w-48 rounded-full bg-gray-100 animate-pulse"></div>
                <div class="h-4 w-32 rounded bg-gray-200 animate-pulse"></div>
            </div>

            {{-- Chart layout: doughnut + legend --}}
            <div id="performa-chart-content" class="hidden">
                <div class="flex flex-col items-center gap-6 md:flex-row md:items-start">
                    {{-- Doughnut Canvas --}}
                    <div class="relative w-64 h-64 flex-shrink-0 mx-auto">
                        <canvas id="performa-doughnut-chart"></canvas>
                        {{-- Center label --}}
                        <div id="performa-chart-center" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total</div>
                            <div id="performa-chart-total" class="text-sm font-bold text-gray-900 text-center px-2">Rp 0</div>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div id="performa-chart-legend" class="flex-1 space-y-2 min-w-0"></div>
                </div>

                {{-- Info box when segment clicked --}}
                <div id="performa-info-box"
                    class="hidden mt-4 flex items-center justify-between rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-3">
                    <span id="performa-info-name" class="font-bold text-indigo-800 text-sm"></span>
                    <span id="performa-info-val" class="font-bold text-indigo-600 text-sm"></span>
                </div>
            </div>

            {{-- Empty state --}}
            <div id="performa-chart-empty" class="hidden py-12 text-center text-gray-400">
                <iconify-icon icon="solar:chart-square-bold-duotone" class="text-5xl mb-3 opacity-40"></iconify-icon>
                <p class="text-sm">Tidak ada data positif untuk metrik ini</p>
            </div>
        </div>
    </article>

    {{-- ─── Rincian Per Toko (Accordion Cards) ────────────────────────────── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Rincian Per Toko</h2>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600" id="performa-store-count"></span>
        </div>
        <div id="performa-store-list" class="space-y-4">
            {{-- Loading skeleton --}}
            @for($i = 0; $i < 3; $i++)
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-gray-200 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="h-4 w-32 rounded bg-gray-200"></div>
                        <div class="mt-2 h-3 w-48 rounded bg-gray-100"></div>
                    </div>
                    <div class="h-4 w-24 rounded bg-gray-200"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</section>
