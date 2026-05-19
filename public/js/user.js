// Safe LocalStorage Wrapper - Mencegah SecurityError di browser/auditing tool yang menonaktifkan cookies/localStorage
const safeLocalStorage = {
    getItem(key) {
        try {
            return window.localStorage ? window.localStorage.getItem(key) : null;
        } catch (e) {
            return null;
        }
    },
    setItem(key, value) {
        try {
            if (window.localStorage) window.localStorage.setItem(key, value);
        } catch (e) {}
    },
    removeItem(key) {
        try {
            if (window.localStorage) window.localStorage.removeItem(key);
        } catch (e) {}
    }
};

function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('show');
}

window.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    const btn = document.querySelector('.user-icon-btn');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});

const body = document.getElementById('body');

window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

const cartItemsContainer = document.getElementById('cartItems');
const productGrid = document.getElementById('productGrid');
const searchInput = document.getElementById('searchInput');
const mainContainer = document.getElementById('mainContainer');

const addressSections = document.querySelectorAll('.address-section');
const orderSections = document.querySelectorAll('.order-section');
const discountSections = document.querySelectorAll('.discount-section');

const homePage = document.getElementById('homePage');
const historyPage = document.getElementById('historyPage');
const historyList = document.getElementById('historyList');

// Helper untuk format Rupiah
function formatRupiah(amount) {
    return "Rp " + Math.floor(amount).toLocaleString('id-ID');
}

function escapeHtml(text) {
    const safeText = String(text ?? '');
    return safeText
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Sementara: ongkir dihitung dari jarak pengantaran (500 rupiah per km).
function calculateTemporaryShippingFee(distanceKm) {
    const safeDistanceKm = Number.isFinite(distanceKm) ? Math.max(0, distanceKm) : 0;
    return Math.ceil(safeDistanceKm * 500);
}

const products = JSON.parse(document.getElementById('products-data').textContent);

let cart = [];
let historyData = [];
let discountPercent = 0;
let appliedPromo = null;
const isAuthenticated = document.querySelector('meta[name="auth-check"]').content === 'true';
const loginUrl = document.querySelector('meta[name="login-url"]').content;
const storeHours = document.querySelector('meta[name="store-hours"]').content || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const deliveryAddressStoreUrl = document.querySelector('meta[name="delivery-address-store-url"]').content;
const checkoutTokenUrl = document.querySelector('meta[name="checkout-token-url"]').content;
const midtransEnabled = document.querySelector('meta[name="midtrans-enabled"]').content === 'true';
const persistedDeliveryPreference = JSON.parse(document.querySelector('meta[name="persisted-delivery-preference"]').content);

function parseStoreHours(hoursText) {
    const match = (hoursText || '').match(/(\d{1,2})[.:](\d{2})\s*-\s*(\d{1,2})[.:](\d{2})/);
    if (!match) return null;

    const openHour = Number(match[1]);
    const openMinute = Number(match[2]);
    const closeHour = Number(match[3]);
    const closeMinute = Number(match[4]);

    const validOpen = Number.isInteger(openHour) && Number.isInteger(openMinute) && openHour >= 0 && openHour <=
        23 && openMinute >= 0 && openMinute <= 59;
    const validClose = Number.isInteger(closeHour) && Number.isInteger(closeMinute) && closeHour >= 0 &&
        closeHour <= 23 && closeMinute >= 0 && closeMinute <= 59;

    if (!validOpen || !validClose) return null;

    return {
        openMinutes: (openHour * 60) + openMinute,
        closeMinutes: (closeHour * 60) + closeMinute,
    };
}

function isStoreClosedNow() {
    const parsed = parseStoreHours(storeHours);
    if (!parsed) return false;

    const now = new Date();
    const nowMinutes = (now.getHours() * 60) + now.getMinutes();
    const {
        openMinutes,
        closeMinutes
    } = parsed;

    let isOpen = false;
    if (openMinutes === closeMinutes) {
        isOpen = true;
    } else if (openMinutes < closeMinutes) {
        isOpen = nowMinutes >= openMinutes && nowMinutes < closeMinutes;
    } else {
        // Jadwal lintas tengah malam, contoh: 20.00 - 02.00
        isOpen = nowMinutes >= openMinutes || nowMinutes < closeMinutes;
    }

    return !isOpen;
}

function showStoreClosedNotification() {
    const scheduleText = storeHours ? `Jam operasional: ${storeHours}` :
        'Silakan cek kembali jam operasional outlet.';
    Swal.fire({
        title: 'Toko Sedang Tutup',
        text: `Checkout belum tersedia saat toko tutup. ${scheduleText}`,
        icon: 'info',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        confirmButtonColor: 'var(--orange-brand)',
        confirmButtonText: 'Mengerti'
    });
}

function applyCheckoutAvailability() {
    const closed = isStoreClosedNow();
    const checkoutButtons = document.querySelectorAll('.checkout-btn');

    checkoutButtons.forEach((btn) => {
        if (!btn.dataset.originalBackground) btn.dataset.originalBackground = btn.style.background || '';
        if (!btn.dataset.originalCursor) btn.dataset.originalCursor = btn.style.cursor || '';
        if (!btn.dataset.originalOpacity) btn.dataset.originalOpacity = btn.style.opacity || '';

        if (closed) {
            btn.style.background = '#9ca3af';
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.85';
            btn.setAttribute('aria-disabled', 'true');
            btn.setAttribute('title', 'Toko sedang tutup');
        } else {
            btn.style.background = btn.dataset.originalBackground;
            btn.style.cursor = btn.dataset.originalCursor;
            btn.style.opacity = btn.dataset.originalOpacity;
            btn.setAttribute('aria-disabled', 'false');
            btn.removeAttribute('title');
        }
    });
}

function savePersistence() {
    if (!isAuthenticated) return;
    safeLocalStorage.setItem('twins_cart', JSON.stringify(cart));
    safeLocalStorage.setItem('twins_history', JSON.stringify(historyData));
    if (window.deliveryDetailAddress) {
        safeLocalStorage.setItem('twins_delivery_detail', window.deliveryDetailAddress);
    }
}

function loadPersistence() {
    if (!isAuthenticated) {
        // Bersihkan jika tidak login (untuk keamanan)
        safeLocalStorage.removeItem('twins_cart');
        safeLocalStorage.removeItem('twins_history');
        safeLocalStorage.removeItem('twins_delivery_detail');
        return;
    }
    const savedCart = safeLocalStorage.getItem('twins_cart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {}
    }
    const savedHistory = safeLocalStorage.getItem('twins_history');
    if (savedHistory) {
        try {
            historyData = JSON.parse(savedHistory);
        } catch (e) {}
    }
    const savedDetail = safeLocalStorage.getItem('twins_delivery_detail');
    if (savedDetail) window.deliveryDetailAddress = savedDetail;
}

loadPersistence();

function savePersistedDeliveryAddress() {
    if (!isAuthenticated) return Promise.resolve(true);

    const safeAddress = (deliveryAddress || '').trim();
    const hasCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number.isFinite(
        deliveryCoordinates.lng));

    if (!safeAddress) return Promise.resolve(false);

    return fetch(deliveryAddressStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                address: safeAddress,
                recipient_name: deliveryContactName,
                recipient_phone: deliveryPhone,
                coordinates: hasCoordinates ? {
                    lat: deliveryCoordinates.lat,
                    lng: deliveryCoordinates.lng
                } : null
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`save_delivery_failed_${response.status}`);
            }
            return true;
        })
        .catch(() => {
            return false;
        });
}

// Toggle Panel Filter
function toggleFilterPanel() {
    const panel = document.getElementById('filterPanel');
    panel.classList.toggle('hidden');
}

// Toggle Semua Kategori
function toggleAllCategories(checkbox) {
    if (checkbox.checked) {
        // Jika 'Semua' dicentang, hapus semua centang kategori lain
        const catChecks = document.querySelectorAll('.cat-check');
        catChecks.forEach(c => c.checked = false);
    }
    // Jangan panggil applyFilters di sini agar user bisa pilih dulu
}

// Event listener untuk kategori satuan
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('cat-check')) {
        if (e.target.checked) {
            // Jika kategori satuan dicentang, hapus centang 'Semua'
            document.getElementById('check-all').checked = false;
        } else {
            // Jika semua kategori satuan tidak dicentang, centang kembali 'Semua'
            const anyChecked = document.querySelectorAll('.cat-check:checked').length > 0;
            if (!anyChecked) document.getElementById('check-all').checked = true;
        }
    }
});

// Jalankan Filter & Sort
function applyFilters() {
    renderProducts();
    renderActiveFilters();

    // Tutup panel secara paksa
    const panel = document.getElementById('filterPanel');
    panel.classList.add('hidden');
}

// Tampilkan Badge Filter Aktif
function renderActiveFilters() {
    const container = document.getElementById('activeFilters');
    container.innerHTML = '';

    const isAllChecked = document.getElementById('check-all').checked;
    const priceSort = document.getElementById('priceSort');

    // 1. Tambah Badge Harga (Jika tidak default)
    if (priceSort.value !== 'default') {
        const priceText = priceSort.options[priceSort.selectedIndex].text;
        const priceBadge = document.createElement('div');
        priceBadge.className = 'filter-badge';
        priceBadge.style.borderColor = '#10b981'; // Beri warna hijau agar beda dengan kategori
        priceBadge.style.color = '#10b981';
        priceBadge.innerHTML = `
            <span>${priceText}</span>
            <div class="remove-btn" onclick="removePriceFilter()">✕</div>
        `;
        container.appendChild(priceBadge);
    }

    // 2. Tambah Badge Kategori
    if (!isAllChecked) {
        const checkedCats = document.querySelectorAll('.cat-check:checked');
        checkedCats.forEach(cb => {
            const badge = document.createElement('div');
            badge.className = 'filter-badge';
            badge.innerHTML = `
                <span>${cb.dataset.name}</span>
                <div class="remove-btn" onclick="removeFilterBadge('${cb.value}')">✕</div>
            `;
            container.appendChild(badge);
        });
    }
}

// Hapus Filter Harga lewat Badge
function removePriceFilter() {
    document.getElementById('priceSort').value = 'default';
    renderProducts();
    renderActiveFilters();
}

