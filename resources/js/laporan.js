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
        summaryData.laba_bersih ?? labaKotor + pemasukan - pengeluaran,
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
        
        // Manual handle for rugi to keep it red without negative sign
        const rugiEl = document.getElementById("monthly-rugi-value");
        if (rugiEl) {
            rugiEl.textContent = formatCurrency(Math.abs(Number(rugi) || 0));
            rugiEl.classList.remove("text-current", "text-red-500");
            rugiEl.classList.add("text-rose-600");
        }

        // Toggle Surplus / Defisit badge
        const mSurplus = document.getElementById("monthly-surplus-badge");
        const mDefisit = document.getElementById("monthly-defisit-badge");
        const mBadge   = document.getElementById("monthly-laba-bersih-badge");
        if (mSurplus && mDefisit && mBadge) {
            mSurplus.classList.toggle("hidden", labaBersih < 0);
            mDefisit.classList.toggle("hidden", labaBersih >= 0);
            mBadge.classList.remove("text-gray-900", "text-emerald-600", "text-rose-600");
            mBadge.classList.add(labaBersih >= 0 ? "text-emerald-600" : "text-rose-600");
        }

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
        const annualLabaBersih = Number(summaryData.laba_bersih || 0);

        setTextIfExists("annual-offline-omset-value", formatCurrency(offlineOmset));
        setTextIfExists("annual-online-omset-value", formatCurrency(onlineOmset));
        setTextIfExists("annual-total-omset-value", formatCurrency(totalOmset));
        setTextIfExists("annual-laba-kotor-value", formatCurrency(summaryData.laba_kotor || 0));
        setTextIfExists("annual-hpp-value", formatCurrency(summaryData.hpp || 0));
        setTextIfExists("annual-rugi-value", formatCurrency(summaryData.rugi || 0));
        setTextIfExists("annual-pemasukan-value", formatCurrency(summaryData.pemasukan || 0));
        setTextIfExists("annual-pengeluaran-value", formatCurrency(summaryData.pengeluaran || 0));

        // Laba Bersih with color
        const annualLBEl = document.getElementById("annual-laba-bersih-value");
        if (annualLBEl) {
            annualLBEl.textContent = (annualLabaBersih < 0 ? "-" : "") + formatCurrency(Math.abs(annualLabaBersih));
            annualLBEl.classList.remove("text-gray-900", "text-emerald-600", "text-rose-600");
            annualLBEl.classList.add(annualLabaBersih >= 0 ? "text-emerald-600" : "text-rose-600");
        }

        // Toggle Surplus / Defisit badge
        const aSurplus = document.getElementById("annual-surplus-badge");
        const aDefisit = document.getElementById("annual-defisit-badge");
        if (aSurplus) aSurplus.classList.toggle("hidden", annualLabaBersih < 0);
        if (aDefisit) aDefisit.classList.toggle("hidden", annualLabaBersih >= 0);

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
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Masuk ${formatCurrency(Math.abs(totalMasuk))}</span>
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Keluar ${formatCurrency(Math.abs(totalKeluar))}</span>
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
                                        <td class="px-5 py-4 font-semibold text-emerald-600">${formatCurrency(Math.abs(op.masuk || 0))}</td>
                                        <td class="px-5 py-4 text-right font-semibold text-rose-500">${formatCurrency(Math.abs(op.keluar || 0))}</td>
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

            const aChartId = `annual-chart-${jenis}`;

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
    const icon = document.getElementById("laporan-store-icon");
    if (icon) {
        if (storeId) icon.classList.add("text-primary-blue");
        else icon.classList.remove("text-primary-blue");
    }

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

    if (activeTab === "performa") {
        fetchPerformaToko();
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
    const icon = document.getElementById("laporan-calendar-icon");
    if (icon) icon.classList.add("text-primary-blue");

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
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Masuk ${formatCurrency(Math.abs(totalMasuk))}</span>
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Keluar ${formatCurrency(Math.abs(totalKeluar))}</span>
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
                                                                        <td class="px-5 py-4 font-semibold text-emerald-600">${formatCurrency(Math.abs(op.masuk || 0))}</td>
                                                                        <td class="px-5 py-4 text-right font-semibold text-rose-500">${formatCurrency(Math.abs(op.keluar || 0))}</td>
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

// ─── Chart.js line chart for Monthly/Yearly transaction accordion ──────────
const _laporanChartInstances = {};

function renderTransactionLineChart(canvasId, rawData, isYearly) {
    if (_laporanChartInstances[canvasId]) {
        _laporanChartInstances[canvasId].destroy();
        delete _laporanChartInstances[canvasId];
    }
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const { labels, totals, freqs } = rawData;
    const maxTotal = Math.max(...totals, 1);
    const maxFreq  = Math.max(...freqs, 1);
    const normFreqs = freqs.map(f => (f / maxFreq) * maxTotal);
    const ctx = canvas.getContext("2d");
    _laporanChartInstances[canvasId] = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Omset",
                    data: totals,
                    borderColor: "#3B82F6",
                    backgroundColor: "rgba(59,130,246,0.1)",
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                },
                {
                    label: "Frekuensi",
                    data: normFreqs,
                    borderColor: "#F97316",
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    pointRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "top" },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.datasetIndex === 0) {
                                return "Omset: " + formatCurrency(ctx.raw);
                            }
                            const actual = Math.round((ctx.raw / maxTotal) * maxFreq);
                            return "Frekuensi: " + actual + " trx";
                        },
                    },
                },
            },
            scales: { y: { beginAtZero: true } },
        },
    });
}

