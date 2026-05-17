function formatCurrency(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
}

// Financial contract mirrors backend:
// laba_bersih = (laba_kotor + pemasukan) - (pengeluaran + rugi)
// where pengeluaran excludes rugi.
function resolveMonthlyFinancialValues(summaryData) {
    const offlineOmset = Number(summaryData.omset || 0);
    const onlineOmset = Number(summaryData.penjualan_online || 0);
    const totalOmset = offlineOmset + onlineOmset;
    const hpp = Number(summaryData.hpp || 0);
    const labaKotor = Number(summaryData.laba_kotor ?? totalOmset - hpp);
    const pemasukan = Number(summaryData.pemasukan || 0);
    const pengeluaran = Number(summaryData.pengeluaran || 0);
    const rugi = Number(summaryData.rugi || 0);
    const labaBersih = Number(
        summaryData.laba_bersih ?? labaKotor + pemasukan - (pengeluaran + rugi),
    );

    return {
        offlineOmset,
        onlineOmset,
        totalOmset,
        hpp,
        labaKotor,
        pemasukan,
        pengeluaran,
        rugi,
        labaBersih,
    };
}

function getMonthlyLoadingMarkup(type) {
    if (type === "operator") {
        return `
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
                `;
    }

    if (type === "debt") {
        return `
                    <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse">
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                        <div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div>
                    </div>
                    <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse">
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                        <div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div>
                    </div>
                `;
    }

    return `
                <details class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm animate-pulse" open>
                    <summary class="flex list-none items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <div class="h-4 w-36 rounded bg-gray-200"></div>
                            <div class="mt-2 h-3 w-24 rounded bg-gray-100"></div>
                        </div>
                        <div class="text-right">
                            <div class="h-4 w-28 rounded bg-gray-200"></div>
                            <div class="mt-2 h-3 w-16 rounded bg-gray-100 ml-auto"></div>
                        </div>
                    </summary>
                    <div class="border-t border-gray-100 px-5 py-4 space-y-3">
                        <div class="h-11 rounded-2xl bg-gray-100"></div>
                        <div class="h-11 rounded-2xl bg-gray-100"></div>
                        <div class="h-11 rounded-2xl bg-gray-100"></div>
                    </div>
                </details>
            `;
}

function setMonthlyLoadingPlaceholders() {
    const operatorList = document.getElementById("monthly-operator-list");
    const debtList = document.getElementById("monthly-debt-list");
    const transactionList = document.getElementById("monthly-transaction-list");

    if (operatorList) {
        operatorList.innerHTML = getMonthlyLoadingMarkup("operator");
    }

    if (debtList) {
        debtList.innerHTML = getMonthlyLoadingMarkup("debt");
    }

    if (transactionList) {
        transactionList.innerHTML = getMonthlyLoadingMarkup("transaction");
    }
}

function normalizeCashboxMethod(name) {
    const normalized = String(name || "")
        .trim()
        .toLowerCase();

    if (normalized.includes("tunai") || normalized.includes("cash")) {
        return "cash";
    }

    if (normalized.includes("online")) {
        return "online";
    }

    if (normalized.includes("transfer")) {
        return "transfer";
    }

    return "other";
}

function getCashboxCardTheme(name, index, variant) {
    const themes = {
        cash: {
            label: "Tunai",
            background: "#eff6ff",
            border: "#bfdbfe",
            accent: "#2563eb",
            iconBackground: "#dbeafe",
            icon: "solar:wallet-bold",
        },
        online: {
            label: "Online",
            background: "#ecfeff",
            border: "#a5f3fc",
            accent: "#0284c7",
            iconBackground: "#cffafe",
            icon: "mdi:cloud-check",
        },
        transfer: {
            label: "Transfer",
            background: "#f0fdf4",
            border: "#bbf7d0",
            accent: "#16a34a",
            iconBackground: "#dcfce7",
            icon: "solar:card-transfer-bold",
        },
        other: {
            label: "Metode Lain",
            background: variant === "annual" ? "#f8fafc" : "#faf5ff",
            border: variant === "annual" ? "#e2e8f0" : "#e9d5ff",
            accent: variant === "annual" ? "#475569" : "#7c3aed",
            iconBackground: variant === "annual" ? "#e2e8f0" : "#f3e8ff",
            icon: "solar:card-bold",
        },
    };

    const methodKey = normalizeCashboxMethod(name);
    const theme = themes[methodKey] || themes.other;

    return {
        label: theme.label,
        style: [
            `background: ${theme.background}`,
            `border: 1px solid ${theme.border}`,
            "box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06)",
        ].join("; "),
        accent: theme.accent,
        iconBackground: theme.iconBackground,
        icon: theme.icon,
        methodKey,
    };
}

function renderCashboxCards(items, variant) {
    return items
        .map((item, index) => {
            const namaMetode = item.nama_metode || "-";
            const total = formatCurrency(item.total || 0);
            const theme = getCashboxCardTheme(namaMetode, index, variant);

            return `
                <div class="rounded-[1.5rem] p-4 overflow-hidden min-h-[100px] transition-shadow hover:shadow-md" style="${theme.style}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background:${theme.iconBackground}; color:${theme.accent};">
                            <iconify-icon icon="${theme.icon}" class="text-2xl"></iconify-icon>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[11px] font-bold uppercase tracking-[0.2em]" style="color:${theme.accent};">${theme.label}</div>
                            <div class="mt-1 text-sm font-bold text-gray-800 truncate">${namaMetode}</div>
                            <div class="mt-2 text-2xl font-bold tracking-tight text-gray-900">${total}</div>
                        </div>
                    </div>
                </div>
            `;
        })
        .join("");
}
function getDailyLoadingMarkup(type) {
    if (type === "operator") {
        return `
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 animate-pulse">
                        <div class="h-4 w-32 rounded bg-gray-200"></div>
                        <div class="mt-3 grid grid-cols-1 gap-3">
                            <div class="h-14 rounded-2xl bg-gray-100"></div>
                            <div class="h-14 rounded-2xl bg-gray-100"></div>
                        </div>
                    </div>
                `;
    }

    if (type === "cashbox") {
        return `
                    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 animate-pulse">
                        <div class="h-4 w-40 rounded bg-gray-200"></div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="h-20 rounded-2xl bg-gray-100"></div>
                            <div class="h-20 rounded-2xl bg-gray-100"></div>
                            <div class="h-20 rounded-2xl bg-gray-100"></div>
                        </div>
                    </div>
                `;
    }

    return `
                <div class="px-6 py-4">
                    <div class="h-4 w-48 rounded bg-gray-200"></div>
                    <div class="mt-3 space-y-3">
                        <div class="h-12 rounded-2xl bg-gray-100"></div>
                        <div class="h-12 rounded-2xl bg-gray-100"></div>
                    </div>
                </div>
            `;
}