// Hapus Filter Kategori lewat Badge
function removeFilterBadge(catId) {
    const cb = document.querySelector(`.cat-check[value="${catId}"]`);
    if (cb) {
        cb.checked = false;

        // Jika setelah dihapus tidak ada lagi yang dicentang, balikkan ke 'Semua'
        const anyChecked = document.querySelectorAll('.cat-check:checked').length > 0;
        if (!anyChecked) {
            document.getElementById('check-all').checked = true;
        }

        renderProducts();
        renderActiveFilters();
    }
}
const outletAddress = document.querySelector('meta[name="outlet-address"]').content;
let deliveryAddress = (persistedDeliveryPreference && typeof persistedDeliveryPreference.address === 'string' &&
        persistedDeliveryPreference.address.trim()) ? persistedDeliveryPreference.address.trim() :
    document.querySelector('meta[name="outlet-address"]').content;
let deliveryCoordinates = (persistedDeliveryPreference && persistedDeliveryPreference.coordinates && Number
    .isFinite(persistedDeliveryPreference.coordinates.lat) && Number.isFinite(persistedDeliveryPreference
        .coordinates.lng)) ? {
    lat: Number(persistedDeliveryPreference.coordinates.lat),
    lng: Number(persistedDeliveryPreference.coordinates.lng)
} : null;
let deliveryContactName = document.querySelector('meta[name="user-name"]').content;
let deliveryPhone = document.querySelector('meta[name="user-phone"]').content;
let deliveryDistanceKm = 0;
let outletCoordinates = null;
let outletGeocodeTried = false;

function updateDeliveryAddressUI() {
    const safeAddress = (deliveryAddress || '').trim() || 'Alamat belum diisi';
    const hasCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number.isFinite(
        deliveryCoordinates.lng));

    document.querySelectorAll('.delivery-address-value').forEach(el => {
        el.textContent = safeAddress;
    });

    document.querySelectorAll('.delivery-address-note').forEach(el => {
        el.textContent = hasCoordinates ?
            `Dipilih dari peta (${deliveryCoordinates.lat.toFixed(6)}, ${deliveryCoordinates.lng.toFixed(6)}).` :
            'Alamat pengiriman default Anda.';
    });

    document.querySelectorAll('.delivery-contact-note').forEach(el => {
        const nameText = (deliveryContactName || '').trim() || '-';
        const phoneText = (deliveryPhone || '').trim() || '-';
        const detailText = (window.deliveryDetailAddress || '').trim();
        el.innerHTML =
            `Penerima: ${nameText} | No HP: ${phoneText}${detailText ? '<br><span style="color:var(--orange-brand); font-style:italic;">Detail: ' + detailText + '</span>' : ''}`;
    });
}

function calculateDistanceKmBetweenPoints(from, to) {
    const earthRadiusKm = 6371;
    const dLat = (to.lat - from.lat) * (Math.PI / 180);
    const dLng = (to.lng - from.lng) * (Math.PI / 180);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(from.lat * (Math.PI / 180)) * Math.cos(to.lat * (Math.PI / 180)) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return earthRadiusKm * c;
}

function resolveOutletCoordinatesFromAddress() {
    if (outletCoordinates) return Promise.resolve(outletCoordinates);
    
    // Performa Tinggi: Gunakan cache localStorage agar terhindar dari pemanggilan API lambat berulang kali
    const cacheKey = 'twins_outlet_coords_' + btoa(unescape(encodeURIComponent(outletAddress)));
    const cachedCoords = localStorage.getItem(cacheKey);
    if (cachedCoords) {
        try {
            outletCoordinates = JSON.parse(cachedCoords);
            return Promise.resolve(outletCoordinates);
        } catch (e) {}
    }

    if (outletGeocodeTried) return Promise.resolve(null);
    outletGeocodeTried = true;

    return fetch(
            `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=id&q=${encodeURIComponent(outletAddress)}`
        )
        .then(response => response.ok ? response.json() : [])
        .then(results => {
            if (!Array.isArray(results) || results.length === 0) return null;
            const first = results[0];
            const lat = Number(first.lat);
            const lng = Number(first.lon);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
            outletCoordinates = {
                lat,
                lng
            };
            try {
                localStorage.setItem(cacheKey, JSON.stringify(outletCoordinates));
            } catch (e) {}
            return outletCoordinates;
        })
        .catch(() => null);
}

function syncPersistedDeliveryDistance() {
    const hasDeliveryCoordinates = !!(deliveryCoordinates && Number.isFinite(deliveryCoordinates.lat) && Number
        .isFinite(deliveryCoordinates.lng));

    if (!hasDeliveryCoordinates) {
        deliveryDistanceKm = 0;
        renderCart();
        return;
    }

    resolveOutletCoordinatesFromAddress().then(outletLatLng => {
        if (!outletLatLng) {
            deliveryDistanceKm = 0;
            renderCart();
            return;
        }

        fetch(
                `https://router.project-osrm.org/route/v1/driving/${outletLatLng.lng},${outletLatLng.lat};${deliveryCoordinates.lng},${deliveryCoordinates.lat}?overview=false`
            )
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                const route = data && Array.isArray(data.routes) && data.routes.length > 0 ? data
                    .routes[0] : null;
                const routeDistanceKm = route ? Number(route.distance || 0) / 1000 : NaN;

                if (Number.isFinite(routeDistanceKm) && routeDistanceKm > 0) {
                    deliveryDistanceKm = routeDistanceKm;
                } else {
                    const straightDistance = calculateDistanceKmBetweenPoints(outletLatLng,
                        deliveryCoordinates);
                    deliveryDistanceKm = Number.isFinite(straightDistance) ? Math.max(0,
                        straightDistance) : 0;
                }

                renderCart();
            })
            .catch(() => {
                const straightDistance = calculateDistanceKmBetweenPoints(outletLatLng,
                    deliveryCoordinates);
                deliveryDistanceKm = Number.isFinite(straightDistance) ? Math.max(0,
                    straightDistance) : 0;
                renderCart();
            });
    });
}

