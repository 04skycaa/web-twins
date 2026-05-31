document.addEventListener('DOMContentLoaded', function() {
    if (!window.dashboardData) return;

    const data = window.dashboardData;

    const datasets = {
        harian: { labels: data.chartHarian.labels, offline: data.chartHarian.offline, online: data.chartHarian.online },
        mingguan: { labels: data.chartMingguan.labels, offline: data.chartMingguan.offline, online: data.chartMingguan.online },
        bulanan: { labels: data.chartBulanan.labels, offline: data.chartBulanan.offline, online: data.chartBulanan.online },
        tahunan: { labels: data.chartTahunan.labels, offline: data.chartTahunan.offline, online: data.chartTahunan.online }
    };

    window.datasets = datasets;

    const mainSalesElement = document.querySelector("#mainSalesChart");
    if (mainSalesElement) {
        window.mainChart = new ApexCharts(mainSalesElement, {
            series: [{ name: 'Offline (Kasir)', data: datasets.harian.offline }, { name: 'Online (Web)', data: datasets.harian.online }],
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Plus Jakarta Sans',
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#3b82f6', '#10b981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: datasets.harian.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: window.innerWidth < 720 ? {
                    rotate: -45,
                    hideOverlappingLabels: false,
                    style: { colors: '#94a3b8', fontWeight: 600, fontSize: '7.5px' }
                } : {
                    style: { colors: '#94a3b8', fontWeight: 600 }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) { 
                        if (val >= 1000000) return "Rp " + (val / 1000000).toFixed(1) + "jt";
                        if (val >= 1000) return "Rp " + (val / 1000).toFixed(0) + "rb";
                        if (val === 0) return "Rp 0";
                        return "Rp " + val;
                    },
                    style: { colors: '#94a3b8', fontWeight: 600 }
                }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontWeight: 700,
                labels: { colors: '#64748b' },
                markers: { radius: 12 }
            },
            markers: { size: 0, hover: { size: 5 } }
        });
        window.mainChart.render();
    }

    // Mini Pemasukan Chart
    const miniChartOpts = (color, dataSeries) => ({
        series: [{ data: dataSeries }],
        chart: { type: 'line', height: 60, sparkline: { enabled: true } },
        stroke: { curve: 'smooth', width: 3, colors: [color] },
        tooltip: { enabled: false }
    });

    const cfData = {
        harian: data.cfHarian,
        mingguan: data.cfMingguan,
        bulanan: data.cfBulanan,
        tahunan: data.cfTahunan
    };
    window.cfData = cfData;

    const pElement = document.querySelector("#pemasukanChart");
    const eElement = document.querySelector("#pengeluaranChart");
    if (pElement && eElement) {
        window.pChart = new ApexCharts(pElement, miniChartOpts('#10b981', cfData.harian.p_series));
        window.eChart = new ApexCharts(eElement, miniChartOpts('#f43f5e', cfData.harian.e_series));
        window.pChart.render();
        window.eChart.render();
    }

    // Debt Donut Chart
    const debtElement = document.querySelector("#debtChart");
    if (debtElement) {
        const debtOptions = {
            series: [data.totalPiutang, data.totalHutang],
            chart: { type: 'donut', height: 140 },
            labels: ['Piutang', 'Hutang'],
            colors: ['#3b82f6', '#f43f5e'],
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: { 
                                show: true,
                                fontSize: '9px',
                                fontWeight: 700,
                                color: '#64748b',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '11px',
                                fontWeight: 800,
                                color: '#0f172a',
                                offsetY: 5,
                                formatter: () => data.formattedTotalDebt
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                formatter: () => data.formattedTotalDebt,
                                fontSize: '9px',
                                fontWeight: 700,
                                color: '#64748b'
                            }
                        }
                    }
                }
            },
            legend: { show: false }
        };

        new ApexCharts(debtElement, debtOptions).render();
    }
});

// Global functions need to be outside DOMContentLoaded or assigned to window
window.updateMainChart = function(preset) {
    const picker = document.getElementById('year-range-picker');
    
    if (preset === 'tahunan') {
        picker.classList.remove('hidden');
    } else {
        picker.classList.add('hidden');
    }

    const d = window.datasets[preset];
    if (!d || !d.offline || d.offline.length === 0) {
        const currentStoreId = document.querySelector('.outlet-select')?.value || '';
        fetch(`${window.location.pathname}?preset=${preset}&type=main&store_id=${currentStoreId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            window.datasets[preset] = data;
            applyMainChartUpdate(preset);
        })
        .catch(err => console.error('Error loading chart:', err));
    } else {
        applyMainChartUpdate(preset);
    }
}

window.applyMainChartUpdate = function(preset) {
    const d = window.datasets[preset];
    window.mainChart.updateOptions({
        xaxis: {
            categories: d.labels,
            labels: window.innerWidth < 720 ? {
                rotate: -45,
                hideOverlappingLabels: false,
                style: { colors: '#94a3b8', fontWeight: 600, fontSize: '7.5px' }
            } : {
                style: { colors: '#94a3b8', fontWeight: 600 }
            }
        },
        series: [
            { name: 'Offline (Kasir)', data: d.offline },
            { name: 'Online (Web)', data: d.online }
        ]
    });
}

window.applyYearRange = function() {
    const from = document.getElementById('year-from').value;
    const to = document.getElementById('year-to').value;
    const url = new URL(window.location.href);
    url.searchParams.set('year_from', from);
    url.searchParams.set('year_to', to);
    window.location.href = url.toString();
}

window.updateCashFlow = function(preset) {
    const d = window.cfData[preset];
    if (!d || !d.p_series || (d.p_series.length <= 1 && d.p_series[0] === 0)) {
        const currentStoreId = document.querySelector('.outlet-select')?.value || '';
        fetch(`${window.location.pathname}?preset=${preset}&type=cashflow&store_id=${currentStoreId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            window.cfData[preset] = data;
            applyCashFlowUpdate(preset);
        })
        .catch(err => console.error('Error loading cashflow:', err));
    } else {
        applyCashFlowUpdate(preset);
    }
}

window.applyCashFlowUpdate = function(preset) {
    const d = window.cfData[preset];
    document.getElementById('cf-total-pemasukan').innerText = 'Rp ' + (d.total_pemasukan / 1000).toFixed(0) + 'k';
    document.getElementById('cf-total-pengeluaran').innerText = 'Rp ' + (d.total_pengeluaran / 1000).toFixed(0) + 'k';
    window.pChart.updateSeries([{ data: d.p_series }]);
    window.eChart.updateSeries([{ data: d.e_series }]);
}

window.refreshDashboard = function() {
    const btn = document.querySelector('[onclick="refreshDashboard()"]');
    const icon = btn.querySelector('iconify-icon');
    icon.style.transition = 'transform 1s ease';
    icon.style.transform = 'rotate(360deg)';
    
    const url = new URL(window.location.href);
    url.searchParams.set('refresh', '1');
    window.location.href = url.toString();
}

window.filterByStore = function(storeId) {
    const url = new URL(window.location.href);
    if (storeId) {
        url.searchParams.set('store_id', storeId);
    } else {
        url.searchParams.delete('store_id');
    }
    window.location.href = url.toString();
}

window.switchStockTab = function(tabId, btn) {
    document.querySelectorAll('.stock-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.stock-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}