function buildTransactionChartData(rows, isYearly) {
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    const labels = [], totals = [], freqs = [];
    if (isYearly) {
        const currentYear = new Date().getFullYear();
        const yearSelector = document.getElementById("year-selector");
        const selYear = yearSelector ? parseInt(yearSelector.value) : currentYear;
        const maxMonth = selYear === currentYear ? new Date().getMonth() + 1 : 12;
        const dataMap = {};
        rows.forEach(r => { dataMap[r.bulan] = r; });
        for (let m = 1; m <= maxMonth; m++) {
            labels.push(monthNames[m - 1]);
            totals.push(Number(dataMap[m]?.total || 0));
            freqs.push(Number(dataMap[m]?.frekuensi || 0));
        }
    } else {
        const monthSelector = document.getElementById("month-selector");
        let maxDay = 31;
        if (monthSelector && monthSelector.value) {
            const [y, m] = monthSelector.value.split("-").map(Number);
            maxDay = new Date(y, m, 0).getDate();
        }
        const dataMap = {};
        rows.forEach(r => {
            const day = r.tanggal ? String(new Date(r.tanggal).getDate()).padStart(2, "0") : null;
            if (day) dataMap[day] = r;
        });
        for (let d = 1; d <= maxDay; d++) {
            const key = String(d).padStart(2, "0");
            labels.push(key);
            totals.push(Number(dataMap[key]?.total || 0));
            freqs.push(Number(dataMap[key]?.frekuensi || 0));
        }
    }
    return { labels, totals, freqs };
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

            const chartId = `monthly-chart-${jenis}`;
            const chartData = showLaba ? buildTransactionChartData(rows, false) : null;

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
                                                        ? new Date(row.tanggal).toLocaleDateString("id-ID", { day: "2-digit", month: "short" })
                                                        : "-";
                                                    const rowTotal = Number(row.total || 0);
                                                    const rowLaba = Number(row.laba || 0);
                                                    const rowFreq = Number(row.frekuensi || 0);
                                                    const totalClass = negative ? "text-rose-600" : "text-gray-900";
                                                    const labaCell = showLaba
                                                        ? `<td class="py-3 pr-4 font-semibold text-emerald-600">${formatCurrency(rowLaba)}</td>`
                                                        : "";
                                                    return `<tr><td class="py-4 pr-4 font-medium text-gray-900">${tanggal}</td><td class="py-4 pr-4 font-semibold ${totalClass}">${negative ? "-" : ""}${formatCurrency(rowTotal)}</td>${labaCell}<td class="py-4 text-right text-gray-600">${rowFreq} trx</td></tr>`;
                                                })
                                                .join("")}
                                        </tbody>
                                    </table>
                                </div>
                                ${showLaba ? `
                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <button onclick="toggleMonthlyChart('${chartId}')" class="flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                                        <span>📊 Lihat Grafik Performa</span>
                                    </button>
                                    <div id="${chartId}" class="hidden mt-4" style="max-height:280px">
                                        <canvas id="canvas-${chartId}"></canvas>
                                    </div>
                                </div>` : ""}
                            </div>
                        </details>
                    `;
        })
        .filter(Boolean)
        .join("");

    // After rendering, attach chart data to window for lazy init
    if (typeof window._monthlyChartData === 'undefined') window._monthlyChartData = {};
    ['penjualan', 'penjualan_online'].forEach(jenis => {
        const rows = grouped[jenis] || [];
        if (rows.length) {
            const cid = `monthly-chart-${jenis}`;
            window._monthlyChartData[cid] = buildTransactionChartData(rows, false);
        }
    });
}

function toggleMonthlyChart(chartId) {
    const wrapper = document.getElementById(chartId);
    if (!wrapper) return;
    const wasHidden = wrapper.classList.contains("hidden");
    wrapper.classList.toggle("hidden", !wasHidden);
    if (wasHidden && window._monthlyChartData && window._monthlyChartData[chartId]) {
        renderTransactionLineChart(`canvas-${chartId}`, window._monthlyChartData[chartId], false);
    }
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
window.toggleMonthlyChart = typeof toggleMonthlyChart !== 'undefined' ? toggleMonthlyChart : () => {};

// ─── Performa Toko Module ────────────────────────────────────────────────────

let _performaChartInstance = null;
let _performaStores = [];
let _performaCurrentMetric = 'laba_bersih';

const PERFORMA_COLORS = [
    '#6366F1', '#8B5CF6', '#3B82F6', '#06B6D4', '#10B981',
    '#F59E0B', '#EF4444', '#EC4899', '#14B8A6', '#F97316',
];

async function fetchPerformaToko() {
    const yearEl = document.getElementById('year-selector');
    const year   = yearEl ? yearEl.value || new Date().getFullYear() : new Date().getFullYear();
    const url    = (window.laporanConfig && window.laporanConfig.performaTokoUrl)
        ? `${window.laporanConfig.performaTokoUrl}?year=${year}`
        : `/laporan/api/performa-toko?year=${year}`;

    // Show loading state
    const loading  = document.getElementById('performa-chart-loading');
    const content  = document.getElementById('performa-chart-content');
    const empty    = document.getElementById('performa-chart-empty');
    const storeList = document.getElementById('performa-store-list');

    if (loading)  { loading.classList.remove('hidden');  }
    if (content)  { content.classList.add('hidden');     }
    if (empty)    { empty.classList.add('hidden');       }
    if (storeList) {
        storeList.innerHTML = Array.from({length: 3}, () => `
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-gray-200 flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="h-4 w-32 rounded bg-gray-200"></div>
                        <div class="mt-2 h-3 w-48 rounded bg-gray-100"></div>
                    </div>
                    <div class="h-4 w-24 rounded bg-gray-200"></div>
                </div>
            </div>
        `).join('');
    }

    try {
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Gagal fetch performa toko');
        const data = await resp.json();

        _performaStores = data.stores || [];
        const chartData = data.chart_data || [];

        if (!chartData.length) {
            if (loading) loading.classList.add('hidden');
            if (empty)   empty.classList.remove('hidden');
            if (storeList) storeList.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500 text-center">Tidak ada data toko untuk tahun ini</div>';
            return;
        }

        renderPerformaChart(chartData, _performaCurrentMetric);
        renderPerformaStoreList(_performaStores);

        const countEl = document.getElementById('performa-store-count');
        if (countEl) countEl.textContent = `${_performaStores.length} Toko`;

    } catch (err) {
        console.error('Error performa toko:', err);
        if (loading) loading.classList.add('hidden');
        if (storeList) storeList.innerHTML = `<div class="rounded-2xl border border-rose-100 bg-rose-50 p-6 text-sm text-rose-700 text-center">Gagal memuat data: ${err.message}</div>`;
    }
}

function renderPerformaChart(chartData, metric) {
    const loading = document.getElementById('performa-chart-loading');
    const content = document.getElementById('performa-chart-content');
    const empty   = document.getElementById('performa-chart-empty');

    const labels = chartData.map(d => d.nama);
    const values = chartData.map(d => Number(d[metric] || 0));
    const positiveCount = values.filter(v => v > 0).length;

    if (loading) loading.classList.add('hidden');

    if (positiveCount === 0) {
        if (empty) empty.classList.remove('hidden');
        if (content) content.classList.add('hidden');
        return;
    }

    if (empty) empty.classList.add('hidden');
    if (content) content.classList.remove('hidden');

    const total = values.reduce((s, v) => s + Math.max(v, 0), 0);
    const totalEl = document.getElementById('performa-chart-total');
    if (totalEl) totalEl.textContent = formatCurrency(total);

    const canvas = document.getElementById('performa-doughnut-chart');
    if (!canvas) return;

    if (_performaChartInstance) {
        _performaChartInstance.destroy();
        _performaChartInstance = null;
    }

    const bgColors = labels.map((_, i) => PERFORMA_COLORS[i % PERFORMA_COLORS.length]);

    _performaChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values.map(v => Math.max(v, 0)),
                backgroundColor: bgColors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${formatCurrency(ctx.raw)}`,
                    },
                },
            },
            onClick: (evt, elements) => {
                if (!elements.length) return;
                const idx = elements[0].index;
                const name = labels[idx];
                const val  = values[idx];
                const infoBox  = document.getElementById('performa-info-box');
                const infoName = document.getElementById('performa-info-name');
                const infoVal  = document.getElementById('performa-info-val');
                if (infoBox && infoName && infoVal) {
                    infoName.textContent = name;
                    infoVal.textContent  = formatCurrency(val);
                    infoBox.classList.remove('hidden');
                }
            },
        },
    });

    // Render legend
    const legendEl = document.getElementById('performa-chart-legend');
    if (legendEl) {
        legendEl.innerHTML = labels.map((label, i) => `
            <div class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 hover:bg-gray-50 transition cursor-pointer"
                 onclick="highlightPerformaSegment(${i})">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="h-3 w-3 rounded-full flex-shrink-0" style="background:${bgColors[i]}"></span>
                    <span class="text-sm font-medium text-gray-700 truncate">${label}</span>
                </div>
                <span class="text-sm font-bold text-gray-900 whitespace-nowrap">${formatCurrency(values[i])}</span>
            </div>
        `).join('');
    }
}