function openAddressPopup(event) {
    if (event) event.preventDefault();

    let popupMap = null;
    let popupMarker = null;
    let outletMarker = null;
    let routeLine = null;
    let selectedLatLng = deliveryCoordinates ? {
        lat: deliveryCoordinates.lat,
        lng: deliveryCoordinates.lng
    } : null;
    let selectedDistanceKm = Number.isFinite(deliveryDistanceKm) ? Math.max(0, deliveryDistanceKm) : 0;
    let geocodeDebounceTimer = null;
    let geocodeRequestToken = 0;

    const popupHtml = `
        <div class="address-popup-wrap">
            <div class="address-popup-layout">
                <div class="address-popup-left">
                    <div class="route-tracking-card">
                        <p style="font-size:11px; letter-spacing:0.05em; color:var(--accent-purple); margin:0 0 6px 0; font-weight:800;">🛰️ LIVE ROUTE TRACKING</p>
                        <p style="font-size:13px; color:var(--text-color); margin:0; line-height:1.5; font-weight:500;" id="routeTrackingSummary">Menyiapkan rute dari outlet ke alamat tujuan...</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                        <div>
                            <label for="recipientNameInput">Nama Penerima</label>
                            <input type="text" id="recipientNameInput" placeholder="Contoh: Budi">
                        </div>
                        <div>
                            <label for="recipientPhoneInput">No HP / WhatsApp</label>
                            <input type="text" id="recipientPhoneInput" placeholder="0812...">
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label for="manualAddressInput">Alamat Utama (Pencarian/Geser Peta)</label>
                        <textarea id="manualAddressInput" rows="3" placeholder="Nama jalan, kecamatan, kota..."></textarea>
                    </div>

                    <div>
                        <label for="detailAddressInput">Detail / Catatan (Opsional)</label>
                        <input type="text" id="detailAddressInput" placeholder="Nomor rumah, warna pagar, atau instruksi khusus">
                    </div>
                </div>

                <div class="address-popup-right">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <p style="font-size:12px; margin:0; color:var(--sub-text); font-weight:500;">📍 Pilih titik lokasi tepat pada peta.</p>
                        <button type="button" id="useCurrentLocationBtn" style="background: var(--accent-purple); color: white; border: none; border-radius: 8px; padding: 6px 12px; font-size: 11px; cursor: pointer; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);">📍 Lokasi Saya</button>
                    </div>
                    <div id="addressMapCanvas"></div>
                    <div id="mapAddressResult" style="margin-top:10px; font-size:11px; color:var(--sub-text); line-height:1.5; font-style: italic;"></div>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: 'Ubah Alamat Pengiriman',
        html: popupHtml,
        showCancelButton: true,
        confirmButtonText: 'Simpan Lokasi',
        cancelButtonText: 'Batal',
        confirmButtonColor: 'var(--orange-brand)',
        width: window.innerWidth > 768 ? '950px' : '96vw',
        customClass: {
            popup: 'address-modal-custom'
        },
        didOpen: () => {
            const popup = Swal.getPopup();
            const htmlContainer = Swal.getHtmlContainer();
            const recipientNameInput = popup.querySelector('#recipientNameInput');
            const recipientPhoneInput = popup.querySelector('#recipientPhoneInput');
            const manualAddressInput = popup.querySelector('#manualAddressInput');
            const detailAddressInput = popup.querySelector('#detailAddressInput');
            const mapAddressResult = popup.querySelector('#mapAddressResult');
            const routeTrackingSummary = popup.querySelector('#routeTrackingSummary');
            const useCurrentLocationBtn = popup.querySelector('#useCurrentLocationBtn');

            if (htmlContainer) {
                htmlContainer.style.maxHeight = '72vh';
                htmlContainer.style.overflowY = 'auto';
                htmlContainer.style.paddingRight = '4px';
            }
            if (popup) {
                popup.style.maxHeight = '95vh';
            }

            recipientNameInput.value = (deliveryContactName || '').trim();
            recipientPhoneInput.value = (deliveryPhone || '').trim();
            manualAddressInput.value = (deliveryAddress || '').trim();
            detailAddressInput.value = (window.deliveryDetailAddress || '').trim();

            function renderMapResultText(text) {
                mapAddressResult.textContent = text || '';
            }

            function renderRouteTrackingText(text) {
                routeTrackingSummary.textContent = text || '';
            }

            function calculateDistanceKm(from, to) {
                const earthRadiusKm = 6371;
                const dLat = (to.lat - from.lat) * (Math.PI / 180);
                const dLng = (to.lng - from.lng) * (Math.PI / 180);
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(from.lat * (Math.PI / 180)) * Math.cos(to.lat * (Math.PI / 180)) *
                    Math.sin(dLng / 2) * Math.sin(dLng / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return earthRadiusKm * c;
            }

            function resolveOutletCoordinates() {
                if (outletCoordinates) return Promise.resolve(outletCoordinates);
                if (outletGeocodeTried) return Promise.resolve(null);

                outletGeocodeTried = true;

                return fetch(
                        `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=id&q=${encodeURIComponent(outletAddress)}`
                    )
                    .then(response => response.ok ? response.json() : [])
                    .then(results => {
                        if (!Array.isArray(results) || results.length === 0) return null;
                        const first = results[0];
                        const lat = Number(first.lat);
                        const lng = Number(first.lon);
                        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                        outletCoordinates = {
                            lat,
                            lng
                        };
                        return outletCoordinates;
                    })
                    .catch(() => null);
            }

            function updateRouteTracking() {
                if (!popupMap) return;

                if (routeLine) {
                    popupMap.removeLayer(routeLine);
                    routeLine = null;
                }

                resolveOutletCoordinates().then(outletLatLng => {
                    if (!outletLatLng) {
                        selectedDistanceKm = 0;
                        renderRouteTrackingText(
                            'Lokasi outlet belum ditemukan. Rute tidak dapat ditampilkan.');
                        return;
                    }

                    if (!outletMarker) {
                        outletMarker = L.circleMarker([outletLatLng.lat, outletLatLng.lng], {
                            radius: 6,
                            color: '#2563eb',
                            fillColor: '#60a5fa',
                            fillOpacity: 0.9,
                            weight: 2
                        }).addTo(popupMap);
                        outletMarker.bindTooltip('Lokasi Outlet', {
                            permanent: false
                        });
                    } else {
                        outletMarker.setLatLng([outletLatLng.lat, outletLatLng.lng]);
                    }

                    if (!selectedLatLng) {
                        selectedDistanceKm = 0;
                        renderRouteTrackingText(
                            'Pilih alamat tujuan untuk menampilkan rute dari outlet.');
                        return;
                    }

                    renderRouteTrackingText('Menghitung rute dari outlet ke tujuan...');

                    fetch(
                            `https://router.project-osrm.org/route/v1/driving/${outletLatLng.lng},${outletLatLng.lat};${selectedLatLng.lng},${selectedLatLng.lat}?overview=full&geometries=geojson`
                        )
                        .then(response => response.ok ? response.json() : null)
                        .then(data => {
                            if (!data || !Array.isArray(data.routes) || data.routes
                                .length === 0) {
                                throw new Error('route_not_found');
                            }

                            const route = data.routes[0];
                            const coords = route.geometry && Array.isArray(route.geometry
                                    .coordinates) ?
                                route.geometry.coordinates : [];
                            const latLngs = coords.map(point => [point[1], point[0]]);

                            if (latLngs.length > 0) {
                                routeLine = L.polyline(latLngs, {
                                    color: '#f97316',
                                    weight: 4,
                                    opacity: 0.9
                                }).addTo(popupMap);
                                popupMap.fitBounds(routeLine.getBounds(), {
                                    padding: [30, 30]
                                });
                            }

                            const distanceKm = Number(route.distance || 0) / 1000;
                            const durationMin = Number(route.duration || 0) / 60;
                            selectedDistanceKm = Number.isFinite(distanceKm) ? Math.max(0,
                                distanceKm) : 0;
                            renderRouteTrackingText(
                                `Rute outlet -> tujuan sekitar ${distanceKm.toFixed(2)} km (${durationMin.toFixed(0)} menit).`
                            );
                        })
                        .catch(() => {
                            routeLine = L.polyline([
                                [outletLatLng.lat, outletLatLng.lng],
                                [selectedLatLng.lat, selectedLatLng.lng]
                            ], {
                                color: '#f97316',
                                weight: 3,
                                dashArray: '8, 8',
                                opacity: 0.75
                            }).addTo(popupMap);
                            popupMap.fitBounds(routeLine.getBounds(), {
                                padding: [30, 30]
                            });

                            const straightDistance = calculateDistanceKm(outletLatLng,
                                selectedLatLng);
                            selectedDistanceKm = Number.isFinite(straightDistance) ? Math
                                .max(0,
                                    straightDistance) : 0;
                            renderRouteTrackingText(
                                `Rute detail belum tersedia. Jarak garis lurus outlet -> tujuan sekitar ${straightDistance.toFixed(2)} km.`
                            );
                        });
                });
            }

            function setMarker(latlng, shouldCenter = false) {
                selectedLatLng = {
                    lat: latlng.lat,
                    lng: latlng.lng
                };
                if (!popupMarker) {
                    popupMarker = L.marker(latlng).addTo(popupMap);
                } else {
                    popupMarker.setLatLng(latlng);
                }

                if (shouldCenter && popupMap) {
                    popupMap.setView([latlng.lat, latlng.lng], 16);
                }

                renderMapResultText(
                    `Koordinat dipilih: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`);

                fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id&lat=${latlng.lat}&lon=${latlng.lng}`
                    )
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        if (data && data.display_name) {
                            manualAddressInput.value = data.display_name;
                            renderMapResultText(data.display_name);
                        }
                        updateRouteTracking();
                    })
                    .catch(() => {
                        // Keep coordinate fallback when reverse geocoding fails.
                        updateRouteTracking();
                    });

                updateRouteTracking();
            }

            function geocodeAddressToMap(addressText) {
                const query = (addressText || '').trim();
                if (!query || query.length < 5) {
                    return;
                }

                geocodeRequestToken += 1;
                const currentToken = geocodeRequestToken;

                fetch(
                        `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=id&q=${encodeURIComponent(query)}`
                    )
                    .then(response => response.ok ? response.json() : [])
                    .then(results => {
                        if (currentToken !== geocodeRequestToken) return;
                        if (!Array.isArray(results) || results.length === 0) {
                            renderMapResultText(
                                'Alamat belum ditemukan di peta. Coba detailkan alamat.');
                            return;
                        }

                        const first = results[0];
                        const lat = Number(first.lat);
                        const lng = Number(first.lon);
                        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                            renderMapResultText(
                                'Koordinat alamat tidak valid dari hasil pencarian.');
                            return;
                        }

                        setMarker({
                            lat,
                            lng
                        }, true);
                        if (first.display_name) {
                            renderMapResultText(first.display_name);
                        }
                    })
                    .catch(() => {
                        renderMapResultText('Gagal mencari alamat. Periksa koneksi internet Anda.');
                    });
            }

            function initMap() {
                if (popupMap || typeof L === 'undefined') return;

                // Fix for Leaflet default marker icons being blocked by Tracking Prevention
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: window.TwinsConfig?.leaflet?.iconRetinaUrl || '',
                    iconUrl: window.TwinsConfig?.leaflet?.iconUrl || '',
                    shadowUrl: window.TwinsConfig?.leaflet?.shadowUrl || '',
                });

                const initialLatLng = selectedLatLng ? [selectedLatLng.lat, selectedLatLng.lng] : [-6.200000, 106.816666];
                const initialZoom = selectedLatLng ? 16 : 12;

                popupMap = L.map('addressMapCanvas', {
                    zoomControl: true,
                    attributionControl: true
                }).setView(initialLatLng, initialZoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(popupMap);

                if (selectedLatLng) {
                    setMarker(selectedLatLng, true);
                } else if ((manualAddressInput.value || '').trim()) {
                    geocodeAddressToMap(manualAddressInput.value);
                }

                popupMap.on('click', e => setMarker(e.latlng));

                updateRouteTracking();
            }

            manualAddressInput.addEventListener('input', () => {
                if (geocodeDebounceTimer) {
                    clearTimeout(geocodeDebounceTimer);
                }
                geocodeDebounceTimer = setTimeout(() => {
                    geocodeAddressToMap(manualAddressInput.value);
                }, 700);
            });

            useCurrentLocationBtn.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    Swal.showValidationMessage('Geolocation tidak didukung oleh browser Anda.');
                    return;
                }

                useCurrentLocationBtn.innerText = '⌛ Mencari...';
                useCurrentLocationBtn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        setMarker(latlng, true);
                        useCurrentLocationBtn.innerText = '📍 Gunakan Lokasi Saat Ini';
                        useCurrentLocationBtn.disabled = false;
                    },
                    (error) => {
                        let msg = 'Gagal mendapatkan lokasi.';
                        if (error.code === 1) msg =
                            'Izin lokasi ditolak. Harap aktifkan izin lokasi di browser.';
                        else if (error.code === 2) msg =
                            'Lokasi tidak tersedia (Pastikan GPS aktif).';
                        else if (error.code === 3) msg =
                            'Waktu pencarian habis. Coba klik lagi.';

                        if (window.location.protocol !== 'https:' && window.location
                            .hostname !== 'localhost' && window.location.hostname !==
                            '127.0.0.1') {
                            msg += ' (GPS memerlukan HTTPS)';
                        }

                        Swal.showValidationMessage(msg);
                        useCurrentLocationBtn.innerText = '📍 Gunakan Lokasi Saat Ini';
                        useCurrentLocationBtn.disabled = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });

            initMap();
            if (popupMap) {
                setTimeout(() => popupMap.invalidateSize(), 100);
            }
        },
        preConfirm: () => {
            const popup = Swal.getPopup();
            const nameIn = popup.querySelector('#recipientNameInput');
            const phoneIn = popup.querySelector('#recipientPhoneInput');
            const addressIn = popup.querySelector('#manualAddressInput');
            const detailIn = popup.querySelector('#detailAddressInput');

            const recipientName = (nameIn.value || '').trim();
            const recipientPhone = (phoneIn.value || '').trim();
            const manualAddress = (addressIn.value || '').trim();
            const detailAddress = (detailIn.value || '').trim();

            if (!recipientName) {
                Swal.showValidationMessage('Nama penerima wajib diisi.');
                return false;
            }

            if (!recipientPhone) {
                Swal.showValidationMessage('No HP wajib diisi.');
                return false;
            }

            if (!manualAddress) {
                Swal.showValidationMessage('Pilih alamat pada peta atau isi alamat utama.');
                return false;
            }

            return {
                recipientName,
                recipientPhone,
                address: manualAddress,
                detail: detailAddress,
                distanceKm: selectedDistanceKm,
                coordinates: selectedLatLng ? {
                    lat: selectedLatLng.lat,
                    lng: selectedLatLng.lng
                } : null
            };
        }
    }).then(async (result) => {
        if (!result.isConfirmed || !result.value) return;

        deliveryContactName = result.value.recipientName;
        deliveryPhone = result.value.recipientPhone;
        deliveryAddress = result.value.address;
        window.deliveryDetailAddress = result.value
            .detail; // Simpan di window agar persisten selama sesi
        deliveryDistanceKm = Number.isFinite(result.value.distanceKm) ? Math.max(0, result.value
            .distanceKm) : 0;
        deliveryCoordinates = result.value.coordinates;

        // Animasi Menyimpan Data
        Swal.fire({
            title: 'Menyimpan Lokasi',
            html: 'Sedang mensinkronkan data alamat Anda...',
            allowOutsideClick: false,
            showConfirmButton: false,
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const persisted = await savePersistedDeliveryAddress();
        updateDeliveryAddressUI();
        renderCart();

        if (!persisted && isAuthenticated) {
            Swal.fire({
                icon: 'warning',
                title: 'Alamat tersimpan sementara',
                text: 'Penyimpanan alamat ke server gagal. Coba simpan ulang alamat Anda.',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--orange-brand)'
            });
            return;
        }

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Alamat Diperbarui',
                text: 'Lokasi telah disinkronkan.',
                timer: 1500,
                showConfirmButton: false,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                customClass: {
                    popup: 'premium-swal-success',
                },
                showClass: {
                    popup: 'premium-swal-show'
                },
                hideClass: {
                    popup: 'premium-swal-hide'
                }
            });
        }, 100);
    });
}

function renderProducts() {
    if (!productGrid) return;
    productGrid.innerHTML = '';

    const searchEl = document.getElementById('searchInput');
    const sortEl = document.getElementById('priceSort');
    const searchTerm = searchEl ? searchEl.value.toLowerCase().trim() : '';
    const priceSort = sortEl ? sortEl.value : 'default';
    const checkedCats = Array.from(document.querySelectorAll('.cat-check:checked')).map(c => c.value);
    const isAllChecked = document.getElementById('check-all') ? document.getElementById('check-all').checked : true;

    let filtered = products.filter(p => {
        const matchesCategory = isAllChecked || checkedCats.length === 0 || checkedCats.includes(p
            .category_id);
        const matchesSearch = p.name.toLowerCase().includes(searchTerm);
        return matchesCategory && matchesSearch;
    });

    if (priceSort === 'low-high') {
        filtered.sort((a, b) => a.price - b.price);
    } else if (priceSort === 'high-low') {
        filtered.sort((a, b) => b.price - a.price);
    }

    if (filtered.length === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.style.cssText =
            'grid-column: 1/-1; text-align: center; padding: 60px; color: var(--sub-text); font-size: 1.1rem;';
        emptyMsg.innerHTML = '<div style="margin-bottom: 15px; font-size: 3rem;">🔍</div>Item tidak ditemukan.';
        productGrid.appendChild(emptyMsg);
        return;
    }

    filtered.forEach(product => {
        const isOutOfStock = product.stok <= 0;
        const card = document.createElement('div');
        card.className = `food-card anim-zoom-in ${isOutOfStock ? 'out-of-stock' : ''}`;

        card.innerHTML = `
            <div style="width: 100%; aspect-ratio: 1/1; overflow: hidden; border-radius: 14px; margin-bottom: 8px; position: relative; background: #fff; display: flex; align-items: center; justify-content: center; padding: 6px;">
                <img src="${product.img}" class="food-img" style="max-width: 100%; max-height: 100%; object-fit: contain; filter: ${isOutOfStock ? 'grayscale(1) opacity(0.6)' : 'none'}" crossorigin="anonymous" referrerpolicy="no-referrer-when-downgrade">

                ${product.is_discount && !isOutOfStock ? `
                    <div style="position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; padding: 2px 5px; border-radius: 4px; font-size: 0.6rem; font-weight: 800; z-index: 2; box-shadow: 0 2px 4px rgba(239,68,68,0.3);">
                        -${product.discount_label}
                    </div>
                ` : ''}

                ${isOutOfStock ? `
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(239, 68, 68, 0.9); color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; z-index: 2; backdrop-filter: blur(4px);">HABIS</div>
                ` : ''}
            </div>
            
            <div style="display: flex; flex-direction: column; flex-grow: 1; min-height: 0;">
                <h4 style="font-size: 0.72rem; color: var(--text-color); font-weight: 700; margin-bottom: 2px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.25; height: 2.5em; min-height: 2.5em;">${product.name}</h4>

                <div style="height: 1em; margin-bottom: 6px; display: flex; align-items: center;">
                    ${!isOutOfStock ? `
                        <p style="color: #10b981; font-size: 0.58rem; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Stok: ${product.stok}</p>
                    ` : ''}
                </div>

                <div style="margin-top: auto;">
                    <div style="height: 0.9rem; margin-bottom: 0px; display: flex; align-items: flex-end;">
                        ${product.is_discount && !isOutOfStock ? `
                            <span style="display: block; color: var(--sub-text); text-decoration: line-through; font-size: 0.65rem; line-height: 1;">
                                ${formatRupiah(product.original_price)}
                            </span>
                        ` : ''}
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                        <span style="font-weight: 800; color: ${isOutOfStock ? 'var(--sub-text)' : 'var(--orange-brand)'}; font-size: 0.8rem; white-space: nowrap;">
                            ${formatRupiah(product.price).replace('Rp', '<span style="font-size: 0.75em;">Rp</span>')}
                        </span>
                        <button class="add-btn"
                                data-name="${product.name}"
                                data-price="${product.price}"
                                data-stock="${product.stok}"
                                onclick="addToCartFromEl(this)"
                                style="width: 28px; height: 28px; border-radius: 8px; background: ${isOutOfStock ? 'rgba(255,255,255,0.1)' : 'var(--btn-grad)'}; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: transform 0.2s; box-shadow: ${isOutOfStock ? 'none' : 'var(--glow)'};">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        productGrid.appendChild(card);
    });
}