function setDailyLoadingPlaceholders() {
    const operatorList = document.getElementById("operator-list");
    const cashboxList = document.getElementById("daily-cashbox-list");
    const onlineList = document.getElementById("daily-online-list");

    if (operatorList)
        operatorList.innerHTML = getDailyLoadingMarkup("operator");
    if (cashboxList) cashboxList.innerHTML = getDailyLoadingMarkup("cashbox");
    if (onlineList) onlineList.innerHTML = getDailyLoadingMarkup("online");
}

function setLoadingState(scope, isLoading) {
    const overlay = document.getElementById(`${scope}-loading-overlay`);
    if (overlay) {
        overlay.classList.toggle("opacity-0", !isLoading);
        overlay.classList.toggle("opacity-100", isLoading);
        overlay.classList.toggle("pointer-events-none", !isLoading);
        overlay.classList.toggle("pointer-events-auto", isLoading);
    }
}

function setTextIfExists(id, text) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = text;
}

async function fetchDailyData() {
    const storeId = document.getElementById("store-id-hidden").value;
    const date = document.getElementById("date-selector").value;

    const requestId = (window.laporanDailyRequestId || 0) + 1;
    window.laporanDailyRequestId = requestId;

    // show placeholders immediately
    setDailyLoadingPlaceholders();

    try {
        const response = await fetch(
            `/laporan/api/daily/summary?store_id=${storeId}&date=${date}`,
        );
        if (!response.ok) throw new Error("Gagal fetch data");

        const data = await response.json();
        if (window.laporanDailyRequestId !== requestId) return;

        // Update Daily Summary Card immediately
        document.getElementById("omset-value").textContent = formatCurrency(
            data.omset || 0,
        );
        document.getElementById("laba-kotor-value").textContent =
            formatCurrency(data.laba_kotor || 0);

        // Update Mini Cards
        document.getElementById("pemasukan-value").textContent = formatCurrency(
            data.pemasukan || 0,
        );
        document.getElementById("pengeluaran-value").textContent =
            formatCurrency(data.pengeluaran || 0);

        const renderIfCurrent = (renderFn) => (payload) => {
            if (window.laporanDailyRequestId === requestId) renderFn(payload);
        };

        // load sub-requests in background
        fetch(`/laporan/api/daily/operators?store_id=${storeId}&date=${date}`)
            .then((r) =>
                r.ok
                    ? r.json()
                    : Promise.reject(new Error("Gagal fetch operator harian")),
            )
            .then((d) =>
                renderIfCurrent((p) =>
                    renderDailyOperatorCards(p.operators || p || []),
                )(d),
            )
            .catch((err) => console.error("Error daily operators:", err));

        fetchDailyCashbox(storeId, date).catch((err) =>
            console.error("Error daily cashbox:", err),
        );

        fetch(`/laporan/api/daily/online?store_id=${storeId}&date=${date}`)
            .then((r) =>
                r.ok
                    ? r.json()
                    : Promise.reject(new Error("Gagal fetch online harian")),
            )
            .then((d) =>
                renderIfCurrent((p) => {
                    const list = document.getElementById("daily-online-list");
                    if (!list) return;
                    const orders = p.orders || p || [];
                    if (orders.length === 0) {
                        list.innerHTML =
                            '<div class="px-6 py-4 text-sm text-gray-500">Tidak ada transaksi online hari ini</div>';
                        return;
                    }
                    list.innerHTML = orders
                        .map((order) => {
                            const time = order.tanggal
                                ? new Date(order.tanggal).toLocaleTimeString(
                                      "id-ID",
                                      {
                                          hour: "2-digit",
                                          minute: "2-digit",
                                      },
                                  )
                                : "-";
                            return `
                                <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 truncate">${order.customer || "Pelanggan"}</div>
                                        <div class="text-xs text-gray-500">${order.gateway || "Midtrans"} • ${time}</div>
                                    </div>
                                    <div class="font-bold text-emerald-600 whitespace-nowrap">${formatCurrency(order.total || 0)}</div>
                                </div>
                            `;
                        })
                        .join("");
                })(d),
            )
            .catch((err) => console.error("Error daily online:", err));
    } catch (error) {
        console.error("Error:", error);
        // keep placeholders visible or show message
    }
}

async function fetchDailyCashbox(storeId, date) {
    try {
        const response = await fetch(
            `/laporan/api/daily/cashbox?store_id=${storeId}&date=${date}`,
        );
        if (!response.ok) throw new Error("Gagal fetch cashbox");

        const data = await response.json();
        const list = document.getElementById("daily-cashbox-list");
        const items = data.items || [];

        if (items.length === 0) {
            list.innerHTML =
                '<div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Belum ada data cashbox</div>';
            return;
        }

        list.innerHTML = renderCashboxCards(items, "daily");
    } catch (error) {
        console.error("Error cashbox:", error);
    }
}

