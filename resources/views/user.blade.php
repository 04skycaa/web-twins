<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="session-success" content="{{ session('success') ?? '' }}">
    <meta name="session-error" content="{{ session('error') ?? '' }}">
    <meta name="auth-check" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="login-url" content="{{ route('login') }}">
    <meta name="outlet-address" content="{{ $outlet->alamat ?? 'Alamat outlet belum tersedia' }}">
    <meta name="store-hours" content="{{ $outlet->jam_buka ?? '' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="outlet-uuid" content="{{ $outlet->uuid }}">
    <meta name="delivery-address-store-url"
        content="{{ route('user.delivery-address.store', ['id' => $outlet->uuid]) }}">
    <meta name="checkout-token-url" content="{{ route('user.checkout.token', ['id' => $outlet->uuid]) }}">
    <meta name="user-history-url" content="{{ route('user.history.api', ['id' => $outlet->uuid]) }}">
    <meta name="sync-payment-url" content="{{ route('user.payment.sync', ['id' => $outlet->uuid]) }}">
    <meta name="midtrans-enabled"
        content="{{ config('services.midtrans.client_key') && config('services.midtrans.server_key') ? 'true' : 'false' }}">
    <meta name="persisted-delivery-preference" content="{{ json_encode($deliveryPreference ?? null) }}">
    <meta name="user-name" content="{{ optional(auth()->user())->name ?? '' }}">
    <meta name="user-phone" content="{{ optional(auth()->user())->no_hp ?? '' }}">
    <title>TWINS - Food Delivery Dashboard</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="dns-prefetch" href="https://nominatim.openstreetmap.org">
    <link rel="dns-prefetch" href="https://router.project-osrm.org">
    
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
    @if (config('services.midtrans.client_key'))
        <script
            src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}" defer></script>
    @endif


</head>
<script type="application/json" id="products-data">
    {!! json_encode($products) !!}
</script>
<script type="application/json" id="promos-data">
    {!! json_encode($activePromosList) !!}
</script>