function handleSearch() {
    renderProducts();
}

function getCartPricedItems() {
    return cart.map((item) => {
        const pInfo = products.find(p => p.name === item.name);
        let displayPrice = item.price;
        if (pInfo && pInfo.price_levels && pInfo.price_levels.length > 0) {
            const levels = [...pInfo.price_levels].sort((a, b) => b.jmlh - a.jmlh);
            const appliedLevel = levels.find(l => item.qty >= l.jmlh);
            if (appliedLevel) displayPrice = appliedLevel.harga;
        }

        return {
            ...item,
            product_id: pInfo ? pInfo.id : (item.product_id || null),
            image: pInfo ? pInfo.img : '',
            displayPrice,
            subtotal: displayPrice * item.qty,
            original_price: pInfo ? pInfo.original_price : item.price,
            is_discount: pInfo ? pInfo.is_discount : false,
            discount_label: pInfo ? pInfo.discount_label : ''
        };
    });
}

function calculateCartSummary() {
    const pricedItems = getCartPricedItems();
    const subtotal = pricedItems.reduce((acc, item) => acc + item.subtotal, 0);
    
    // Calculate total from original prices to get total product discount
    const originalSubtotal = pricedItems.reduce((acc, item) => acc + (item.original_price * item.qty), 0);
    const productDiscountAmount = originalSubtotal - subtotal;
    
    const shippingFee = calculateTemporaryShippingFee(deliveryDistanceKm);
    
    // Hitung potongan promo secara dinamis berdasarkan tipe voucher/diskon
    let promoDiscountAmount = 0;
    let calculatedDiscountPercent = 0;
    if (appliedPromo) {
        if (appliedPromo.tipe === 'persen') {
            calculatedDiscountPercent = parseFloat(appliedPromo.nilai) / 100;
            promoDiscountAmount = subtotal * calculatedDiscountPercent;
        } else {
            promoDiscountAmount = Math.min(subtotal, parseFloat(appliedPromo.nilai));
            calculatedDiscountPercent = subtotal > 0 ? (promoDiscountAmount / subtotal) : 0;
        }
    }
    
    discountPercent = calculatedDiscountPercent; // update global variable for checkout payload
    const discountedSubtotal = subtotal - promoDiscountAmount;
    
    const totalDiscountAmount = productDiscountAmount + promoDiscountAmount;
    const total = discountedSubtotal + shippingFee;

    return {
        originalSubtotal,
        subtotal, // Subtotal after product discount, before promo
        discountedSubtotal,
        productDiscountAmount,
        promoDiscountAmount,
        totalDiscountAmount,
        shippingFee,
        total,
        pricedItems,
    };
}

function addToCartFromEl(el) {
    const name = el.getAttribute('data-name');
    const price = parseFloat(el.getAttribute('data-price'));
    const stock = parseInt(el.getAttribute('data-stock'));

    if (stock <= 0) {
        Swal.fire('Opps!', 'Stok produk ini sedang habis.', 'error');
        return;
    }

    const productInfo = products.find(p => p.name === name);
    addToCart(name, price, productInfo ? productInfo.id : null);
}

function addToCart(name, price, productId = null) {
    // Temukan info stok asli dari array products
    const productInfo = products.find(p => p.name === name);
    if (productInfo && productInfo.stok <= 0) {
        Swal.fire('Maaf!', 'Stok barang ini sudah habis.', 'error');
        return;
    }

    const existingItem = cart.find(item => item.product_id === productId || item.name === name);
    if (existingItem) {
        // Cek jika jumlah di keranjang sudah melebihi stok
        if (existingItem.qty >= productInfo.stok) {
            Swal.fire('Limit Stok!', `Anda hanya bisa memesan maksimal ${productInfo.stok} item.`, 'warning');
            return;
        }
        existingItem.qty += 1;
    } else {
        cart.push({
            name,
            product_id: productId,
            price,
            qty: 1
        });
    }
    renderCart();
    const fab = document.getElementById('mobileCartBtn');
    if (fab) {
        fab.style.transform = 'scale(1.2)';
        setTimeout(() => fab.style.transform = '', 200);
    }
}

function updateQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function renderCart() {
    applyCheckoutAvailability();

    const isMobile = window.innerWidth <= 992;
    const badge = document.getElementById('cartBadge');
    const totalCount = cart.reduce((acc, item) => acc + item.qty, 0);
    if (badge) badge.innerText = totalCount;

    const mobileCartBtn = document.getElementById('mobileCartBtn');

    if (cart.length > 0) {
        [...addressSections, ...orderSections, ...discountSections].forEach(el => el.classList.remove('hidden'));
        if (!isMobile) {
            mainContainer.classList.add('has-sidebar');
            if (mobileCartBtn) mobileCartBtn.style.display = 'none';
        } else {
            mainContainer.classList.remove('has-sidebar');
            if (mobileCartBtn) mobileCartBtn.style.display = 'flex';
        }
    } else {
        [...addressSections, ...orderSections, ...discountSections].forEach(el => el.classList.add('hidden'));
        mainContainer.classList.remove('has-sidebar');
        if (mobileCartBtn) mobileCartBtn.style.display = 'none';
        toggleBottomSheet(false);
    }

    // Calculate totals
    const summary = calculateCartSummary();
    const cartHtmlItems = summary.pricedItems.map((item, index) => {
        const pInfo = {
            img: item.image,
        };

        return `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: white; flex-shrink: 0;">
                        <img src="${pInfo ? pInfo.img : ''}" style="width: 100%; height: 100%; object-fit: cover;" crossorigin="anonymous" referrerpolicy="no-referrer-when-downgrade">
                    </div>
                    <div style="flex: 1;">
                        <h5 style="font-size: 0.85rem;">${item.name}</h5>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                            <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span style="font-size: 0.8rem;">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; text-align: right;">
                    ${item.displayPrice < item.price ? `<span style="color: var(--sub-text); font-size: 0.65rem; text-decoration: line-through;">${formatRupiah(item.price * item.qty)}</span>` : ''}
                    <span style="color: var(--orange-brand); font-weight: 700; font-size: 0.75rem;">${formatRupiah(item.subtotal).replace('Rp', '<span style="font-size: 0.8em;">Rp</span>')}</span>
                    ${item.displayPrice < item.price ? `<span style="font-size: 0.65rem; color: #10b981; font-weight: 700;">Hemat Grosir!</span>` : ''}
                    <button class="delete-item-btn" onclick="removeFromCart(${index})">🗑️</button>
                </div>
            </div>
        `;
    }).join('');

    const formattedTotal = formatRupiah(summary.total);

    document.querySelectorAll('.originalSubtotalDisplay').forEach(el => {
        el.innerText = formatRupiah(summary.originalSubtotal);
    });

    document.querySelectorAll('.discountedSubtotalDisplay').forEach(el => {
        el.innerText = formatRupiah(summary.discountedSubtotal);
    });

    document.querySelectorAll('.shippingFeeDisplay').forEach(el => {
        el.innerText = formatRupiah(summary.shippingFee);
    });

    const hasDiscount = summary.totalDiscountAmount > 0;

    ['.originalSubtotalDisplay', '.totalDiscountDisplay'].forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            if (el.parentElement) {
                el.parentElement.style.display = hasDiscount ? 'flex' : 'none';
            }
        });
    });

    document.querySelectorAll('.discountedSubtotalDisplay').forEach(el => {
        const labelSpan = el.parentElement.querySelector('span:first-child');
        if (labelSpan) {
            labelSpan.innerText = hasDiscount ? 'Harga Setelah Diskon' : 'Harga Produk';
        }
        el.innerText = formatRupiah(summary.discountedSubtotal);
    });

    document.querySelectorAll('.totalDiscountDisplay').forEach(el => {
        el.innerText = hasDiscount ? "- " + formatRupiah(summary.totalDiscountAmount) : "Rp 0";
    });

    document.querySelectorAll('.totalPriceDisplay').forEach(el => {
        el.innerHTML = formattedTotal.replace('Rp', '<span style="font-size: 0.8em;">Rp</span>');
    });

    document.querySelectorAll('.cart-items-container').forEach(c => c.innerHTML = cartHtmlItems);

    if (isMobile) {
        const sheetContent = document.getElementById('mobileSheetContent');
        if (sheetContent) {
            sheetContent.querySelectorAll('.totalPriceDisplay').forEach(el => el.innerHTML = formattedTotal.replace(
                'Rp', '<span style="font-size: 0.8em;">Rp</span>'));
        }
    }

    updateDeliveryAddressUI();
    savePersistence();
}

function toggleBottomSheet(force) {
    const sheet = document.getElementById('bottomSheet');
    const overlay = document.getElementById('sheetOverlay');
    if (!sheet || !overlay) return;

    let isActive = false;
    if (force === true) {
        sheet.classList.add('active');
        overlay.classList.add('active');
        isActive = true;
    } else if (force === false) {
        sheet.classList.remove('active');
        overlay.classList.remove('active');
        isActive = false;
    } else {
        sheet.classList.toggle('active');
        overlay.classList.toggle('active');
        isActive = sheet.classList.contains('active');
    }

    if (isActive) {
        renderCart();
    }
}

function showWholesaleInfo(productId) {
    const product = products.find(p => p.id === productId);
    if (!product || !product.price_levels) return;

    let tableHtml = `
        <div style="text-align: left; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee;">
                        <th style="padding: 10px 5px; text-align: left;">Min. Pembelian</th>
                        <th style="padding: 10px 5px; text-align: left;">Harga per Unit</th>
                    </tr>
                </thead>
                <tbody>
    `;

    product.price_levels.forEach(level => {
        tableHtml += `
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 5px; font-weight: 600;">${level.jmlh} Unit atau lebih</td>
                <td style="padding: 12px 5px; color: #C62828; font-weight: 700;">${formatRupiah(level.harga)}</td>
            </tr>
        `;
    });

    tableHtml += `</tbody></table></div>`;

    Swal.fire({
        title: 'Harga Grosir',
        html: `Dapatkan harga lebih hemat untuk pembelian dalam jumlah banyak pada produk <strong>${product.name}</strong>.<br>${tableHtml}`,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: 'var(--orange-brand)'
    });
}

function applyPromo(target = 'desktop') {
    const inputId = target === 'mobile' ? 'promoInputMobile' : 'promoInput';
    const msgId = target === 'mobile' ? '.promoMessage' : '#promoMessage';
    
    const inputEl = document.getElementById(inputId);
    if (!inputEl) return;
    
    const code = inputEl.value.trim().toUpperCase();
    const messageEls = target === 'mobile' ? document.querySelectorAll('.promoMessage') : [document.getElementById('promoMessage')];
    
    const promosEl = document.getElementById('promos-data');
    const activePromos = promosEl ? JSON.parse(promosEl.textContent || '[]') : [];
    
    const foundPromo = activePromos.find(p => p.kode === code);
    
    if (foundPromo) {
        appliedPromo = foundPromo;
        messageEls.forEach(el => {
            if (!el) return;
            if (foundPromo.tipe === 'persen') {
                el.innerText = `Promo ${foundPromo.kode} berhasil digunakan! (Diskon ${foundPromo.nilai}%)`;
            } else {
                const formattedNominal = "Rp " + Math.floor(foundPromo.nilai).toLocaleString('id-ID');
                el.innerText = `Promo ${foundPromo.kode} berhasil digunakan! (Potongan ${formattedNominal})`;
            }
            el.style.color = "#10b981";
            el.style.display = 'block';
        });
    } else {
        appliedPromo = null;
        messageEls.forEach(el => {
            if (!el) return;
            el.innerText = code === "" ? "" : "Kode promo tidak valid.";
            el.style.color = "#ef4444";
            el.style.display = code === "" ? 'none' : 'block';
        });
    }
    renderCart();
}

function switchPage(page) {
    document.querySelectorAll('.nav-link, .mob-nav-item').forEach(l => l.classList.remove('active'));
    homePage.classList.add('hidden');
    historyPage.classList.add('hidden');

    if (page === 'home') {
        homePage.classList.remove('hidden');
        document.getElementById('nav-home').classList.add('active');
        const mobHome = document.getElementById('mob-home');
        if (mobHome) mobHome.classList.add('active');
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        renderCart();
    } else if (page === 'history') {
        historyPage.classList.remove('hidden');
        document.getElementById('nav-history').classList.add('active');
        const mobHistory = document.getElementById('mob-history');
        if (mobHistory) mobHistory.classList.add('active');
        mainContainer.classList.remove('has-sidebar');
        if (isAuthenticated) {
            fetchHistoryFromServer().then(() => renderHistory());
        } else {
            renderHistory();
        }
    }
}