function highlightPerformaSegment(index) {
    if (!_performaChartInstance) return;
    _performaChartInstance.setDatasetVisibility(0, true);
    _performaChartInstance.update();
    const infoBox  = document.getElementById('performa-info-box');
    const infoName = document.getElementById('performa-info-name');
    const infoVal  = document.getElementById('performa-info-val');
    if (infoBox && infoName && infoVal && _performaStores[index]) {
        const store  = _performaStores[index];
        const metric = _performaCurrentMetric;
        infoName.textContent = store.nama;
        infoVal.textContent  = formatCurrency(store[`total_${metric}`] || 0);
        infoBox.classList.remove('hidden');
    }
}

function updatePerformaChart(metric) {
    _performaCurrentMetric = metric;
    if (!_performaStores.length) return;
    const chartData = _performaStores.map(s => ({
        nama:        s.nama,
        laba_bersih: s.total_laba_bersih,
        laba_kotor:  s.total_laba_kotor,
        omset:       s.total_omset,
    }));
    renderPerformaChart(chartData, metric);
}

function renderPerformaStoreList(stores) {
    const list = document.getElementById('performa-store-list');
    if (!list) return;

    if (!stores.length) {
        list.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500 text-center">Tidak ada data toko</div>';
        return;
    }

    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    list.innerHTML = stores.map((store, i) => {
        const color = PERFORMA_COLORS[i % PERFORMA_COLORS.length];
        const rank  = i + 1;
        const isPositive = store.total_laba_bersih >= 0;
        const badge = isPositive
            ? `<span class="text-xs font-bold rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">Surplus</span>`
            : `<span class="text-xs font-bold rounded-full px-2 py-0.5 bg-rose-100 text-rose-700">Defisit</span>`;

        const monthRows = (store.months || []).map(m => `
            <tr class="hover:bg-gray-50/70 transition">
                <td class="px-4 py-3 font-medium text-gray-700">${monthNames[(m.bulan || 1) - 1]}</td>
                <td class="px-4 py-3 font-semibold text-gray-900">${formatCurrency(m.omset || 0)}</td>
                <td class="px-4 py-3 font-semibold ${m.laba >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${formatCurrency(m.laba || 0)}</td>
                <td class="px-4 py-3 text-right font-semibold text-rose-500">${formatCurrency(m.rugi || 0)}</td>
            </tr>
        `).join('');

        return `
            <details class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden group">
                <summary class="flex cursor-pointer list-none items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl font-bold text-white text-sm flex-shrink-0"
                         style="background:${color}">#${rank}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-gray-900 truncate">${store.nama}</span>
                            ${badge}
                        </div>
                        <div class="mt-0.5 text-xs text-gray-500">Omset: ${formatCurrency(store.total_omset)}</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-bold ${isPositive ? 'text-emerald-600' : 'text-rose-600'}">${formatCurrency(store.total_laba_bersih)}</div>
                        <div class="text-xs text-gray-500">Laba Bersih</div>
                    </div>
                    <iconify-icon icon="solar:alt-arrow-down-bold" class="text-gray-400 flex-shrink-0 group-open:rotate-180 transition-transform"></iconify-icon>
                </summary>

                <div class="border-t border-gray-100 px-5 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-3">
                            <div class="text-xs font-bold text-blue-600 uppercase tracking-wider">Laba Kotor</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">${formatCurrency(store.total_laba_kotor)}</div>
                        </div>
                        <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                            <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Pemasukan</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">${formatCurrency(store.total_pemasukan)}</div>
                        </div>
                        <div class="rounded-xl bg-rose-50 border border-rose-100 p-3">
                            <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Pengeluaran</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">${formatCurrency(store.total_pengeluaran)}</div>
                        </div>
                        <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                            <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Rugi</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">${formatCurrency(store.total_rugi)}</div>
                        </div>
                    </div>

                    ${monthRows ? `
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Bulan</th>
                                    <th class="px-4 py-3">Omset</th>
                                    <th class="px-4 py-3">Laba Kotor</th>
                                    <th class="px-4 py-3 text-right">Rugi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">${monthRows}</tbody>
                        </table>
                    </div>` : ''}
                </div>
            </details>
        `;
    }).join('');
}

window.fetchPerformaToko = fetchPerformaToko;
window.updatePerformaChart = updatePerformaChart;
window.highlightPerformaSegment = highlightPerformaSegment;
window.toggleMonthlyChart = typeof toggleMonthlyChart !== 'undefined' ? toggleMonthlyChart : () => {};
