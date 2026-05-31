@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perilaku.css') }}">
@endpush

@section('content')
    <div class="fitur-container" id="perilaku-app">
        {{-- PILL TABS --}}
        <div class="tab-navigation overflow-x-auto whitespace-nowrap justify-center pb-2">
            <a href="#" class="tab-pill {{ ($active_tab ?? 'customer') === 'customer' ? 'active' : '' }}" onclick="switchTab('customer')" id="pill-customer">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                <span>Perilaku Customer</span>
            </a>
            <a href="#" class="tab-pill {{ ($active_tab ?? 'customer') === 'produk' ? 'active' : '' }}" onclick="switchTab('produk')" id="pill-produk">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                <span>Perilaku Produk</span>
            </a>
        </div>

        {{-- ACTION BAR --}}
        <header class="action-bar mb-4 bg-transparent p-0" style="justify-content: space-between; border: none; box-shadow: none; flex-wrap: wrap; gap: 15px;">
            <div id="headerLeftActions" style="display: flex; gap: 12px; align-items: center; flex: 1; min-width: 280px; flex-wrap: wrap;">
                
                {{-- Search Bar --}}
                <div class="search-wrapper" style="min-width: 250px;">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari..." onkeyup="handleSearch()" style="width: 100%;">
                </div>

                <form id="globalFilterForm" method="GET" action="{{ route('perilaku.index') }}" style="display:none;">
                    <input type="hidden" name="active_tab" id="filterActiveTab" value="{{ $active_tab ?? 'customer' }}">
                    <input type="hidden" name="store_id" id="filterStoreId" value="{{ $store_id }}">
                    <input type="hidden" name="year" id="filterYear" value="{{ $year ?? date('Y') }}">
                    <input type="hidden" name="month" id="filterMonth" value="{{ $month ?? '' }}">
                    <input type="hidden" name="sort" id="filterSort" value="{{ $sort ?? 'omset' }}">
                </form>

                <div id="perilakuFilters" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    {{-- Dropdown Toko --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                                <a href="javascript:void(0)" onclick="applyGlobalFilter('store', 'all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                            @endif
                            @foreach ($outlets as $o)
                                <a href="javascript:void(0)" onclick="applyGlobalFilter('store', '{{ $o->uuid }}')" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dropdown Year & Month --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Waktu" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" class="{{ $year || !empty($month) ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content" style="padding: 15px; width: 220px;">
                            <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tahun</label>
                            <input id="year-selector" type="number" class="form-control" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 13px; outline: none;" min="2020" max="2100" value="{{ $year ?? date('Y') }}">
                            
                            <div id="month-filter-group" style="display: {{ ($active_tab ?? 'customer') === 'produk' ? 'block' : 'none' }};">
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Bulan (Opsional)</label>
                                <select id="month-selector" class="form-control" style="width: 100%; padding: 8px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 13px; outline: none;">
                                    <option value="">-- Semua Bulan --</option>
                                    @for($m=1; $m<=12; $m++)
                                        <option value="{{ $m }}" {{ (isset($month) && $month == $m) ? 'selected' : '' }}>
                                            {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m-1] }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            
                            <button type="button" class="btn-action w-full" style="justify-content: center; margin-top: 12px; padding: 8px; border-radius: 8px; background: var(--primary-blue); color: white; border: none; cursor: pointer;" onclick="applyDateFilter()">Terapkan</button>
                        </div>
                    </div>

                    {{-- Dropdown Sort (Only for Produk) --}}
                    <div class="dropdown" id="sortDropdown" style="display: {{ ($active_tab ?? 'customer') === 'produk' ? 'inline-block' : 'none' }};">
                        <button type="button" class="btn-filter" title="Urutkan" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:sort-from-top-to-bottom-bold-duotone" style="font-size: 24px;" class="{{ isset($sort) && $sort ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="javascript:void(0)" onclick="applyGlobalFilter('sort', 'omset')" class="{{ (isset($sort) ? $sort : 'omset') == 'omset' ? 'active-dropdown-item' : '' }}">Omset Tertinggi</a>
                            <a href="javascript:void(0)" onclick="applyGlobalFilter('sort', 'frekuensi')" class="{{ (isset($sort) ? $sort : 'omset') == 'frekuensi' ? 'active-dropdown-item' : '' }}">Frekuensi Tertinggi</a>
                            <a href="javascript:void(0)" onclick="applyGlobalFilter('sort', 'laba')" class="{{ (isset($sort) ? $sort : 'omset') == 'laba' ? 'active-dropdown-item' : '' }}">Laba Tertinggi</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN BOX --}}
        <div class="main-content-box">
            @include('perilaku._tab_customer')
            @include('perilaku._tab_produk')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // ═══════════════════════════════════════
        //  State Variables
        // ═══════════════════════════════════════
        let currentTab = '{{ $active_tab ?? 'customer' }}';
        let currentStoreId = '{{ $store_id ?? '' }}';
        let currentYear = {{ $year ?? date('Y') }};
        let currentMonth = '{{ $month ?? '' }}';
        let currentSort = '{{ $sort ?? 'omset' }}';
        let currentKanal = 'semua';
        let searchTimeout = null;

        let perilakuState = {
            customer: { allItems: [], filtered: [], page: 1, perPage: 10 },
            produk: { allItems: [], filtered: [], page: 1, perPage: 10 }
        };

        function renderPaginationUI(tab, totalPages) {
            const container = document.getElementById(tab + '-pagination');
            const wrapper = document.getElementById(tab + '-pagination-container');
            if (!container || !wrapper) return;
            
            if (totalPages <= 1) {
                wrapper.style.display = 'none';
                return;
            }
            wrapper.style.display = 'block';
            
            const state = perilakuState[tab];
            let html = '';
            
            html += `<button type="button" class="k-page-btn" ${state.page === 1 ? 'disabled' : ''} onclick="changePerilakuPage('${tab}', ${state.page - 1})"><iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon></button>`;
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= state.page - 1 && i <= state.page + 1)) {
                    html += `<button type="button" class="k-page-btn ${i === state.page ? 'active' : ''}" onclick="changePerilakuPage('${tab}', ${i})">${i}</button>`;
                } else if (i === state.page - 2 || i === state.page + 2) {
                    html += `<span class="k-page-dots">...</span>`;
                }
            }
            html += `<button type="button" class="k-page-btn" ${state.page === totalPages ? 'disabled' : ''} onclick="changePerilakuPage('${tab}', ${state.page + 1})"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>`;
            
            container.innerHTML = html;
        }

        function changePerilakuPage(tab, page) {
            perilakuState[tab].page = page;
            if (tab === 'customer') renderCustomerList();
            else renderProductList();
        }
        
        function applyClientSearch() {
            const search = (document.getElementById('globalSearch')?.value || '').toLowerCase();
            const tab = currentTab;
            const state = perilakuState[tab];
            
            state.filtered = state.allItems.filter(item => {
                if (tab === 'customer') {
                    return item.nama_customer.toLowerCase().includes(search);
                } else {
                    return item.nama_produk.toLowerCase().includes(search) || (item.barcode && item.barcode.toLowerCase().includes(search));
                }
            });
            
            state.page = 1;
            
            if (tab === 'customer') renderCustomerList();
            else renderProductList();
        }

        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value || 0);
        }

        function applyGlobalFilter(type, value) {
            if (type === 'store') {
                currentStoreId = value;
                document.getElementById('filterStoreId').value = value;
                // Update dropdown visual
                document.querySelectorAll('a[onclick^="applyGlobalFilter(\'store\'"]').forEach(a => a.classList.remove('active-dropdown-item'));
                const active = document.querySelector(`a[onclick="applyGlobalFilter('store', '${value}')"]`);
                if(active) active.classList.add('active-dropdown-item');
                
                const icon = document.querySelector('button[title="Filter Toko"] iconify-icon');
                if(icon) icon.className = (value && value !== 'all') ? 'text-primary-blue' : '';
            }
            if (type === 'sort') {
                currentSort = value;
                document.getElementById('filterSort').value = value;
                document.querySelectorAll('a[onclick^="applyGlobalFilter(\'sort\'"]').forEach(a => a.classList.remove('active-dropdown-item'));
                const active = document.querySelector(`a[onclick="applyGlobalFilter('sort', '${value}')"]`);
                if(active) active.classList.add('active-dropdown-item');
                
                const icon = document.querySelector('button[title="Urutkan"] iconify-icon');
                if(icon) icon.className = (value) ? 'text-primary-blue' : '';
            }
            
            // Close dropdowns
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            
            // Load data without reload
            loadData();
        }

        function applyDateFilter() {
            currentYear = document.getElementById('year-selector').value;
            const monthSelect = document.getElementById('month-selector');
            currentMonth = monthSelect ? monthSelect.value : '';
            
            document.getElementById('filterYear').value = currentYear;
            document.getElementById('filterMonth').value = currentMonth;
            
            const icon = document.querySelector('button[title="Filter Waktu"] iconify-icon');
            if(icon) icon.className = (currentYear || currentMonth) ? 'text-primary-blue' : '';
            
            // Close dropdowns
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            
            loadData();
        }

        // ═══════════════════════════════════════
        //  Tab Switching
        // ═══════════════════════════════════════
        function switchTab(tab) {
            currentTab = tab;
            if (document.getElementById('filterActiveTab')) {
                document.getElementById('filterActiveTab').value = tab;
            }

            document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
            document.getElementById('pill-' + tab).classList.add('active');

            document.getElementById('view-customer').style.display = (tab === 'customer') ? 'block' : 'none';
            document.getElementById('view-produk').style.display = (tab === 'produk') ? 'block' : 'none';

            // Toggle UI elements
            const searchInput = document.getElementById('globalSearch');
            if (tab === 'customer') {
                searchInput.placeholder = 'Cari nama customer...';
            } else {
                searchInput.placeholder = 'Cari nama produk / barcode...';
            }
            
            document.getElementById('sortDropdown').style.display = (tab === 'produk') ? 'inline-block' : 'none';
            document.getElementById('month-filter-group').style.display = (tab === 'produk') ? 'block' : 'none';

            loadData();
        }

        // ═══════════════════════════════════════
        //  Filters
        // ═══════════════════════════════════════

        function switchKanal(kanal) {
            currentKanal = kanal;
            document.getElementById('kanal-semua').classList.remove('active');
            document.getElementById('kanal-offline').classList.remove('active');
            document.getElementById('kanal-online').classList.remove('active');
            document.getElementById('kanal-' + kanal).classList.add('active');
            loadData();
        }

        function handleSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => applyClientSearch(), 300);
        }

        function toggleDropdown(event) {
            event.stopPropagation();
            const dd = event.currentTarget.nextElementSibling;
            document.querySelectorAll('.dropdown-content').forEach(el => {
                if (el !== dd) el.classList.remove('show');
            });
            dd.classList.toggle('show');
        }

        window.addEventListener('click', e => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            }
        });

        // ═══════════════════════════════════════
        //  Data Loading
        // ═══════════════════════════════════════
        function loadData() {
            if (currentTab === 'customer') {
                loadCustomerData();
            } else {
                loadProductData();
            }
        }

        // ─── CUSTOMER ───
        async function loadCustomerData() {
            const container = document.getElementById('customer-list');
            const summaryOmset = document.getElementById('cust-total-omset');
            const summaryCount = document.getElementById('cust-total-count');

            container.innerHTML = renderSkeleton(5);
            document.getElementById('customer-pagination-container').style.display = 'none';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15s timeout

            try {
                const res = await fetch(
                    `/perilaku/api/customer/yearly?store_id=${currentStoreId}&year=${currentYear}&kanal=${currentKanal}`,
                    { signal: controller.signal }
                );
                clearTimeout(timeout);

                const data = await res.json();

                if (!res.ok) {
                    const msg = data.error || 'Terjadi kesalahan saat memuat data';
                    container.innerHTML = renderError(msg);
                    return;
                }

                summaryOmset.textContent = formatCurrency(data.total_omset);
                summaryCount.textContent = data.total_customers + ' Customer';

                perilakuState.customer.allItems = data.customers || [];
                applyClientSearch();
                
            } catch (err) {
                clearTimeout(timeout);
                console.error(err);
                const isTimeout = err.name === 'AbortError';
                container.innerHTML = renderError(
                    isTimeout ? 'Koneksi terlalu lama. Periksa jaringan atau coba lagi.' : 'Terjadi kesalahan saat memuat data'
                );
            }
        }
        
        function renderCustomerList() {
            const container = document.getElementById('customer-list');
            const state = perilakuState.customer;
            
            if (state.filtered.length === 0) {
                container.innerHTML = renderEmpty('Belum ada data customer');
                document.getElementById('customer-pagination-container').style.display = 'none';
                return;
            }
            
            const totalPages = Math.ceil(state.filtered.length / state.perPage) || 1;
            if (state.page > totalPages) state.page = totalPages;
            
            const start = (state.page - 1) * state.perPage;
            const paginated = state.filtered.slice(start, start + state.perPage);
            
            container.innerHTML = paginated.map((c, i) => renderCustomerCard(c, start + i + 1)).join('');
            renderPaginationUI('customer', totalPages);
        }

        function renderCustomerCard(customer, rank) {
            const monthsHtml = customer.months.map(m => `
                <div class="perilaku-month-row">
                    <span class="month-label">${monthNames[m.bulan - 1] || 'Bulan ' + m.bulan}</span>
                    <span class="month-value">${formatCurrency(m.total_omset)}</span>
                    <a href="/perilaku/customer/${customer.contact_id}?store_id=${currentStoreId}&year=${currentYear}&month=${m.bulan}"
                       class="btn-chart-link" title="Lihat Grafik Harian">
                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                    </a>
                </div>
            `).join('');

            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card" onclick="toggleAccordion(this)">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${customer.nama_customer}</div>
                        <div class="perilaku-subtitle">${customer.months.length} bulan aktif</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(customer.total_omset)}</div>
                        <iconify-icon icon="solar:alt-arrow-down-bold-duotone" class="accordion-icon"></iconify-icon>
                    </div>
                </div>
                <div class="perilaku-card-detail" style="display: none;">
                    ${monthsHtml}
                </div>
            </div>`;
        }

        // ─── PRODUCT ───
        async function loadProductData() {
            const container = document.getElementById('product-list');
            const summaryOmset = document.getElementById('prod-total-omset');
            const summaryLaba = document.getElementById('prod-total-laba');
            const summaryFreq = document.getElementById('prod-total-freq');

            container.innerHTML = renderSkeleton(5);
            document.getElementById('produk-pagination-container').style.display = 'none';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15s timeout

            try {
                let url =
                    `/perilaku/api/product/yearly?store_id=${currentStoreId}&year=${currentYear}&sort=${currentSort}`;
                if (currentMonth) url += `&month=${currentMonth}`;

                const res = await fetch(url, { signal: controller.signal });
                clearTimeout(timeout);

                const data = await res.json();

                if (!res.ok) {
                    const msg = data.error || 'Terjadi kesalahan saat memuat data';
                    container.innerHTML = renderError(msg);
                    return;
                }

                summaryOmset.textContent = formatCurrency(data.total_omset);
                summaryLaba.textContent = formatCurrency(data.total_laba);
                summaryFreq.textContent = (data.total_freq || 0) + ' item';

                perilakuState.produk.allItems = data.products || [];
                perilakuState.produk.mode = data.mode;
                applyClientSearch();
                
            } catch (err) {
                clearTimeout(timeout);
                console.error(err);
                const isTimeout = err.name === 'AbortError';
                container.innerHTML = renderError(
                    isTimeout ? 'Koneksi terlalu lama. Periksa jaringan atau coba lagi.' : 'Terjadi kesalahan saat memuat data'
                );
            }
        }
        
        function renderProductList() {
            const container = document.getElementById('product-list');
            const state = perilakuState.produk;
            
            if (state.filtered.length === 0) {
                container.innerHTML = renderEmpty('Belum ada data produk');
                document.getElementById('produk-pagination-container').style.display = 'none';
                return;
            }
            
            const totalPages = Math.ceil(state.filtered.length / state.perPage) || 1;
            if (state.page > totalPages) state.page = totalPages;
            
            const start = (state.page - 1) * state.perPage;
            const paginated = state.filtered.slice(start, start + state.perPage);
            
            if (state.mode === 'monthly') {
                container.innerHTML = paginated.map((p, i) => renderProductCardMonthly(p, start + i + 1)).join('');
            } else {
                container.innerHTML = paginated.map((p, i) => renderProductCard(p, start + i + 1)).join('');
            }
            renderPaginationUI('produk', totalPages);
        }

        function renderProductCard(product, rank) {
            const monthsHtml = product.months.map(m => `
                <div class="perilaku-month-row">
                    <span class="month-label">${monthNames[m.bulan - 1] || 'Bulan ' + m.bulan}</span>
                    <div class="month-metrics">
                        <span class="metric-pill omset">${formatCurrency(m.total_omset)}</span>
                        <span class="metric-pill laba">${formatCurrency(m.total_laba)}</span>
                        <span class="metric-pill freq">${m.frekuensi} pcs</span>
                    </div>
                    <a href="/perilaku/produk/${product.product_id}?store_id=${currentStoreId}&year=${currentYear}&month=${m.bulan}"
                       class="btn-chart-link" title="Lihat Detail">
                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                    </a>
                </div>
            `).join('');

            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card" onclick="toggleAccordion(this)">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${product.nama_produk}</div>
                        <div class="perilaku-subtitle">${product.barcode || '-'} • ${product.frekuensi} terjual</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(product.total_omset)}</div>
                        <div class="perilaku-laba">${formatCurrency(product.total_laba)}</div>
                        <iconify-icon icon="solar:alt-arrow-down-bold-duotone" class="accordion-icon"></iconify-icon>
                    </div>
                </div>
                <div class="perilaku-card-detail" style="display: none;">
                    ${monthsHtml}
                </div>
            </div>`;
        }

        function renderProductCardMonthly(product, rank) {
            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card perilaku-card-flat">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${product.nama_produk}</div>
                        <div class="perilaku-subtitle">${product.barcode || '-'} • ${product.frekuensi} terjual</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(product.total_omset)}</div>
                        <div class="perilaku-laba">${formatCurrency(product.total_laba)}</div>
                        <a href="/perilaku/produk/${product.product_id}?store_id=${currentStoreId}&year=${currentYear}&month=${currentMonth}"
                           class="btn-chart-link" title="Lihat Detail">
                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>`;
        }

        // ═══════════════════════════════════════
        //  Helpers
        // ═══════════════════════════════════════
        function toggleAccordion(card) {
            const detail = card.querySelector('.perilaku-card-detail');
            const icon = card.querySelector('.accordion-icon');
            if (!detail) return;
            const isOpen = detail.style.display !== 'none';
            detail.style.display = isOpen ? 'none' : 'block';
            if (icon) icon.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        function renderSkeleton(count) {
            return Array(count).fill(0).map(() => `
                <div class="perilaku-card skeleton-card">
                    <div class="skeleton-line w60"></div>
                    <div class="skeleton-line w40"></div>
                </div>
            `).join('');
        }

        function renderEmpty(msg) {
            return `<div class="perilaku-empty">
                <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
                <p>${msg}</p>
            </div>`;
        }

        function renderError(msg) {
            return `<div class="perilaku-empty">
                <iconify-icon icon="solar:danger-circle-bold-duotone" style="font-size: 64px; color: #f87171;"></iconify-icon>
                <p style="color: #f87171; font-weight: 600;">${msg}</p>
                <button onclick="loadData()" class="btn-action" style="margin-top: 12px; gap: 6px;">
                    <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                    Coba Lagi
                </button>
            </div>`;
        }

        // ═══════════════════════════════════════
        //  Init
        // ═══════════════════════════════════════
        switchTab(currentTab);
    </script>
@endsection