function scrollToCategory() {
    switchPage('home');
    document.querySelectorAll('.nav-link, .mob-nav-item').forEach(l => l.classList.remove('active'));
    document.getElementById('nav-cat').classList.add('active');
    const mobCat = document.getElementById('mob-cat');
    if (mobCat) mobCat.classList.add('active');

    setTimeout(() => {
        const categorySection = document.getElementById('categorySection');
        if (categorySection) {
            categorySection.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }, 100);
}

function goToWhatsApp() {
    window.open(`https://wa.me/6282330755390?text=Halo TWINS!`, '_blank');
}

function checkout() {
    if (isStoreClosedNow()) {
        showStoreClosedNotification();
        return;
    }

    if (cart.length === 0) return;

    if (!isAuthenticated) {
        Swal.fire({
            title: 'Login Diperlukan',
            text: 'Silakan login terlebih dahulu untuk melanjutkan checkout.',
            icon: 'warning',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--orange-brand)',
            confirmButtonText: 'Login Sekarang',
            showCancelButton: true,
            cancelButtonText: 'Nanti',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = loginUrl;
            }
        });
        return;
    }

    if (!midtransEnabled || typeof window.snap === 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Pembayaran Belum Tersedia',
            text: 'Konfigurasi Midtrans belum aktif. Hubungi admin untuk mengaktifkan pembayaran.',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--orange-brand)'
        });
        return;
    }

    const recipientName = (deliveryContactName || '').trim();
    const recipientPhone = (deliveryPhone || '').trim();
    const address = (deliveryAddress || '').trim();

    if (!recipientName || !recipientPhone || !address) {
        Swal.fire({
            icon: 'warning',
            title: 'Lengkapi Data Pengiriman',
            text: 'Isi dulu nama penerima, no HP, dan alamat pengiriman sebelum checkout.',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--orange-brand)'
        });
        return;
    }

    const summary = calculateCartSummary();
    if (summary.total <= 0) {
        Swal.fire('Oops', 'Total pembayaran tidak valid.', 'error');
        return;
    }

    const visibleItems = summary.pricedItems.slice(0, 5);
    const hiddenItemCount = Math.max(0, summary.pricedItems.length - visibleItems.length);
    const itemListHtml = visibleItems.map(i => `
        <div style="display:flex; justify-content:space-between; gap:10px; font-size:0.82rem; padding:8px 0; border-bottom:1px dashed rgba(148,163,184,0.25);">
            <div style="flex:1; min-width:0;">
                <span style="color:var(--text-color); display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(i.qty)}x ${escapeHtml(i.name)}</span>
                ${i.is_discount ? `
                    <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                        <span style="font-size:0.7rem; text-decoration:line-through; color:var(--sub-text);">${escapeHtml(formatRupiah(i.original_price * i.qty))}</span>
                        <span style="font-size:0.7rem; color:#10b981; font-weight:700;">Diskon Produk</span>
                    </div>
                ` : ''}
            </div>
            <span style="color:var(--orange-brand); font-weight:700; white-space:nowrap; align-self:center;">${escapeHtml(formatRupiah(i.subtotal))}</span>
        </div>
    `).join('');

    const totalDiscountFormatted = summary.totalDiscountAmount > 0 ? `- ${formatRupiah(summary.totalDiscountAmount)}` : 'Rp 0';
    const estimatedArrivalMinutes = Math.max(10, Math.round(Number(deliveryDistanceKm || 0) * 4));

    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        html: `
            <div style="text-align:left; font-size:0.9rem; line-height:1.45; display:grid; gap:12px;">
                <div style="border:1px solid var(--card-border); border-radius:12px; padding:12px; background:rgba(15,23,42,0.18);">
                    <p style="margin:0 0 8px 0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">DETAIL PENGIRIMAN</p>
                    <div style="display:grid; gap:6px; font-size:0.82rem;">
                        <div><span style="color:var(--sub-text);">Penerima:</span> <strong>${escapeHtml(recipientName)}</strong></div>
                        <div><span style="color:var(--sub-text);">No HP:</span> <strong>${escapeHtml(recipientPhone)}</strong></div>
                        <div><span style="color:var(--sub-text);">Jarak:</span> <strong>${escapeHtml(Number(deliveryDistanceKm || 0).toFixed(2))} km</strong></div>
                        <div><span style="color:var(--sub-text);">Estimasi:</span> <strong>${escapeHtml(estimatedArrivalMinutes)} menit</strong></div>
                        <div style="display:flex; gap:6px;"><span style="color:var(--sub-text); white-space:nowrap;">Alamat:</span><span style="word-break:break-word;">${escapeHtml(address)}</span></div>
                    </div>
                </div>

                <div style="border:1px solid var(--card-border); border-radius:12px; padding:12px; background:rgba(2,132,199,0.06);">
                    <p style="margin:0 0 8px 0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">RINGKASAN ITEM (${escapeHtml(summary.pricedItems.length)})</p>
                    <div style="max-height:168px; overflow:auto; padding-right:4px;">${itemListHtml}</div>
                    ${hiddenItemCount > 0 ? `<p style="margin:8px 0 0 0; font-size:0.75rem; color:var(--sub-text);">+${escapeHtml(hiddenItemCount)} item lainnya</p>` : ''}
                </div>

                <div style="border:1px solid rgba(249,115,22,0.35); border-radius:14px; padding:12px; background:linear-gradient(135deg, rgba(249,115,22,0.14), rgba(249,115,22,0.04));">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:8px;">
                        <div>
                            <p style="margin:0; font-size:0.75rem; letter-spacing:0.04em; color:var(--sub-text);">TOTAL PEMBAYARAN</p>
                            <p style="margin:4px 0 0 0; font-size:1.22rem; font-weight:800; color:var(--orange-brand);">${escapeHtml(formatRupiah(summary.total))}</p>
                        </div>
                    </div>
                    <div style="display:grid; gap:5px; font-size:0.8rem;">
                        ${summary.totalDiscountAmount > 0 ? `
                            <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Harga Awal</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.originalSubtotal))}</span></div>
                            <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Diskon</span><span style="font-weight:700; color:#10b981;">${escapeHtml(totalDiscountFormatted)}</span></div>
                            <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Harga Setelah Diskon</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.discountedSubtotal))}</span></div>
                        ` : `
                            <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Harga Produk</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.discountedSubtotal))}</span></div>
                        `}
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--sub-text);">Ongkir</span><span style="font-weight:700;">${escapeHtml(formatRupiah(summary.shippingFee))}</span></div>
                    </div>
                </div>

                <p style="margin:0; font-size:0.76rem; color:var(--sub-text);">Setelah klik Lanjut ke Pembayaran, Anda akan diarahkan ke popup Midtrans untuk memilih metode pembayaran.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Lanjut ke Pembayaran',
        cancelButtonText: 'Kembali',
        confirmButtonColor: 'var(--orange-brand)',
        width: 'min(760px, 96vw)',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        didOpen: () => {
            const popup = Swal.getPopup();
            if (popup) {
                popup.style.borderRadius = '20px';
            }
            const htmlContainer = Swal.getHtmlContainer();
            if (htmlContainer) {
                htmlContainer.style.maxHeight = '70vh';
                htmlContainer.style.overflowY = 'auto';
                htmlContainer.style.overflowX = 'hidden';
                htmlContainer.style.paddingRight = '4px';
            }
        },
        preConfirm: () => {
            return fetch(checkoutTokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        recipient_name: recipientName,
                        recipient_phone: recipientPhone,
                        address,
                        coordinates: deliveryCoordinates ? {
                            lat: deliveryCoordinates.lat,
                            lng: deliveryCoordinates.lng
                        } : null,
                        distance_km: deliveryDistanceKm,
                        discount_percent: discountPercent,
                        shipping_fee: summary.shippingFee,
                        items: summary.pricedItems.map(i => ({
                            product_id: i.product_id,
                            name: i.name,
                            qty: i.qty,
                            unit_price: i.original_price,
                            discount_amount: (i.original_price - i.displayPrice) * i.qty
                        }))
                    })
                })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const errorMessage = data.message || 'Gagal membuat token pembayaran.';
                        throw new Error(errorMessage);
                    }
                    return data;
                })
                .catch((error) => {
                    Swal.showValidationMessage(error.message || 'Gagal memproses pembayaran.');
                });
        }
    }).then((result) => {
        if (!result.isConfirmed || !result.value || !result.value.snap_token) return;

        const paymentData = result.value;

        const finalizeOrder = (paymentStatus, paymentResult) => {
            historyData.unshift({
                id: paymentData.order_id || Date.now(),
                payment_order_db_id: paymentData.payment_order_id || null,
                date: new Date().toLocaleString('id-ID'),
                items: summary.pricedItems.map(i => ({
                    name: i.name,
                    qty: i.qty,
                    price: i.displayPrice,
                    product_id: i.product_id
                })),
                total: summary.total,
                subtotal_amount: summary.originalSubtotal,
                shipping_fee: summary.shippingFee,
                meta: JSON.stringify({
                    item_discount_total: summary.productDiscountAmount,
                    global_discount_amount: summary.promoDiscountAmount
                }),
                recipient_name: recipientName,
                recipient_phone: recipientPhone,
                address,
                coordinates: deliveryCoordinates ? {
                    lat: deliveryCoordinates.lat,
                    lng: deliveryCoordinates.lng
                } : null,
                payment_status: paymentStatus,
                midtrans_result: paymentResult || null,
            });

            cart = [];
            discountPercent = 0;
            appliedPromo = null;
            savePersistence();
            
            // Reload otomatis ke halaman riwayat
            safeLocalStorage.setItem('open_history_on_load', 'true');
            
            const loader = document.getElementById('global-page-loader');
            if (loader) {
                const loaderText = document.getElementById('global-page-loader-text');
                if (loaderText) loaderText.innerText = 'Sinkronisasi Pesanan...';
                loader.style.visibility = 'visible';
                loader.style.opacity = '1';
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 150);
        };

        const syncPaymentStatus = (orderId) => {
            const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
            return fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    order_id: orderId
                })
            }).catch(err => console.error('Sync failed:', err));
        };

        window.snap.pay(paymentData.snap_token, {
            onSuccess: function(resultSnap) {
                Swal.fire({
                    title: 'Memproses Transaksi...',
                    text: 'Pembayaran berhasil! Sedang mensinkronkan pesanan Anda.',
                    allowOutsideClick: false,
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                syncPaymentStatus(paymentData.order_id).finally(() => {
                    finalizeOrder('paid', resultSnap);
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil',
                        text: 'Pesanan Anda telah diterima dan sedang diproses.',
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--orange-brand)'
                    }).then(() => {
                        switchPage('history');
                    });
                });
            },
            onPending: function(resultSnap) {
                Swal.fire({
                    title: 'Menunggu Konfirmasi...',
                    text: 'Sedang menyiapkan instruksi pembayaran.',
                    allowOutsideClick: false,
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                syncPaymentStatus(paymentData.order_id).finally(() => {
                    finalizeOrder('pending', resultSnap);
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        text: 'Silakan selesaikan pembayaran Anda sesuai instruksi.',
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--orange-brand)'
                    }).then(() => {
                        switchPage('history');
                    });
                });
            },
            onError: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Transaksi gagal diproses. Silakan coba lagi nanti melalui Riwayat.',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--orange-brand)'
                }).then(() => {
                    finalizeOrder('failed', null);
                });
            },
            onClose: function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Pembayaran Tertunda',
                    text: 'Anda belum menyelesaikan pembayaran. Pesanan telah disimpan di Riwayat.',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    confirmButtonColor: 'var(--orange-brand)'
                }).then(() => {
                    finalizeOrder('pending', null);
                });
            }
        });
    });
}

function getPaymentStatusLabel(status) {
    const normalized = (status || '').toLowerCase();
    if (normalized === 'pending') return 'MENUNGGU PEMBAYARAN';
    if (normalized === 'paid' || normalized === 'success' || normalized === 'settlement' || normalized === 'capture') return 'PESANAN DIPROSES';
    if (normalized === 'expired') return 'KADALUWARSA';
    if (normalized === 'canceled' || normalized === 'cancel') return 'DIBATALKAN';
    if (normalized === 'denied') return 'DITOLAK';
    return 'GAGAL';
}

function getPaymentStatusColor(status) {
    const normalized = (status || '').toLowerCase();
    if (normalized === 'pending') return '#f59e0b'; // Amber/Orange
    if (normalized === 'paid' || normalized === 'success' || normalized === 'settlement' || normalized === 'capture') return '#10b981'; // Green
    if (normalized === 'expired' || normalized === 'canceled' || normalized === 'denied' || normalized === 'failed') return '#ef4444'; // Red
    return '#10b981';
}

function renderHistory() {
    if (historyData.length === 0) {
        historyList.innerHTML =
            '<p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>';
        return;
    }
    historyList.innerHTML = historyData.map(trx => `
        <div class="history-item" data-db-id="${trx.payment_order_db_id || ''}" onclick="showTransactionDetail('${trx.payment_order_db_id || ''}','${trx.id}')" style="display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 10px; cursor: pointer;">
            <div>
                <p style="font-weight: 700;">ID: #${trx.id.toString().slice(-6)}</p>
                <p style="font-size: 0.75rem; color: var(--sub-text);">${trx.date}</p>
                <p style="font-size: 0.85rem; margin-top: 8px;">${trx.items.map(i => `${i.qty}x ${i.name}`).join(', ')}</p>
                <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">👤 ${trx.recipient_name || '-'} | 📞 ${trx.recipient_phone || '-'}</p>
                <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">📍 ${trx.address || '-'}</p>
                <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">🚚 Ongkir: ${formatRupiah(trx.shipping_fee || 0)}</p>
            </div>
            <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--orange-brand);">${formatRupiah(trx.total)}</span>
                <p style="color: ${getPaymentStatusColor(trx.payment_status)}; font-size: 0.7rem; font-weight: bold;">${getPaymentStatusLabel(trx.payment_status)}</p>
                ${(trx.payment_status || '').toLowerCase() === 'pending' && trx.snap_token ? `
                    <button onclick="event.stopPropagation(); payPendingOrder('${trx.snap_token}', '${trx.id}')" style="background: var(--orange-brand); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.3);">Bayar Sekarang</button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

function payPendingOrder(token, orderId) {
    if (typeof window.snap === 'undefined') {
        Swal.fire('Error', 'Midtrans Snap belum dimuat.', 'error');
        return;
    }

    const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
    const syncPaymentStatus = (oId) => {
        return fetch(syncUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_id: oId
            })
        }).catch(err => console.error('Sync failed:', err));
    };

    window.snap.pay(token, {
        onSuccess: function(resultSnap) {
            Swal.fire({
                title: 'Memproses Transaksi...',
                text: 'Sedang mensinkronkan status pembayaran Anda.',
                allowOutsideClick: false,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            syncPaymentStatus(orderId).finally(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil',
                    text: 'Status pesanan Anda telah diperbarui.',
                    confirmButtonColor: 'var(--orange-brand)',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                }).then(() => {
                    fetchHistoryFromServer().then(() => renderHistory());
                });
            });
        },
        onPending: function(resultSnap) {
            Swal.fire({
                title: 'Menunggu Konfirmasi...',
                text: 'Sedang mengecek status pembayaran.',
                allowOutsideClick: false,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            syncPaymentStatus(orderId).finally(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Pembayaran',
                    text: 'Silakan selesaikan pembayaran Anda.',
                    confirmButtonColor: 'var(--orange-brand)',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                }).then(() => {
                    fetchHistoryFromServer().then(() => renderHistory());
                });
            });
        }
    });
}

// Show transaction detail modal. Try DB lookup by numeric id first, fall back to minimal data in history.
function showTransactionDetail(dbId, legacyId) {
    const outletUuid = document.querySelector('meta[name="outlet-uuid"]').content || '';
    
    // Show fast loading animation
    Swal.fire({
        title: 'Memuat Pesanan...',
        allowOutsideClick: false,
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        didOpen: () => {
            Swal.showLoading();
        }
    });

    if (dbId) {
        const url = `/outlet/${outletUuid}/payment-order/${encodeURIComponent(dbId)}`;
        fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(data => {
                const order = data.order;
                const items = order.items || [];
                const itemsHtml = items.map(it => `
                    <div style="display:flex; justify-content:space-between; gap:8px; padding:10px 0; border-bottom:1px solid rgba(148,163,184,0.08);">
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700;">${escapeHtml(it.product_name)}</div>
                            <div style="font-size:0.8rem; color:var(--sub-text);">${it.quantity} × ${formatRupiah(it.unit_price)}</div>
                            ${it.discount_amount > 0 ? `<div style="font-size:0.8rem; color:#10b981;">Diskon: ${formatRupiah(it.discount_amount)}</div>` : ''}
                        </div>
                        <div style="text-align:right;">
                            ${it.discount_amount > 0 ? `<div style="font-size:0.65rem; color:var(--sub-text); text-decoration:line-through;">${formatRupiah(Number(it.unit_price) * Number(it.quantity))}</div>` : ''}
                            <div style="font-weight:700;">${formatRupiah(it.final_price)}</div>
                        </div>
                    </div>
                `).join('');

                const meta = order.meta || {};
                const orderDate = new Date(order.created_at).toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short'
                });

                Swal.fire({
                    title: `<div style="font-size: 1.1rem; font-weight: 800;">Detail Pesanan #${String(order.id).slice(-6)}</div>`,
                    html: `
                        <div style="text-align:left; font-size:0.9rem; line-height:1.45;">
                            <div style="background: rgba(148,163,184,0.05); padding: 12px; border-radius: 12px; margin-bottom: 20px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="color: var(--sub-text);">Status:</span>
                                    <strong style="color: ${getPaymentStatusColor(order.payment_status)};">${getPaymentStatusLabel(order.payment_status)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="color: var(--sub-text);">Waktu:</span>
                                    <strong>${orderDate}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--sub-text);">Metode:</span>
                                    <strong>${(order.midtrans_payment_type || order.payment_gateway || 'Midtrans').toUpperCase()}</strong>
                                </div>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">PENERIMA</p>
                                <p style="margin:0; font-weight: 700;">${escapeHtml(order.recipient_name)} | ${escapeHtml(order.recipient_phone)}</p>
                                <p style="margin:4px 0 0 0; font-size:0.8rem; color:var(--sub-text);">${escapeHtml(order.delivery_address || '')}</p>
                            </div>

                            <div style="margin-bottom: 10px;">
                                <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">DAFTAR ITEM</p>
                                <div style="border-top:1px solid rgba(148,163,184,0.06);">${itemsHtml}</div>
                            </div>

                            <div style="margin-top:20px; background: rgba(249, 115, 22, 0.05); padding: 12px; border-radius: 12px; font-size:0.9rem;">
                                ${(() => {
                                    let meta = {};
                                    try { meta = typeof order.meta === 'string' ? JSON.parse(order.meta) : (order.meta || {}); } catch(e) {}
                                    const itemDisc = Number(meta.item_discount_total) || 0;
                                    const globalDisc = Number(meta.global_discount_amount) || 0;
                                    const totalDisc = itemDisc + globalDisc;
                                    const originalSubtotal = Number(order.subtotal_amount) + itemDisc;

                                    const formattedDisc = totalDisc > 0 ? `- ${formatRupiah(totalDisc)}` : 'Rp 0';
                                    return `
                                        ${totalDisc > 0 ? `
                                            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span style="color: var(--sub-text);">Harga Awal</span><strong>${formatRupiah(originalSubtotal)}</strong></div>
                                            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span style="color: var(--sub-text);">Diskon</span><strong style="color: #10b981;">${formattedDisc}</strong></div>
                                            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span style="color: var(--sub-text);">Harga Setelah Diskon</span><strong>${formatRupiah(Number(order.subtotal_amount) - globalDisc)}</strong></div>
                                        ` : `
                                            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span style="color: var(--sub-text);">Harga Produk</span><strong>${formatRupiah(Number(order.subtotal_amount))}</strong></div>
                                        `}
                                        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><span style="color: var(--sub-text);">Ongkir</span><strong>${formatRupiah(order.shipping_fee || 0)}</strong></div>
                                        <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:1.1rem; color: var(--orange-brand);"><span>Total</span><strong>${formatRupiah(order.total_amount)}</strong></div>
                                    `;
                                })()}
                            </div>

                            <div style="margin-top: 25px; display: grid; gap: 10px;">
                                <a href="https://wa.me/6281249414369?text=Halo Admin, saya ingin menanyakan pesanan #${String(order.id).slice(-6)}" target="_blank" style="text-decoration: none; background: #25D366; color: white; padding: 12px; border-radius: 12px; text-align: center; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <span>💬 Hubungi Admin</span>
                                </a>
                            </div>
                        </div>
                    `,
                    width: 'min(500px, 96vw)',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: 'var(--orange-brand)',
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)',
                    didOpen: () => {
                        const popup = Swal.getPopup();
                        if (popup) {
                            popup.style.borderRadius = '20px';
                        }
                        const htmlContainer = Swal.getHtmlContainer();
                        if (htmlContainer) {
                            htmlContainer.style.maxHeight = '70vh';
                            htmlContainer.style.overflowY = 'auto';
                            htmlContainer.style.overflowX = 'hidden';
                            htmlContainer.style.paddingRight = '4px';
                        }
                    }
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat detail pesanan',
                    text: 'Silakan coba lagi nanti.',
                    confirmButtonColor: 'var(--orange-brand)'
                });
            });
        return;
    }

    // Fallback: cari di historyData berdasarkan legacy id
    const found = historyData.find(h => String(h.id) === String(legacyId));
    if (!found) {
        Swal.fire({
            icon: 'info',
            title: 'Detail tidak tersedia',
            text: 'Detail pesanan tidak ditemukan di riwayat lokal.',
            confirmButtonColor: 'var(--orange-brand)'
        });
        return;
    }

    const fallbackHtml = found.items.map(i => `
        <div style="display:flex; justify-content:space-between; gap:8px; padding:10px 0; border-bottom:1px solid rgba(148,163,184,0.08);">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:700;">${escapeHtml(i.name)}</div>
                <div style="font-size:0.8rem; color:var(--sub-text);">${i.qty} × ${formatRupiah(i.price)}</div>
            </div>
            <div style="text-align:right; font-weight:700;">${formatRupiah(i.qty * i.price)}</div>
        </div>
    `).join('');

    Swal.fire({
        title: `<div style="font-size: 1.1rem; font-weight: 800;">Detail Pesanan #${String(found.id).slice(-6)}</div>`,
        html: `
            <div style="text-align:left; font-size:0.9rem; line-height:1.45;">
                <div style="background: rgba(148,163,184,0.05); padding: 12px; border-radius: 12px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: var(--sub-text);">Status:</span>
                        <strong style="color: ${getPaymentStatusColor(found.payment_status)};">${getPaymentStatusLabel(found.payment_status)}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--sub-text);">Waktu:</span>
                        <strong>${found.date || '-'}</strong>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">PENERIMA</p>
                    <p style="margin:0; font-weight: 700;">${escapeHtml(found.recipient_name || '-')} | ${escapeHtml(found.recipient_phone || '-')}</p>
                    <p style="margin:4px 0 0 0; font-size:0.8rem; color:var(--sub-text);">${escapeHtml(found.address || '')}</p>
                </div>

                <div style="margin-bottom: 10px;">
                    <p style="margin:0 0 6px 0; font-size:0.75rem; color:var(--sub-text); font-weight: 700;">DAFTAR ITEM</p>
                    <div style="border-top:1px solid rgba(148,163,184,0.06);">${fallbackHtml}</div>
                </div>

                <div style="margin-top:20px; background: rgba(249, 115, 22, 0.05); padding: 12px; border-radius: 12px; font-size:0.9rem;">
                    <div style="display:flex; justify-content:space-between; margin-top:4px; font-size:1.1rem; color: var(--orange-brand);"><span>Total</span><strong>${formatRupiah(found.total)}</strong></div>
                </div>

                <div style="margin-top: 25px;">
                    <a href="https://wa.me/6281249414369?text=Halo Admin, saya ingin menanyakan pesanan #${String(found.id).slice(-6)}" target="_blank" style="text-decoration: none; background: #25D366; color: white; padding: 12px; border-radius: 12px; text-align: center; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>💬 Hubungi Admin</span>
                    </a>
                </div>
            </div>
        `,
        width: 'min(500px, 96vw)',
        confirmButtonText: 'Tutup',
        confirmButtonColor: 'var(--orange-brand)',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
    });
}