async function fetchDailyOnline(storeId, date) {
    try {
        const response = await fetch(
            `/laporan/api/daily/online?store_id=${storeId}&date=${date}`,
        );
        if (!response.ok) throw new Error("Gagal fetch online");

        const data = await response.json();
        const list = document.getElementById("daily-online-list");
        const orders = data.orders || [];

        if (orders.length === 0) {
            list.innerHTML =
                '<div class="px-6 py-4 text-sm text-gray-500">Tidak ada transaksi online hari ini</div>';
            return;
        }

        list.innerHTML = orders
            .map((order) => {
                const time = order.tanggal
                    ? new Date(order.tanggal).toLocaleTimeString("id-ID", {
                          hour: "2-digit",
                          minute: "2-digit",
                      })
                    : "-";
                return `
                        <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">${order.customer || "Pelanggan"}</div>
                                <div class="text-xs text-gray-500">${order.gateway || "Midtrans"} • ${time}</div>
                            </div>
                            <div class="font-bold text-emerald-600 whitespace-nowrap">${formatCurrency(order.total || 0)}</div>
                        </div>
                    `;
            })
            .join("");
    } catch (error) {
        console.error("Error online:", error);
    }
}

async function fetchMonthlyData() {
    const storeId = document.getElementById("store-id-hidden").value;
    const monthValue = document.getElementById("month-selector").value;
    const requestId = (window.laporanMonthlyRequestId || 0) + 1;
    window.laporanMonthlyRequestId = requestId;

    const monthlyResetValueIds = [
        "monthly-offline-omset-value",
        "monthly-online-omset-value",
        "monthly-total-omset-value",
        "monthly-hpp-value",
        "monthly-laba-kotor-value",
        "monthly-laba-bersih-value",
        "monthly-laba-bersih-badge",
        "monthly-pemasukan-value",
        "monthly-pengeluaran-value",
        "monthly-rugi-value",
    ];

    monthlyResetValueIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.textContent = "Rp 0";
    });

    if (!monthValue) {
        const operatorList = document.getElementById("monthly-operator-list");
        const debtList = document.getElementById("monthly-debt-list");
        const transactionList = document.getElementById(
            "monthly-transaction-list",
        );

        if (operatorList) {
            operatorList.innerHTML =
                '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>';
        }

        if (debtList) {
            debtList.innerHTML =
                '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet untuk memuat data</div>';
        }

        if (transactionList) {
            transactionList.innerHTML =
                '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>';
        }
        return;
    }

    const [year, month] = monthValue.split("-");

    setMonthlyLoadingPlaceholders();

    try {
        const summaryResponse = await fetch(
            `/laporan/api/monthly/summary?store_id=${storeId}&month=${month}&year=${year}`,
        );
        if (!summaryResponse.ok) throw new Error("Gagal fetch summary bulanan");
        const summaryData = await summaryResponse.json();

        if (window.laporanMonthlyRequestId !== requestId) return;

        const {
            offlineOmset,
            onlineOmset,
            totalOmset,
            hpp,
            labaKotor,
            pemasukan,
            pengeluaran,
            rugi,
            labaBersih,
        } = resolveMonthlyFinancialValues(summaryData);

        const setValue = (id, value, invertSign = false) => {
            const el = document.getElementById(id);
            if (!el) return;
            const numeric = Number(value) || 0;
            // determine whether to show as negative (red) for display
            const displayNegative = invertSign ? numeric > 0 : numeric < 0;
            const absFormatted = formatCurrency(Math.abs(numeric));
            el.textContent = displayNegative
                ? `-${absFormatted}`
                : absFormatted;
            // clear previous color classes we control
            el.classList.remove("text-red-300", "text-red-500", "text-white");
            if (displayNegative) {
                el.classList.add("text-red-500");
            } else {
                // allow element to inherit parent color
                el.classList.add("text-current");
            }
        };

        setValue("monthly-offline-omset-value", offlineOmset);
        setValue("monthly-online-omset-value", onlineOmset);
        setValue("monthly-total-omset-value", totalOmset);
        setValue("monthly-hpp-value", hpp);
        setValue("monthly-laba-kotor-value", labaKotor);
        setValue("monthly-laba-bersih-value", labaBersih, labaBersih < 0);
        setValue("monthly-laba-bersih-badge", labaBersih, labaBersih < 0);
        setValue("monthly-pemasukan-value", pemasukan);
        setValue("monthly-pengeluaran-value", pengeluaran);
        setValue("monthly-rugi-value", rugi, true);

        const renderIfCurrent = (renderFn) => (payload) => {
            if (window.laporanMonthlyRequestId === requestId) {
                renderFn(payload);
            }
        };

        fetch(
            `/laporan/api/monthly/operators?store_id=${storeId}&month=${month}&year=${year}`,
        )
            .then((response) =>
                response.ok
                    ? response.json()
                    : Promise.reject(new Error("Gagal fetch operator bulanan")),
            )
            .then(
                renderIfCurrent((data) =>
                    renderMonthlyOperators(data.operators || []),
                ),
            )
            .catch((error) => console.error("Error monthly operators:", error));

        fetch(`/laporan/api/monthly/debt-summary?store_id=${storeId}`)
            .then((response) =>
                response.ok
                    ? response.json()
                    : Promise.reject(new Error("Gagal fetch debt summary")),
            )
            .then(
                renderIfCurrent((data) =>
                    renderMonthlyDebtSummary(data.items || []),
                ),
            )
            .catch((error) => console.error("Error monthly debt:", error));

        fetch(
            `/laporan/api/monthly/daily?store_id=${storeId}&month=${month}&year=${year}`,
        )
            .then((response) =>
                response.ok
                    ? response.json()
                    : Promise.reject(
                          new Error("Gagal fetch transaksi bulanan"),
                      ),
            )
            .then(
                renderIfCurrent((data) =>
                    renderMonthlyTransactions(data.daily || []),
                ),
            )
            .catch((error) =>
                console.error("Error monthly transactions:", error),
            );
    } catch (error) {
        console.error("Error:", error);
        alert("Error mengambil data laporan bulanan");
    }
}

async function fetchAnnualCashbox(storeId, year) {
    try {
        const response = await fetch(
            `/laporan/api/annual/cashbox?store_id=${storeId}&year=${year}`,
        );
        if (!response.ok) throw new Error("Gagal fetch cashbox tahunan");

        const data = await response.json();
        const list = document.getElementById("annual-cashbox-list");
        const items = data.items || [];

        if (items.length === 0) {
            list.innerHTML =
                '<div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Belum ada data cashbox tahunan</div>';
            return;
        }

        list.innerHTML = renderCashboxCards(items, "annual");
    } catch (error) {
        console.error("Error annual cashbox:", error);
    }
}