<body id="body">

    <div class="animated-bg"></div>
    <div class="light-rays-container">
        <div class="god-ray ray1"></div>
        <div class="god-ray ray2"></div>
        <div class="god-ray ray3"></div>
        <div class="god-ray ray4"></div>
    </div>
    <header id="mainHeader">
        <div class="logo">
            <a href="{{ route('home') }}?skip_splash=1#outlet" class="back-btn-icon" title="Kembali ke Daftar Outlet">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="main-nav" id="mainNav">
            <a class="nav-link active" id="nav-home" onclick="switchPage('home')">Beranda</a>
            <a class="nav-link" id="nav-cat" onclick="scrollToCategory()">Kategori</a>
            <a class="nav-link" id="nav-history" onclick="switchPage('history')">Riwayat</a>
            <a class="nav-link" id="nav-chat" onclick="goToWhatsApp()">Chat</a>
        </nav>
        <div class="nav-btns">
            @auth
                <div class="user-premium-card desktop-only">
                    <span class="user-name-text">{{ Auth::user()->name }}</span>
                    
                    @if(auth()->user()->canAccessAdmin())
                        <a href="/dashboard" class="nav-action-btn" title="Dashboard">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" id="logout-form-card" style="display: none;">
                        @csrf
                    </form>
                    <button type="button" class="nav-action-btn logout-btn" title="Logout" onclick="document.getElementById('logout-form-card').submit();">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </div>

                <div class="mobile-user-drop mobile-only">
                    <button class="user-icon-btn" onclick="toggleUserMenu()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown-menu" id="userMenu">
                        <div class="user-menu-header" style="padding: 12px 16px; border-bottom: 1px solid var(--card-border); margin-bottom: 5px;">
                            <span style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-color);">{{ Auth::user()->name }}</span>
                            <span style="display: block; font-size: 0.75rem; color: var(--sub-text);">{{ Auth::user()->email }}</span>
                        </div>
                        @if(auth()->user()->canAccessAdmin())
                            <button onclick="location.href='/dashboard'">Dashboard</button>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" style="display: none;" id="logout-form-user-page-mob">
                            @csrf
                        </form>
                        <button onclick="document.getElementById('logout-form-user-page-mob').submit();" style="display: flex; align-items: center; color: #ef4444;">
                            Logout
                        </button>
                    </div>
                </div>
            @else
                <div class="mobile-user-drop">
                    <button class="user-icon-btn" onclick="toggleUserMenu()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown-menu" id="userMenu">
                        <button onclick="location.href='/login'">Login</button>
                        <button onclick="location.href='/register'">Register</button>
                    </div>
                </div>
            @endauth

            <div class="theme-dropdown">
                <button class="theme-btn" onclick="toggleThemeMenu()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    Tema
                </button>
                <div class="theme-dropdown-content" id="themeMenu">
                    <button onclick="setTheme('dark')" data-theme-val="dark">🌙 Dark</button>
                    <button onclick="setTheme('light')" data-theme-val="light">☀️ Light</button>
                    <button onclick="setTheme('twins')" data-theme-val="twins">🏮 Twins (Red)</button>
                    <button onclick="setTheme('neon')" data-theme-val="neon">🟣 Neon</button>
                    <button onclick="setTheme('ocean')" data-theme-val="ocean">🌊 Ocean</button>
                    <button onclick="setTheme('forest')" data-theme-val="forest">🍂 Autumn (Orange)</button>
                </div>
            </div>

            @guest
                <a href="{{ route('login') }}" class="btn-outline desktop-only" style="text-decoration: none;">Login</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-fill desktop-only" style="text-decoration: none;">Register</a>
                @endif
            @endguest
        </div>
    </header>


    <div class="mobile-cart-fab" id="mobileCartBtn" onclick="toggleBottomSheet()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <div class="cart-badge" id="cartBadge">0</div>
    </div>

    <div class="sheet-overlay" id="sheetOverlay" onclick="toggleBottomSheet()"></div>
    <div class="bottom-sheet" id="bottomSheet">
        <div class="handle"></div>
        <div id="mobileSheetContent" style="padding: 0 15px 30px 15px;">
            <!-- Pre-populated for mobile to avoid innerHTML copy issues -->
            <div class="white-card hidden address-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="font-size: 0.95rem;">Alamat Pengiriman</h4>
                    <a href="#" onclick="openAddressPopup(event)"
                        style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Ubah</a>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <span style="font-size: 1.2rem;">📍</span>
                    <div style="flex: 1;">
                        <p class="delivery-address-value" style="font-size: 0.85rem; font-weight: 600;">-</p>
                        <p class="delivery-address-note"
                            style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman
                            default Anda.</p>
                        <p class="delivery-contact-note"
                            style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4; margin-top: 4px;">
                            Penerima: - | No HP: -</p>
                    </div>
                </div>
            </div>

            <div class="white-card hidden discount-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Kode Promo</h4>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="promoInputMobile" placeholder="TWINS20"
                        style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                    <button onclick="applyPromo('mobile')"
                        style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Terapkan</button>
                </div>
                <p class="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
            </div>

            <div class="white-card hidden order-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 12px; border-radius: 15px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 12px; font-size: 0.85rem;">Menu Pesanan</h4>
                <div class="cart-items-container"></div>
                <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 12px 0;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--sub-text);">Harga Awal</span>
                        <span class="originalSubtotalDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--sub-text);">Diskon</span>
                        <span class="totalDiscountDisplay" style="font-size: 0.8rem; font-weight: 700; color: #10b981;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--sub-text);">Harga Setelah Diskon</span>
                        <span class="discountedSubtotalDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--sub-text);">Ongkir (sementara)</span>
                        <span class="shippingFeeDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; font-size: 0.8rem;">Total</span>
                        <span class="totalPriceDisplay"
                            style="font-size: 0.8rem; font-weight: 700; color: var(--orange-brand);"><span
                                style="font-size: 0.8em;">Rp</span> 0</span>
                    </div>
                </div>
                <button class="btn-fill checkout-btn" onclick="checkout()"
                    style="width: 100%; margin-top: 12px; padding: 10px; font-size: 0.9rem;">Checkout</button>
            </div>
        </div>
    </div>

    <div class="container" id=
    "mainContainer">
        <main class="main-content anim-fade-up" id="homePage">
            <div class="promo-banner float-hover">
                <span class="badge" style="margin-bottom: 10px;">Outlet TWINS</span>
                <h1>{{ $outlet->nama }}</h1>
                <p>📍 {{ $outlet->alamat }}</p>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 10px;">
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 0; padding: 8px 14px; text-transform: none; letter-spacing: 0.5px; font-size: 12px;">
                        <span style="font-size: 14px; line-height: 1;">🕒</span> <span>{{ $outlet->jam_buka }}</span>
                    </span>
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 0; padding: 8px 14px; text-transform: none; letter-spacing: 0.5px; font-size: 12px;">
                        <span style="font-size: 14px; line-height: 1;">⭐</span> <span>{{ number_format($outlet->rating, 1) }}</span>
                    </span>
                </div>
            </div>

            @if (count($discounts) > 0)
                <div class="discounts-container anim-fade-up" style="margin-top: 30px; max-width: 100%; min-width: 0;">
                    <h3 style="margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
                        <iconify-icon icon="solar:ticket-sale-bold-duotone"
                            style="color: #f59e0b; font-size: 28px;"></iconify-icon>
                        Penawaran Diskon Hari Ini
                    </h3>
                    <div
                        style="display: flex; gap: 15px; overflow-x: auto; overflow-y: visible; padding-bottom: 20px; scrollbar-width: none; -ms-overflow-style: none; align-items: stretch; max-width: 100%; min-width: 0;">
                        @php $shownProducts = []; @endphp
                        @foreach ($discounts as $discount)
                            @foreach ($discount->products as $p)
                                @if (!in_array($p->uuid, $shownProducts))
                                    @php
                                        $shownProducts[] = $p->uuid;
                                        $originalPrice = (int) $p->harga_jual;
                                        $nilaiDiskon = (int) ($p->pivot->nilai_diskon ?? $discount->nilai);
                                        
                                        // Smart Tipe Diskon Detector
                                        $tipeDiskon = $nilaiDiskon <= 100 ? 'persen' : 'nominal';
                                        
                                        $newPrice =
                                            $tipeDiskon == 'persen'
                                                ? $originalPrice * (1 - $nilaiDiskon / 100)
                                                : $originalPrice - $nilaiDiskon;
                                        if ($newPrice < 0) {
                                            $newPrice = 0;
                                        }
                                    @endphp
                                    @php
                                        $currentStok = $stockMap[$p->uuid] ?? 0;
                                        $isOutOfStock = $currentStok <= 0;
                                    @endphp
                                    <div class="discounted-item-vertical {{ $isOutOfStock ? 'out-of-stock' : '' }}"
                                        style="opacity: {{ $isOutOfStock ? '0.6' : '1' }};">
                                        <div
                                            style="width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: white; position: relative;">
                                            <img src="{{ \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url) }}"
                                                class="{{ $isOutOfStock ? 'img-out-of-stock' : '' }}"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                                crossorigin="anonymous"
                                                referrerpolicy="no-referrer-when-downgrade">
                                            <div
                                                style="position: absolute; top: 8px; left: 8px; background: #ff4d4d; color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; z-index: 3;">
                                                -{{ $tipeDiskon == 'persen' ? $nilaiDiskon . '%' : 'Rp ' . number_format($nilaiDiskon, 0, ',', '.') }}
                                            </div>
                                            @if ($isOutOfStock)
                                                <div
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; z-index: 4;">
                                                    HABIS</div>
                                            @endif
                                        </div>

                                        <div
                                            style="padding: 10px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                            <h5
                                                class="product-name-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                                {{ $p->nama_produk }}
                                            </h5>
                                            <div
                                                style="margin-top: 8px; display: flex; justify-content: space-between; align-items: flex-end;">
                                                <div>
                                                    <div
                                                        style="font-size: 0.7rem; text-decoration: line-through; color: #777; margin-bottom: 2px;">
                                                        Rp{{ number_format($originalPrice, 0, ',', '.') }}
                                                    </div>
                                                    <div
                                                        class="product-new-price-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                                        Rp{{ number_format($newPrice, 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                <button
                                                    class="discount-add-btn {{ $isOutOfStock ? 'out-of-stock btn-oos' : 'btn-available' }}"
                                                    data-name="{{ $p->nama_produk }}"
                                                    data-price="{{ $newPrice }}"
                                                    data-stock="{{ $currentStok }}" onclick="addToCartFromEl(this)">
                                                    <svg width="20" height="20" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12 5V19M5 12H19" stroke="white" stroke-width="3"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            <section id="categorySection" class="search-filter-section">
                <div class="search-row">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="searchInput" placeholder="Cari menu favoritmu..."
                            oninput="handleSearch()">
                    </div>
                    <button class="filter-btn" onclick="toggleFilterPanel()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Filter & Sort
                    </button>
                </div>

                <!-- Wadah Badge Filter Aktif -->
                <div id="activeFilters" class="active-filters-container"></div>

                <!-- Advanced Filter Panel (Hidden by default) -->
                <div id="filterPanel" class="filter-panel hidden">
                    <div class="filter-content">
                        <div class="filter-section">
                            <h5>Kategori Produk</h5>
                            <div class="category-grid">
                                <label class="check-container">Semua Kategori
                                    <input type="checkbox" id="check-all" checked
                                        onchange="toggleAllCategories(this)">
                                    <span class="checkmark"></span>
                                </label>
                                @foreach ($categories as $category)
                                    <label class="check-container">{{ $category['name'] }}
                                        <input type="checkbox" class="cat-check" value="{{ $category['id'] }}"
                                            data-name="{{ $category['name'] }}">
                                        <span class="checkmark"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                            <div class="filter-section" style="flex: 1; min-width: 250px;">
                                <h5>Urutkan Harga</h5>
                                <select id="priceSort" class="filter-select">
                                    <option value="default">Default</option>
                                    <option value="low-high">Harga: Terendah ke Tertinggi</option>
                                    <option value="high-low">Harga: Tertinggi ke Terendah</option>
                                </select>
                            </div>
                            <div style="padding-bottom: 5px;">
                                <button onclick="applyFilters()" class="btn-fill"
                                    style="padding: 12px 30px; border-radius: 12px;">Terapkan Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="food-grid" id="productGrid"></div>
            </section>

            <!-- STORE REVIEWS SECTION -->
            <section class="reviews-section anim-fade-up">
                <div class="reviews-header">
                    <h3>Ulasan & Rating Toko</h3>
                    <div class="avg-stats">
                        <span class="avg-val">{{ number_format($outlet->rating, 1) }}</span>
                        <div class="stars-gold">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($outlet->rating))
                                    ★
                                @elseif ($i == ceil($outlet->rating) && $outlet->rating - floor($outlet->rating) > 0)
                                    {{-- Bisa pakai ikon half star jika ada, tapi sementara kita pakai bintang penuh/kosong --}}
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                @auth
                    <div class="review-form-card">
                        <h4>Bagaimana menurutmu tentang toko ini?</h4>
                        <form action="{{ route('store.review.store', $outlet->uuid) }}" method="POST">
                            @csrf
                            <div class="rating-selector">
                                <input type="radio" name="rating" value="5" id="star5"><label
                                    for="star5">★</label>
                                <input type="radio" name="rating" value="4" id="star4"><label
                                    for="star4">★</label>
                                <input type="radio" name="rating" value="3" id="star3"><label
                                    for="star3">★</label>
                                <input type="radio" name="rating" value="2" id="star2"><label
                                    for="star2">★</label>
                                <input type="radio" name="rating" value="1" id="star1" required><label
                                    for="star1">★</label>
                            </div>
                            <textarea name="comment" placeholder="Berikan komentar Anda..." rows="3"></textarea>
                            <button type="submit" class="btn-fill" style="margin-top: 15px; width: 100%;">Kirim
                                Ulasan</button>
                        </form>
                    </div>
                @else
                    <div class="login-prompt-card">
                        <p>Silakan <a href="{{ route('login') }}">Login</a> untuk memberikan ulasan.</p>
                    </div>
                @endauth

                <!-- Reviews List -->
                <div class="reviews-list">
                    @forelse($reviews as $review)
                        <div class="review-item-card">
                            <div class="review-top">
                                <div class="user-meta">
                                    <div class="user-avatar-sm">
                                        {{ strtoupper(substr($review->user->username, 0, 1)) }}</div>
                                    <strong>{{ $review->user->username }}</strong>
                                </div>
                                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="review-rating">
                                @for ($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $review->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                            <p class="review-comment">{{ $review->comment ?? 'Hanya memberikan rating.' }}</p>
                        </div>
                    @empty
                        <p class="empty-msg">Belum ada ulasan untuk toko ini.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <main class="main-content hidden" id="historyPage">
            <h2 style="margin-bottom: 25px;">Riwayat Transaksi</h2>
            <div id="historyList">
                <p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>
            </div>
        </main>

        <aside class="sidebar anim-fade-up" id="sidebarArea">
            <div id="sidebarContentWrapper">
                <div class="white-card hidden address-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="font-size: 0.95rem;">Alamat Pengiriman</h4>
                        <a href="#" onclick="openAddressPopup(event)"
                            style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Ubah</a>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">📍</span>
                        <div>
                            <p class="delivery-address-value" style="font-size: 0.85rem; font-weight: 600;">-</p>
                            <p class="delivery-address-note"
                                style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman
                                default Anda.</p>
                            <p class="delivery-contact-note"
                                style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4; margin-top: 4px;">
                                Penerima: - | No HP: -</p>
                        </div>
                    </div>
                </div>

                <div class="white-card hidden discount-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Kode Promo</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="promoInput" placeholder="TWINS20"
                            style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                        <button onclick="applyPromo()"
                            style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Terapkan</button>
                    </div>
                    <p id="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
                </div>

                <div class="white-card hidden order-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 12px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.85rem;">Menu Pesanan</h4>
                    <div class="cart-items-container"></div>
                    <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 12px 0;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--sub-text);">Harga Awal</span>
                            <span class="originalSubtotalDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--sub-text);">Diskon</span>
                            <span class="totalDiscountDisplay" style="font-size: 0.8rem; font-weight: 700; color: #10b981;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--sub-text);">Harga Setelah Diskon</span>
                            <span class="discountedSubtotalDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--sub-text);">Ongkir (sementara)</span>
                            <span class="shippingFeeDisplay" style="font-size: 0.8rem; font-weight: 700;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; font-size: 0.8rem;">Total</span>
                            <span class="totalPriceDisplay"
                                style="font-size: 0.8rem; font-weight: 700; color: var(--orange-brand);"><span
                                    style="font-size: 0.8em;">Rp</span> 0</span>
                        </div>
                    </div>
                    <button class="btn-fill checkout-btn" onclick="checkout()"
                        style="width: 100%; margin-top: 12px; padding: 10px; font-size: 0.9rem;">Checkout</button>
                </div>
            </div>
        </aside>
    </div>

    <nav class="mobile-nav">
        <div class="mob-nav-item active" id="mob-home" onclick="switchPage('home')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Beranda</span>
        </div>
        <div class="mob-nav-item" id="mob-cat" onclick="scrollToCategory()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Kategori</span>
        </div>
        <div class="mob-nav-item" id="mob-history" onclick="switchPage('history')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Riwayat</span>
        </div>
        <div class="mob-nav-item" onclick="goToWhatsApp()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path
                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                </path>
            </svg>
            <span>Chat</span>
        </div>
    </nav>

    <script>
        window.TwinsConfig = {
            leaflet: {
                iconRetinaUrl: "{{ asset('vendor/leaflet/images/marker-icon-2x.png') }}",
                iconUrl: "{{ asset('vendor/leaflet/images/marker-icon.png') }}",
                shadowUrl: "{{ asset('vendor/leaflet/images/marker-shadow.png') }}"
            }
        };
    </script>
    <script src="{{ asset('js/user.js') }}?v={{ time() }}" defer></script>

    <!-- Beautiful Premium Transition Loader -->
    <div id="dashboard-transition-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="position: relative; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <div style="position: absolute; width: 80px; height: 80px; border-radius: 50%; border: 4px solid rgba(139, 92, 246, 0.1); border-top-color: #8b5cf6; animation: spin-loader 1s linear infinite;"></div>
            <div style="position: absolute; width: 60px; height: 60px; border-radius: 50%; border: 4px solid rgba(236, 72, 153, 0.1); border-bottom-color: #ec4899; animation: spin-loader-reverse 1.2s linear infinite;"></div>
            <img src="{{ asset('images/logo.png') }}" alt="Twins Logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
        </div>
        <div style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #f8fafc; letter-spacing: 1px; text-transform: uppercase;">Memuat Dashboard...</div>
        <div style="font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #94a3b8; margin-top: 5px;">Mempersiapkan data administrasi Anda</div>
    </div>
    
    <style>
        @keyframes spin-loader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes spin-loader-reverse {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }
    </style>

    <nav class="mobile-nav">
        <div id="mob-home" class="mob-nav-item active" onclick="switchPage('home')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>

        <div id="mob-cat" class="mob-nav-item" onclick="scrollToCategory()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
            <span>Kategori</span>
        </div>

        <div id="mob-history" class="mob-nav-item" onclick="switchPage('history')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Riwayat</span>
        </div>

        <div id="mob-chat" class="mob-nav-item" onclick="goToWhatsApp()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            <span>Chat</span>
        </div>
    </nav>
</body>

</html>