// Intersection Observer for Animations
window.observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
        }
    });
}, {
    threshold: 0.1
});

// Theme Menu Logic
function toggleThemeMenu() {
    document.getElementById('themeMenu').classList.toggle('show');
}

function setTheme(themeName) {
    body.setAttribute('data-theme', themeName);
    safeLocalStorage.setItem('twins_theme', themeName);
    document.getElementById('themeMenu').classList.remove('show');
    updateActiveThemeBtn(themeName);
}

function updateActiveThemeBtn(themeName) {
    document.querySelectorAll('#themeMenu button').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-theme-val') === themeName) {
            btn.classList.add('active');
        }
    });
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    const menu = document.getElementById('themeMenu');
    const btn = document.querySelector('.theme-btn');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
}, true);

// Initialize Theme from Storage
const savedTheme = safeLocalStorage.getItem('twins_theme') || 'dark';
setTheme(savedTheme);

document.querySelectorAll('.anim-fade-up, .anim-zoom-in, .white-card').forEach(el => {
    if (!el.classList.contains('anim-fade-up') && !el.classList.contains('anim-zoom-in')) {
        el.classList.add('anim-fade-up');
    }
    window.observer.observe(el);
});

window.addEventListener('resize', renderCart);
renderProducts();
renderCart();
syncPersistedDeliveryDistance();