async function fetchAnnualData() {
    const storeId = document.getElementById("store-id-hidden").value;
    const year = document.getElementById("year-selector").value;

    setLoadingState("annual", true);

    if (!year) {
        setTextIfExists("annual-offline-omset-value", "Rp 0");
        setTextIfExists("annual-online-omset-value", "Rp 0");
        setTextIfExists("annual-total-omset-value", "Rp 0");
        setTextIfExists("annual-laba-kotor-value", "Rp 0");
        setTextIfExists("annual-laba-bersih-value", "Rp 0");
        setTextIfExists("annual-pemasukan-value", "Rp 0");
        setTextIfExists("annual-pengeluaran-value", "Rp 0");
        setTextIfExists("annual-hpp-value", "Rp 0");
        setTextIfExists("annual-rugi-value", "Rp 0");

        const annualOperatorList = document.getElementById(
            "annual-operator-list",
        );
        if (annualOperatorList) {
            annualOperatorList.innerHTML =
                '<div class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm animate-pulse"><div class="border-b border-gray-50 px-5 py-4"><div class="h-4 w-40 rounded bg-gray-200"></div><div class="mt-2 h-3 w-64 rounded bg-gray-100"></div></div><div class="p-5 space-y-3"><div class="h-16 rounded-2xl bg-gray-100"></div><div class="h-16 rounded-2xl bg-gray-100"></div><div class="h-16 rounded-2xl bg-gray-100"></div></div></div>';
        }

        const annualDebtList = document.getElementById("annual-debt-list");
        if (annualDebtList) {
            annualDebtList.innerHTML =
                '<div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse"><div class="h-4 w-24 rounded bg-gray-200"></div><div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div></div><div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5 animate-pulse"><div class="h-4 w-24 rounded bg-gray-200"></div><div class="mt-3 h-20 rounded-[1.25rem] bg-white"></div></div>';
        }

        const annualMonthlyList = document.getElementById(
            "annual-monthly-list",
        );
        if (annualMonthlyList) {
            annualMonthlyList.innerHTML =
                '<details class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm animate-pulse" open><summary class="flex list-none items-center justify-between gap-4 px-5 py-5"><div class="min-w-0"><div class="h-4 w-36 rounded bg-gray-200"></div><div class="mt-2 h-3 w-24 rounded bg-gray-100"></div></div><div class="text-right"><div class="h-4 w-28 rounded bg-gray-200"></div><div class="mt-2 h-3 w-16 rounded bg-gray-100 ml-auto"></div></div></summary><div class="border-t border-gray-100 px-5 py-5 space-y-4"><div class="h-14 rounded-2xl bg-gray-100"></div><div class="h-14 rounded-2xl bg-gray-100"></div><div class="h-14 rounded-2xl bg-gray-100"></div></div></details>';
        }
        setLoadingState("annual", false);
        return;
    }

    try {
        const summaryResponse = await fetch(
            `/laporan/api/annual/summary?store_id=${storeId}&year=${year}`,
        );
        if (!summaryResponse.ok) throw new Error("Gagal fetch summary tahunan");
        const summaryData = await summaryResponse.json();

        const offlineOmset = Number(summaryData.omset || 0);
        const onlineOmset = Number(summaryData.penjualan_online || 0);
        const totalOmset = offlineOmset + onlineOmset;

        setTextIfExists(
            "annual-offline-omset-value",
            formatCurrency(offlineOmset),
        );
        setTextIfExists(
            "annual-online-omset-value",
            formatCurrency(onlineOmset),
        );
        setTextIfExists("annual-total-omset-value", formatCurrency(totalOmset));
        setTextIfExists(
            "annual-laba-kotor-value",
            formatCurrency(summaryData.laba_kotor || 0),
        );
        setTextIfExists(
            "annual-laba-bersih-value",
            formatCurrency(summaryData.laba_bersih || 0),
        );
        setTextIfExists(
            "annual-pemasukan-value",
            formatCurrency(summaryData.pemasukan || 0),
        );
        setTextIfExists(
            "annual-pengeluaran-value",
            formatCurrency(summaryData.pengeluaran || 0),
        );
        setTextIfExists(
            "annual-hpp-value",
            formatCurrency(summaryData.hpp || 0),
        );
        setTextIfExists(
            "annual-rugi-value",
            formatCurrency(summaryData.rugi || 0),
        );

        const operatorsResponse = await fetch(
            `/laporan/api/annual/operators?store_id=${storeId}&year=${year}`,
        );
        const operatorsPromise = operatorsResponse.ok
            ? operatorsResponse
                  .json()
                  .then((operatorsData) =>
                      renderAnnualOperators(operatorsData.operators || []),
                  )
            : Promise.resolve();

        const debtResponse = await fetch(
            `/laporan/api/annual/debt-summary?store_id=${storeId}`,
        );
        const debtPromise = debtResponse.ok
            ? debtResponse
                  .json()
                  .then((debtData) =>
                      renderAnnualDebtSummary(debtData.items || []),
                  )
            : Promise.resolve();

        const monthlyResponse = await fetch(
            `/laporan/api/annual/monthly?store_id=${storeId}&year=${year}`,
        );
        const monthlyPromise = monthlyResponse.ok
            ? monthlyResponse
                  .json()
                  .then((monthlyData) =>
                      renderAnnualMonthly(monthlyData.monthly || []),
                  )
            : Promise.resolve();

        await Promise.all([operatorsPromise, debtPromise, monthlyPromise]);
    } catch (error) {
        console.error("Error annual:", error);
    } finally {
        setLoadingState("annual", false);
    }
}

