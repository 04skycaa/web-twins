@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <style>
        .dropdown-content a.active {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 12px !important;
        }

        .dropdown-content a.active::after {
            content: '✓';
            font-weight: bold;
            color: #0369a1;
            font-size: 16px;
        }
    </style>
@endpush

@section('content')
    <div x-data="{ tab: 'harian' }" class="fitur-container p-6 bg-gray-50 min-h-screen mobile-pb">
        <header class="action-bar flex flex-col md:flex-row gap-4 mb-6 md:mb-8 bg-transparent p-0">
            <!-- Tabs Laporan -->
            <div class="w-full md:w-auto overflow-x-auto pb-2 md:pb-0" style="scrollbar-width: none;">
                <nav class="flex space-x-1 bg-gray-200/50 p-1 rounded-2xl w-max" aria-label="Tab Laporan">
                    <button @click="tab = 'harian'; window.laporanActiveTab = 'harian'; fetchDailyData()"
                        :class="tab === 'harian' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">Harian</button>
                    <button @click="tab = 'bulanan'; window.laporanActiveTab = 'bulanan'; fetchMonthlyData()"
                        :class="tab === 'bulanan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">Bulanan</button>
                    <button @click="tab = 'tahunan'; window.laporanActiveTab = 'tahunan'; fetchAnnualData()"
                        :class="tab === 'tahunan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">Tahunan</button>
                    <button @click="tab = 'performa'; window.laporanActiveTab = 'performa'; fetchPerformaToko()"
                        :class="tab === 'performa' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 whitespace-nowrap">Performa Toko</button>
                </nav>
            </div>

            <!-- Actions (Filters & Extract) -->
            <div class="flex items-center gap-3 w-full md:w-auto ml-auto md:ml-auto">
                <div class="dropdown">
                    <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Toko">
                        <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" id="laporan-store-icon"></iconify-icon>
                    </button>
                    <div class="dropdown-content" id="outlet-dropdown">
                        <a href="#" data-store-id="" onclick="selectStore(event)" class="outlet-item active">Semua Outlet</a>
                        @foreach ($outlets as $outlet)
                            <a href="#" data-store-id="{{ $outlet->uuid }}" onclick="selectStore(event)"
                                class="outlet-item">
                                {{ $outlet->nama }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <input type="hidden" id="store-id-hidden" name="store_id" value="{{ $defaultStoreId ?? '' }}">

                <div class="dropdown">
                    <button type="button" class="btn-filter" onclick="toggleDropdown(event)" title="Filter Tanggal">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" id="laporan-calendar-icon"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 320px; right: 0; left: auto;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label
                                    style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tanggal</label>
                                <input id="date-selector" type="date" aria-label="Filter Tanggal"
                                    class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label
                                    style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Bulan</label>
                                <input id="month-selector" type="month" aria-label="Filter Bulan"
                                    class="form-control" value="{{ date('Y-m') }}">
                            </div>
                            <div>
                                <label
                                    style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tahun</label>
                                <input id="year-selector" type="number" aria-label="Filter Tahun" min="2020"
                                    max="2100" class="form-control" value="{{ date('Y') }}">
                            </div>
                            <button type="button" class="btn-action" style="width: 100%; justify-content: center;"
                                onclick="applyCalendarFilter()">Terapkan</button>
                        </div>
                    </div>
                </div>

                <div class="dropdown ml-auto md:ml-2">
                    <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                        <div class="dropdown-content" style="right: 0; left: auto;">
                            <a href="javascript:void(0)" onclick="downloadLaporanExport('excel')">
                                <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                                Excel
                            </a>
                            <a href="javascript:void(0)" onclick="downloadLaporanExport('pdf')">
                                <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
        </header>

        @include('laporan.partials.daily')
        @include('laporan.partials.monthly')
        @include('laporan.partials.yearly')
        @include('laporan.partials.performa-toko')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.laporanConfig = {
            exportPdfUrl: @json(route('laporan.export.pdf')),
            exportExcelUrl: @json(route('laporan.export.excel')),
            performaTokoUrl: @json(route('laporan.api.performa-toko')),
        };
    </script>
    @vite('resources/js/laporan.js')
@endsection