// Performa Instan: Sembunyikan loader utama segera setelah HTML ter-parsing tanpa menunggu download library CDN selesai!
const globalLoader = document.getElementById('global-page-loader');
if (globalLoader) {
    globalLoader.style.opacity = '0';
    globalLoader.style.visibility = 'hidden';
}

document.addEventListener("DOMContentLoaded", function() {
    const items = ['🧁', '🥐', '🍰', '🥨', '🎂', '🍪', '🥖', '🥞', '🍩'];
    const bgContainer = document.getElementById('bakery-bg');
    let parallaxLayers = [];

    if (bgContainer) {
        // Initialize 3D Engine for Background
        bgContainer.style.perspective = '1200px';
        bgContainer.style.transformStyle = 'preserve-3d';

        for (let i = 0; i < 20; i++) {
            const el = document.createElement('div');
            el.className = 'walking-cake ' + (Math.random() > 0.5 ? 'dir-right' : 'dir-left');
            el.innerText = items[Math.floor(Math.random() * items.length)];
            el.style.top = (Math.random() * 90) + 'vh';
            el.style.animationDuration = (Math.random() * 25 + 20) + 's';
            el.style.animationDelay = '-' + (Math.random() * 20) + 's';
            el.style.fontSize = (Math.random() * 2.5 + 1.5) + 'rem';

            const wrapper = document.createElement('div');
            wrapper.style.position = 'absolute';
            wrapper.style.width = '100vw';
            wrapper.style.height = '100vh';
            wrapper.style.top = '0';
            wrapper.style.left = '0';
            wrapper.style.pointerEvents = 'none';
            wrapper.style.transformStyle = 'preserve-3d';

            const depth = Math.random() * 200 - 100; // Between -100px and +100px Z depth
            wrapper.dataset.depthZ = depth;

            wrapper.appendChild(el);
            bgContainer.appendChild(wrapper);
            parallaxLayers.push(wrapper);
        }

        // Smooth Animation Variables
        let targetX = 0,
            targetY = 0;
        let currentX = 0,
            currentY = 0;

        document.addEventListener("mousemove", (e) => {
            targetX = (e.clientX - window.innerWidth / 2) * 0.08;
            targetY = (e.clientY - window.innerHeight / 2) * 0.08;
        });

        function animate3D() {
            currentX += (targetX - currentX) * 0.05;
            currentY += (targetY - currentY) * 0.05;

            // Tilt the entire bakery container & scale slightly to prevent edge cutoff
            bgContainer.style.transform =
                `scale(1.1) rotateX(${-currentY * 0.4}deg) rotateY(${currentX * 0.4}deg)`;

            // Shift individual cakes based on their 3D depth to create parallax distance
            parallaxLayers.forEach((layer) => {
                const z = parseFloat(layer.dataset.depthZ);
                const moveX = currentX * (z / 50);
                const moveY = currentY * (z / 50);
                layer.style.transform = `translate3d(${moveX}px, ${moveY}px, ${z}px)`;
            });

            requestAnimationFrame(animate3D);
        }
        animate3D();
    }

    const savedTheme = localStorage.getItem('twins_theme') || 'dark';
    setTheme(savedTheme);
});
// SweetAlert2 Session Messages
const _sessionSuccess = document.querySelector('meta[name="session-success"]')?.content || null;
const _sessionError = document.querySelector('meta[name="session-error"]')?.content || null;

document.addEventListener('DOMContentLoaded', () => {
    // Optimasi LCP & Paint: Tunda pop-up SweetAlert2 otomatis agar elemen Largest Contentful Paint (LCP) halaman utama dirender terlebih dahulu
    setTimeout(() => {
        if (_sessionSuccess) {
            Swal.fire({
                title: 'Berhasil!',
                text: _sessionSuccess,
                icon: 'success',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--accent-purple)',
                timer: 3000,
                showConfirmButton: false
            });
        }

        if (_sessionError) {
            Swal.fire({
                title: 'Oops!',
                text: _sessionError,
                icon: 'error',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--accent-pink)',
            });
        }
    }, 600);
});

// --- DASHBOARD PREMIUM HEADER ANIMATION ---
document.addEventListener('DOMContentLoaded', () => {
    const globalLoader = document.getElementById('global-page-loader');
    if (globalLoader) {
        globalLoader.style.opacity = '0';
        globalLoader.style.visibility = 'hidden';
    }

    if (typeof gsap !== 'undefined') {
        gsap.set("#mainHeader", {
            y: -100,
            opacity: 0
        });
        gsap.to("#mainHeader", {
            y: 0,
            opacity: 1,
            duration: 1.2,
            ease: "expo.out",
            delay: 0.2
        });
    }
});
// Initial load for payment and history
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const redirectOrderId = urlParams.get('order_id');
    const redirectStatus = urlParams.get('status_code');

    if (redirectOrderId && redirectStatus) {
        Swal.fire({
            title: 'Memperbarui Status...',
            text: 'Sedang mensinkronkan pembayaran Anda.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        const syncUrl = document.querySelector('meta[name="sync-payment-url"]').content;
        fetch(syncUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ order_id: redirectOrderId })
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
            if (isAuthenticated) fetchHistoryFromServer();
        })
        .catch(err => {
            console.error('Auto-sync failed:', err);
            Swal.close();
            if (isAuthenticated) fetchHistoryFromServer();
        });
    } else {
        if (isAuthenticated) fetchHistoryFromServer();
    }

    // Buka riwayat jika ada flag auto-reload setelah checkout
    if (safeLocalStorage.getItem('open_history_on_load') === 'true') {
        safeLocalStorage.removeItem('open_history_on_load');
        setTimeout(() => { switchPage('history'); }, 100);
    }

    renderProducts();
});
async function fetchHistoryFromServer() {
    if (!isAuthenticated) return;
    const historyUrl = document.querySelector('meta[name="user-history-url"]').content;
    try {
        const response = await fetch(historyUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (response.ok) {
            const data = await response.json();
            historyData = data.history || [];
            savePersistence();
            renderHistory();
        }
    } catch (error) {
        console.error('Failed to fetch history:', error);
        const savedHistory = safeLocalStorage.getItem('twins_history');
        if (savedHistory) {
            try {
                historyData = JSON.parse(savedHistory);
            } catch (e) {}
        }
    }
}

function showLoaderAndNavigate(url) {
    const loader = document.getElementById('dashboard-transition-loader');
    if (loader) {
        loader.style.opacity = '1';
        loader.style.pointerEvents = 'auto';
    }
    setTimeout(() => {
        window.location.href = url;
    }, 50);
}