function renderAnnualOperators(operators) {
    const list = document.getElementById("annual-operator-list");
    if (!list) return;

    if (operators.length === 0) {
        list.innerHTML =
            '<div class="rounded-[1.5rem] border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data operator tahun ini</div>';
        return;
    }

    const totalMasuk = operators.reduce(
        (sum, op) => sum + Number(op.masuk || 0),
        0,
    );
    const totalKeluar = operators.reduce(
        (sum, op) => sum + Number(op.keluar || 0),
        0,
    );

    list.innerHTML = `
        <div class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-50 px-5 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-bold text-gray-900">Daftar Operator</div>
                        <div class="mt-1 text-xs text-gray-500">Total uang masuk dan keluar per operator</div>
                    </div>
                    <div class="flex gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Masuk ${formatCurrency(totalMasuk)}</span>
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Keluar ${formatCurrency(totalKeluar)}</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/70 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama Operator</th>
                            <th class="px-5 py-3">Masuk</th>
                            <th class="px-5 py-3 text-right">Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${operators
                            .map(
                                (op) => `
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-5 py-4 font-bold text-gray-900">${op.name || "Unknown"}</td>
                                        <td class="px-5 py-4 font-semibold text-emerald-600">${formatCurrency(op.masuk || 0)}</td>
                                        <td class="px-5 py-4 text-right font-semibold text-rose-500">${formatCurrency(op.keluar || 0)}</td>
                                    </tr>
                                `,
                            )
                            .join("")}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderDebtSummary(items, listId) {
    const list = document.getElementById(listId);
    if (!list) return;

    let hutang = 0;
    let piutang = 0;

    items.forEach((item) => {
        if ((item.tipe || "").toLowerCase() === "utang") {
            hutang = Number(item.total_belum_lunas || 0);
        }
        if ((item.tipe || "").toLowerCase() === "piutang") {
            piutang = Number(item.total_belum_lunas || 0);
        }
    });

    list.innerHTML = `
        <div class="rounded-[1.5rem] border border-rose-100 bg-rose-50/60 p-5">
            <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-rose-500/10 p-3 text-rose-600">
                    <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-sm font-bold text-rose-700">Hutang</div>
                    <div class="text-xs text-rose-600/80">Total kewajiban yang belum lunas</div>
                </div>
            </div>
            <div class="mt-4 text-2xl font-bold text-rose-600">${formatCurrency(hutang)}</div>
        </div>
        <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/60 p-5">
            <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-emerald-500/10 p-3 text-emerald-600">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-sm font-bold text-emerald-700">Piutang</div>
                    <div class="text-xs text-emerald-600/80">Total dana pelanggan yang belum lunas</div>
                </div>
            </div>
            <div class="mt-4 text-2xl font-bold text-emerald-600">${formatCurrency(piutang)}</div>
        </div>
    `;
}

function renderMonthlyDebtSummary(items) {
    renderDebtSummary(items, "monthly-debt-list");
}

function renderAnnualDebtSummary(items) {
    renderDebtSummary(items, "annual-debt-list");
}

function renderAnnualMonthly(monthly) {
    const list = document.getElementById("annual-monthly-list");
    if (!list) return;

    if (monthly.length === 0) {
        list.innerHTML =
            '<div class="rounded-[1.5rem] border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data transaksi tahun ini</div>';
        return;
    }

    const monthNames = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];

    const grouped = {
        penjualan: [],
        penjualan_online: [],
        pembelian: [],
        transfer: [],
        retur: [],
        rugi: [],
    };

    monthly.forEach((row) => {
        if (Object.prototype.hasOwnProperty.call(grouped, row.jenis)) {
            grouped[row.jenis].push(row);
        }
    });

    const order = [
        "penjualan",
        "penjualan_online",
        "pembelian",
        "transfer",
        "retur",
        "rugi",
    ];

    list.innerHTML = order
        .map((jenis) => {
            const rows = grouped[jenis] || [];
            if (!rows.length) return "";

            const label = getMonthlyTransactionLabel(jenis);
            const showLaba = ["penjualan", "penjualan_online"].includes(jenis);
            const total = rows.reduce(
                (sum, row) => sum + Number(row.total || 0),
                0,
            );
            const laba = rows.reduce(
                (sum, row) => sum + Number(row.laba || 0),
                0,
            );
            const freq = rows.reduce(
                (sum, row) => sum + Number(row.frekuensi || 0),
                0,
            );
            const negative = isMonthlyTransactionNegative(jenis);

            return `
                <details class="group overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm mb-4" ${showLaba ? "open" : ""}>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5">
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900">${label}</div>
                            <div class="mt-1 text-xs text-gray-500">${rows.length} entri bulanan</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold ${negative ? "text-rose-600" : "text-gray-900"}">${negative ? "-" : ""}${formatCurrency(total)}</div>
                            ${showLaba ? `<div class="text-xs font-semibold text-emerald-600">Laba ${formatCurrency(laba)}</div>` : ""}
                            <div class="text-xs text-gray-500">${freq} trx</div>
                        </div>
                    </summary>
                    <div class="border-t border-gray-100 px-5 py-5">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="py-3 pr-4">Bulan</th>
                                        <th class="py-3 pr-4">Total</th>
                                        ${showLaba ? '<th class="py-3 pr-4">Laba</th>' : ""}
                                        <th class="py-3 text-right">Frekuensi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${rows
                                        .map((row) => {
                                            const bulanLabel =
                                                monthNames[
                                                    (row.bulan || 1) - 1
                                                ] ||
                                                `Bulan ${row.bulan || "-"}`;
                                            const rowTotal = Number(
                                                row.total || 0,
                                            );
                                            const rowLaba = Number(
                                                row.laba || 0,
                                            );
                                            const rowFreq = Number(
                                                row.frekuensi || 0,
                                            );
                                            const totalClass = negative
                                                ? "text-rose-600"
                                                : "text-gray-900";
                                            const labaCell = showLaba
                                                ? `<td class="py-3 pr-4 font-semibold text-emerald-600">${formatCurrency(rowLaba)}</td>`
                                                : "";
                                            return `<tr><td class="py-4 pr-4 font-medium text-gray-900">${bulanLabel}</td><td class="py-4 pr-4 font-semibold ${totalClass}">${negative ? "-" : ""}${formatCurrency(rowTotal)}</td>${labaCell}<td class="py-4 text-right text-gray-600">${rowFreq} trx</td></tr>`;
                                        })
                                        .join("")}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            `;
        })
        .filter(Boolean)
        .join("");
}

function updateStoreLabel(storeId) {
    const label = document.getElementById("store-label");
    if (!label) return;

    if (!storeId) {
        label.textContent = "Semua Outlet";
        return;
    }

    const selectedLink = document.querySelector(
        `.dropdown-content a[data-store-id="${storeId}"]`,
    );
    label.textContent = selectedLink
        ? selectedLink.textContent.trim()
        : "Semua Outlet";
}

function loadActiveTabData() {
    const activeTab = window.laporanActiveTab || "harian";

    if (activeTab === "bulanan") {
        fetchMonthlyData();
        return;
    }

    if (activeTab === "tahunan") {
        fetchAnnualData();
        return;
    }

    fetchDailyData();
}

function downloadLaporanExport(format) {
    const activeTab = window.laporanActiveTab || "harian";
    const storeId = document.getElementById("store-id-hidden").value || "";
    const date = document.getElementById("date-selector").value || "";
    const monthValue = document.getElementById("month-selector").value || "";
    const year = document.getElementById("year-selector").value || "";

    const exportPdfUrl =
        (window.laporanConfig && window.laporanConfig.exportPdfUrl) ||
        "/laporan/export/pdf";
    const exportExcelUrl =
        (window.laporanConfig && window.laporanConfig.exportExcelUrl) ||
        "/laporan/export/excel";
    const url = new URL(
        format === "pdf" ? exportPdfUrl : exportExcelUrl,
        window.location.origin,
    );

    url.searchParams.set("active_tab", activeTab);

    if (storeId) {
        url.searchParams.set("store_id", storeId);
    }

    if (activeTab === "harian") {
        if (date) url.searchParams.set("date", date);
    } else if (activeTab === "bulanan") {
        if (monthValue) {
            const [month, yearValue] = monthValue.split("-");
            url.searchParams.set("month", month);
            url.searchParams.set("year", yearValue);
        }
    } else if (activeTab === "tahunan" && year) {
        url.searchParams.set("year", year);
    }

    window.location.href = url.toString();
}

function applyCalendarFilter() {
    document
        .querySelectorAll(".dropdown-content")
        .forEach((dropdown) => dropdown.classList.remove("show"));
    loadActiveTabData();
}

function getDailyOperatorJenisLabel(jenis) {
    switch (jenis) {
        case "penjualan":
            return "Penjualan";
        case "pembelian":
            return "Pembelian";
        case "transfer":
            return "Transfer";
        case "pemasukan":
            return "Pemasukan";
        case "pengeluaran":
            return "Pengeluaran";
        case "pelunasan_piutang":
            return "Pelunasan Piutang";
        case "pembayaran_hutang":
            return "Pembayaran Hutang";
        case "retur":
            return "Retur";
        case "rugi":
            return "Produk Rugi";
        default:
            return jenis || "-";
    }
}

function isDailyOperatorStokJenis(jenis) {
    return ["retur", "rugi"].includes(jenis);
}

function isDailyOperatorNegativeJenis(jenis) {
    return ["pembelian", "pengeluaran", "pembayaran_hutang", "rugi"].includes(
        jenis,
    );
}

function getDailyOperatorJenisOrder(jenis) {
    const order = [
        "penjualan",
        "pembelian",
        "transfer",
        "pelunasan_piutang",
        "pembayaran_hutang",
        "pemasukan",
        "pengeluaran",
    ];
    const stokOrder = ["retur", "rugi"];
    const idx = order.indexOf(jenis);
    if (idx >= 0) return idx;
    const stokIdx = stokOrder.indexOf(jenis);
    return stokIdx >= 0 ? 100 + stokIdx : 999;
}

function renderDailyOperatorCards(rows) {
    const list = document.getElementById("operator-list");
    const grouped = new Map();

    rows.forEach((row) => {
        const name = row.name || "Unknown";
        const jenis = row.jenis || "";
        const total = Number(row.total || 0);

        if (!grouped.has(name)) {
            grouped.set(name, {
                name,
                items: [],
            });
        }

        grouped.get(name).items.push({
            jenis,
            total,
        });
    });

    if (grouped.size === 0) {
        list.innerHTML =
            '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Belum ada data operator hari ini</div>';
        return;
    }

    list.innerHTML = Array.from(grouped.values())
        .map((group) => {
            const items = group.items
                .slice()
                .sort(
                    (a, b) =>
                        getDailyOperatorJenisOrder(a.jenis) -
                        getDailyOperatorJenisOrder(b.jenis),
                );
            const laciItems = items.filter(
                (item) => !isDailyOperatorStokJenis(item.jenis),
            );
            const stokItems = items.filter((item) =>
                isDailyOperatorStokJenis(item.jenis),
            );
            const netLaci = laciItems.reduce((sum, item) => {
                const amount = Number(item.total || 0);
                return (
                    sum +
                    (isDailyOperatorNegativeJenis(item.jenis)
                        ? -amount
                        : amount)
                );
            }, 0);

            const renderItem = (item) => {
                const label = getDailyOperatorJenisLabel(item.jenis);
                const amount = Number(item.total || 0);
                const displayAmount = isDailyOperatorNegativeJenis(item.jenis)
                    ? `-${formatCurrency(amount)}`
                    : formatCurrency(amount);
                const amountClass = isDailyOperatorNegativeJenis(item.jenis)
                    ? "text-red-500"
                    : item.jenis === "retur"
                      ? "text-amber-600"
                      : "text-emerald-600";

                return `
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900">${label}</div>
                                <div class="text-xs text-gray-500">${item.jenis || "-"}</div>
                            </div>
                            <div class="font-bold whitespace-nowrap ${amountClass}">${displayAmount}</div>
                        </div>
                    `;
            };

            return `
                    <article class="rounded-[1.5rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 font-bold uppercase">${(group.name || "?").charAt(0)}</div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 truncate">${group.name || "Unknown"}</div>
                                <div class="text-xs text-gray-500">Aktivitas transaksi harian</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <div class="mb-3 flex items-center justify-between text-xs font-bold uppercase tracking-[0.18em] text-gray-500">
                                    <span>Uang di Laci</span>
                                    <span>${laciItems.length} item</span>
                                </div>
                                <div class="space-y-2">
                                    ${laciItems.length > 0 ? laciItems.map(renderItem).join("") : '<div class="rounded-2xl border border-dashed border-gray-200 p-3 text-sm text-gray-500">Tidak ada data laci</div>'}
                                </div>
                                <div class="mt-3 flex items-center justify-between rounded-2xl bg-blue-50 px-4 py-3">
                                    <span class="font-semibold text-gray-700">Net Laci</span>
                                    <span class="font-bold text-blue-600">${formatCurrency(netLaci)}</span>
                                </div>
                            </div>
                            <div>
                                <div class="mb-3 flex items-center justify-between text-xs font-bold uppercase tracking-[0.18em] text-gray-500">
                                    <span>Info Stok</span>
                                    <span>${stokItems.length} item</span>
                                </div>
                                <div class="space-y-2">
                                    ${stokItems.length > 0 ? stokItems.map(renderItem).join("") : '<div class="rounded-2xl border border-dashed border-gray-200 p-3 text-sm text-gray-500">Tidak ada info stok</div>'}
                                </div>
                            </div>
                        </div>
                    </article>
                `;
        })
        .join("");
}

function toggleDropdown(event) {
    event.stopPropagation();
    const dropdown = event.currentTarget.nextElementSibling;

    document.querySelectorAll(".dropdown-content").forEach((content) => {
        if (content !== dropdown) {
            content.classList.remove("show");
        }
    });

    dropdown.classList.toggle("show");
}

function renderMonthlyOperators(operators) {
    const list = document.getElementById("monthly-operator-list");
    if (!list) return;

    if (operators.length === 0) {
        list.innerHTML =
            '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data operator bulan ini</div>';
        return;
    }

    const totalMasuk = operators.reduce(
        (sum, op) => sum + Number(op.masuk || 0),
        0,
    );
    const totalKeluar = operators.reduce(
        (sum, op) => sum + Number(op.keluar || 0),
        0,
    );

    list.innerHTML = `
                <div class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-50 px-5 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-900">Daftar Operator</div>
                                <div class="mt-1 text-xs text-gray-500">Ringkasan transaksi masuk dan keluar per operator</div>
                            </div>
                            <div class="flex gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Masuk ${formatCurrency(totalMasuk)}</span>
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Keluar ${formatCurrency(totalKeluar)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/70 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nama Operator</th>
                                    <th class="px-5 py-3">Masuk</th>
                                    <th class="px-5 py-3 text-right">Keluar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${operators
                                    .map(
                                        (op) => `
                                                                    <tr class="hover:bg-gray-50/70 transition">
                                                                        <td class="px-5 py-4 font-bold text-gray-900">${op.name || "Unknown"}</td>
                                                                        <td class="px-5 py-4 font-semibold text-emerald-600">${formatCurrency(op.masuk || 0)}</td>
                                                                        <td class="px-5 py-4 text-right font-semibold text-rose-500">${formatCurrency(op.keluar || 0)}</td>
                                                                    </tr>
                                                                `,
                                    )
                                    .join("")}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
}

function getMonthlyTransactionLabel(jenis) {
    switch (jenis) {
        case "penjualan":
            return "Penjualan Toko";
        case "penjualan_online":
            return "Penjualan Online";
        case "pembelian":
            return "Pembelian";
        case "transfer":
            return "Transfer";
        case "retur":
            return "Retur";
        case "rugi":
            return "Produk Rugi";
        default:
            return jenis || "-";
    }
}

function isMonthlyTransactionNegative(jenis) {
    return ["pembelian", "rugi"].includes(jenis);
}

function renderMonthlyTransactions(daily) {
    const list = document.getElementById("monthly-transaction-list");
    if (!list) return;

    if (daily.length === 0) {
        list.innerHTML =
            '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data transaksi bulan ini</div>';
        return;
    }

    const grouped = {
        penjualan: [],
        penjualan_online: [],
        pembelian: [],
        transfer: [],
        retur: [],
        rugi: [],
    };

    daily.forEach((row) => {
        if (Object.prototype.hasOwnProperty.call(grouped, row.jenis)) {
            grouped[row.jenis].push(row);
        }
    });

    const order = [
        "penjualan",
        "penjualan_online",
        "pembelian",
        "transfer",
        "retur",
        "rugi",
    ];

    list.innerHTML = order
        .map((jenis) => {
            const rows = grouped[jenis] || [];
            if (!rows.length) return "";

            const label = getMonthlyTransactionLabel(jenis);
            const showLaba = ["penjualan", "penjualan_online"].includes(jenis);
            const total = rows.reduce(
                (sum, row) => sum + Number(row.total || 0),
                0,
            );
            const laba = rows.reduce(
                (sum, row) => sum + Number(row.laba || 0),
                0,
            );
            const freq = rows.reduce(
                (sum, row) => sum + Number(row.frekuensi || 0),
                0,
            );
            const negative = isMonthlyTransactionNegative(jenis);

            return `
                        <details class="group overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm mb-4" ${showLaba ? "open" : ""}>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5">
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900">${label}</div>
                                    <div class="mt-1 text-xs text-gray-500">${rows.length} entri harian</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold ${negative ? "text-rose-600" : "text-gray-900"}">${negative ? "-" : ""}${formatCurrency(total)}</div>
                                    ${showLaba ? `<div class="text-xs font-semibold text-emerald-600">Laba ${formatCurrency(laba)}</div>` : ""}
                                    <div class="text-xs text-gray-500">${freq} trx</div>
                                </div>
                            </summary>
                            <div class="border-t border-gray-100 px-5 py-5">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm">
                                        <thead class="text-xs uppercase tracking-wide text-gray-500">
                                            <tr>
                                                <th class="py-3 pr-4">Tanggal</th>
                                                <th class="py-3 pr-4">Total</th>
                                                ${showLaba ? '<th class="py-3 pr-4">Laba</th>' : ""}
                                                <th class="py-3 text-right">Frekuensi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            ${rows
                                                .map((row) => {
                                                    const tanggal = row.tanggal
                                                        ? new Date(
                                                              row.tanggal,
                                                          ).toLocaleDateString(
                                                              "id-ID",
                                                              {
                                                                  day: "2-digit",
                                                                  month: "short",
                                                              },
                                                          )
                                                        : "-";
                                                    const rowTotal = Number(
                                                        row.total || 0,
                                                    );
                                                    const rowLaba = Number(
                                                        row.laba || 0,
                                                    );
                                                    const rowFreq = Number(
                                                        row.frekuensi || 0,
                                                    );

                                                    const totalClass = negative
                                                        ? "text-rose-600"
                                                        : "text-gray-900";
                                                    const labaCell = showLaba
                                                        ? '<td class="py-3 pr-4 font-semibold text-emerald-600">' +
                                                          formatCurrency(
                                                              rowLaba,
                                                          ) +
                                                          "</td>"
                                                        : "";

                                                    return (
                                                        "<tr>" +
                                                        '<td class="py-4 pr-4 font-medium text-gray-900">' +
                                                        tanggal +
                                                        "</td>" +
                                                        '<td class="py-4 pr-4 font-semibold ' +
                                                        totalClass +
                                                        '">' +
                                                        (negative ? "-" : "") +
                                                        formatCurrency(
                                                            rowTotal,
                                                        ) +
                                                        "</td>" +
                                                        labaCell +
                                                        '<td class="py-4 text-right text-gray-600">' +
                                                        rowFreq +
                                                        " trx</td>" +
                                                        "</tr>"
                                                    );
                                                })
                                                .join("")}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    `;
        })
        .filter(Boolean)
        .join("");
}

async function fetchOperatorData(storeId, date) {
    try {
        const response = await fetch(
            `/laporan/api/daily/operators?store_id=${storeId}&date=${date}`,
        );
        if (!response.ok) throw new Error("Gagal fetch data operator");

        const data = await response.json();
        renderDailyOperatorCards(data.operators || []);
    } catch (error) {
        console.error("Error:", error);
    }
}

function selectStore(event) {
    event.preventDefault();
    event.stopPropagation();

    const storeId = event.currentTarget.dataset.storeId || "";
    document.getElementById("store-id-hidden").value = storeId;
    updateStoreLabel(storeId);

    // Update active state in outlet dropdown
    document.querySelectorAll(".outlet-item").forEach((item) => {
        item.classList.remove("active");
    });
    event.currentTarget.classList.add("active");

    document
        .querySelectorAll(".dropdown-content")
        .forEach((dropdown) => dropdown.classList.remove("show"));
    loadActiveTabData();
}

window.addEventListener("click", function (event) {
    if (!event.target.closest(".dropdown")) {
        document
            .querySelectorAll(".dropdown-content")
            .forEach((dropdown) => dropdown.classList.remove("show"));
    }
});

// Event listeners (guarded: only attach if element exists)
const dateSelectorEl = document.getElementById("date-selector");
if (dateSelectorEl) dateSelectorEl.addEventListener("change", fetchDailyData);
const monthSelectorEl = document.getElementById("month-selector");
if (monthSelectorEl)
    monthSelectorEl.addEventListener("change", fetchMonthlyData);
const yearSelectorEl = document.getElementById("year-selector");
if (yearSelectorEl) yearSelectorEl.addEventListener("change", fetchAnnualData);

// Initialize active outlet on page load
window.addEventListener("load", function () {
    const currentStoreId =
        document.getElementById("store-id-hidden").value || "";

    // Update UI to show active outlet
    document.querySelectorAll(".outlet-item").forEach((item) => {
        const itemStoreId = item.dataset.storeId || "";
        if (itemStoreId === currentStoreId) {
            item.classList.add("active");
        } else {
            item.classList.remove("active");
        }
    });

    // Update store label display
    updateStoreLabel(currentStoreId);
});

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
    window.laporanActiveTab = "harian";
    updateStoreLabel(document.getElementById("store-id-hidden").value);
    loadActiveTabData();

    // Initialize ApexCharts for monthly/yearly trends
    const options = {
        series: [
            {
                name: "Omset",
                type: "area",
                data: [31, 40, 28, 51, 42, 109, 100],
            },
            {
                name: "Frekuensi",
                type: "line",
                data: [11, 32, 45, 32, 34, 52, 41],
            },
        ],
        chart: {
            height: 350,
            type: "line",
            toolbar: {
                show: false,
            },
            fontFamily: "Plus Jakarta Sans, sans-serif",
        },
        colors: ["#3b82f6", "#f59e0b"],
        stroke: {
            curve: "smooth",
            width: [0, 3],
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
            },
        },
        dataLabels: {
            enabled: false,
        },
        yaxis: [
            {
                title: {
                    text: "Nominal Omset",
                    style: {
                        color: "#3b82f6",
                    },
                },
                labels: {
                    formatter: (val) => "Rp " + val.toLocaleString(),
                },
            },
            {
                opposite: true,
                title: {
                    text: "Jumlah Transaksi",
                    style: {
                        color: "#f59e0b",
                    },
                },
            },
        ],
        grid: {
            borderColor: "#f1f1f1",
        },
    };

    const chartContainer = document.querySelector("#chartTwins");
    if (chartContainer) {
        const chart = new ApexCharts(chartContainer, options);
        chart.render();
    }
});

// Expose handlers for Alpine expressions and inline onclick attributes in Blade.
window.fetchDailyData = fetchDailyData;
window.fetchMonthlyData = fetchMonthlyData;
window.fetchAnnualData = fetchAnnualData;
window.toggleDropdown = toggleDropdown;
window.selectStore = selectStore;
window.applyCalendarFilter = applyCalendarFilter;
window.downloadLaporanExport = downloadLaporanExport;
window.resolveMonthlyFinancialValues = resolveMonthlyFinancialValues;
