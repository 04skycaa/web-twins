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
    <meta name="user-name" content="{{ optional(auth()->user())->name ?? '' }}">
    <title>TWINS - Food Delivery Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        /* CSS to replace inline hover logic and satisfy IDE */
        .discounted-item-vertical {
            min-width: 135px;
            width: 135px;
            background: #1a1625;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            position: relative;
        }
        .discounted-item-vertical:hover {
            transform: translateY(-5px);
            border-color: var(--accent-pink);
        }
        .img-out-of-stock {
            filter: grayscale(1) opacity(0.5);
        }
        .text-muted-stock {
            color: #777 !important;
        }
        .product-name-discount {
            font-size: 0.75rem; margin: 0; color: white; line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600;
        }
        .btn-oos { background: #ef4444 !important; }
        .btn-available { background: #0ea5e9 !important; }
        .product-new-price-discount {
            font-size: 0.95rem; font-weight: 800; color: #00c853;
        }
        .discount-add-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
        }
        .discount-add-btn:not(.out-of-stock):hover {
            background: #0284c7 !important;
            transform: scale(1.05);
        }
        @media (max-width: 600px) {
            .discounted-item-vertical {
                min-width: 110px !important;
                width: 110px !important;
            }
            .product-name-discount {
                font-size: 0.65rem !important;
            }
            .product-new-price-discount {
                font-size: 0.8rem !important;
            }
            .wholesale-badge {
                font-size: 0.45rem !important;
                padding: 2px 4px !important;
                bottom: 4px !important;
                left: 4px !important;
                border-radius: 4px !important;
            }
            .wholesale-badge iconify-icon {
                font-size: 0.6rem !important;
            }
        }
    </style>
</head>
<script type="application/json" id="products-data">
    {!! json_encode($products) !!}
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
            <div class="mobile-user-drop">
                <button class="user-icon-btn" onclick="toggleUserMenu()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
                <div class="user-dropdown-menu" id="userMenu">
                    <button onclick="location.href='/login'">Login</button>
                    <button onclick="location.href='/register'">Register</button>
                </div>
            </div>

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
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="user-profile-link">
                        <div class="user-initial">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="user-name">{{ Auth::user()->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline" style="text-decoration: none;">Login</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-fill" style="text-decoration: none;">Register</a>
                    @endif
                @endauth
            @endif
        </div>
    </header>

    <div class="mobile-cart-fab" id="mobileCartBtn" onclick="toggleBottomSheet()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
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
                    <h4 style="font-size: 0.95rem;">Delivery Address</h4>
                    <a href="#" onclick="openAddressPopup(event)" style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Change</a>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <span style="font-size: 1.2rem;">📍</span>
                    <div style="flex: 1;">
                        <p class="delivery-address-value" style="font-size: 0.85rem; font-weight: 600;">-</p>
                        <p class="delivery-address-note" style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman default Anda.</p>
                        <p class="delivery-contact-note" style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4; margin-top: 4px;">Penerima: - | No HP: -</p>
                    </div>
                </div>
            </div>

            <div class="white-card hidden order-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 15px; font-size: 0.95rem;">Order Menu</h4>
                <div class="cart-items-container"></div>
                <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 15px 0;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600;">Total</span>
                        <span class="totalPriceDisplay" style="font-size: 1.2rem; font-weight: 800; color: var(--orange-brand);">Rp 0</span>
                    </div>
                </div>
                <button class="btn-fill" onclick="checkout()" style="width: 100%; margin-top: 15px; padding: 12px;">Checkout</button>
            </div>

            <div class="white-card hidden discount-section"
                style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px;">
                <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Promo Code</h4>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="promoInputMobile" placeholder="TWINS20"
                        style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                    <button onclick="applyPromo('mobile')"
                        style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Apply</button>
                </div>
                <p class="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
            </div>
        </div>
    </div>

    <div class="container" id=
    "mainContainer">
        <main class="main-content anim-fade-up" id="homePage">
            <div class="promo-banner float-hover" style="min-height: 280px; height: auto; padding: 40px;">
                <span class="badge" style="margin-bottom: 10px;">Outlet TWINS</span>
                <h1 style="margin: 5px 0 15px 0;">{{ $outlet->nama }}</h1>
                <p style="font-size: 1rem; opacity: 0.9; margin-bottom: 20px;">📍 {{ $outlet->alamat }}</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">🕒
                        {{ $outlet->jam_buka }}</span>
                    <span class="badge"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">⭐
                        {{ number_format($outlet->rating, 1) }}</span>
                </div>
            </div>

            @if(count($discounts) > 0)
            <div class="discounts-container anim-fade-up" style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
                    <iconify-icon icon="solar:ticket-sale-bold-duotone" style="color: #f59e0b; font-size: 28px;"></iconify-icon>
                    Penawaran Diskon Hari Ini
                </h3>
                <div style="display: flex; gap: 15px; overflow-x: auto; padding-bottom: 20px; scrollbar-width: none; -ms-overflow-style: none;">
                    @php $shownProducts = []; @endphp
                    @foreach($discounts as $discount)
                        @foreach($discount->products as $p)
                            @if(!in_array($p->uuid, $shownProducts))
                                @php
                                    $shownProducts[] = $p->uuid;
                                    $originalPrice = (int) $p->harga_jual;
                                    $tipeDiskon = $p->pivot->tipe_diskon ?? $discount->tipe;
                                    $nilaiDiskon = (int) ($p->pivot->nilai_diskon ?? $discount->nilai);
                                    $newPrice = ($tipeDiskon == 'persen' || $tipeDiskon == 'Promo')
                                        ? $originalPrice * (1 - ($nilaiDiskon / 100))
                                        : $originalPrice - $nilaiDiskon;
                                    if($newPrice < 0) $newPrice = 0;
                                @endphp
                                @php
                                    $currentStok = $stockMap[$p->uuid] ?? 0;
                                    $isOutOfStock = $currentStok <= 0;
                                @endphp
                                <div class="discounted-item-vertical {{ $isOutOfStock ? 'out-of-stock' : '' }}"
                                     style="opacity: {{ $isOutOfStock ? '0.6' : '1' }};">
                                    <div style="width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: white; position: relative;">
                                        <img src="{{ \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url) }}"
                                             class="{{ $isOutOfStock ? 'img-out-of-stock' : '' }}"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                        <div style="position: absolute; top: 8px; left: 8px; background: #ff4d4d; color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; z-index: 3;">
                                            -{{ $tipeDiskon == 'persen' ? $nilaiDiskon.'%' : 'Rp'.number_format($nilaiDiskon/1000, 0).'k' }}
                                        </div>
                                        @if($isOutOfStock)
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; z-index: 4;">HABIS</div>
                                        @endif
                                    </div>

                                    <div style="padding: 10px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                        <h5 class="product-name-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                            {{ $p->nama_produk }}
                                        </h5>
                                        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: flex-end;">
                                            <div>
                                                <div style="font-size: 0.7rem; text-decoration: line-through; color: #777; margin-bottom: 2px;">
                                                    Rp{{ number_format($originalPrice, 0, ',', '.') }}
                                                </div>
                                                <div class="product-new-price-discount {{ $isOutOfStock ? 'text-muted-stock' : '' }}">
                                                    Rp{{ number_format($newPrice, 0, ',', '.') }}
                                                </div>
                                            </div>

                                            <button class="discount-add-btn {{ $isOutOfStock ? 'out-of-stock btn-oos' : 'btn-available' }}"
                                                    data-name="{{ $p->nama_produk }}"
                                                    data-price="{{ $newPrice }}"
                                                    data-stock="{{ $currentStok }}"
                                                    onclick="addToCartFromEl(this)">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 5V19M5 12H19" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
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
                                    <input type="checkbox" id="check-all" checked onchange="toggleAllCategories(this)">
                                    <span class="checkmark"></span>
                                </label>
                                @foreach ($categories as $category)
                                    <label class="check-container">{{ $category['name'] }}
                                        <input type="checkbox" class="cat-check" value="{{ $category['id'] }}" data-name="{{ $category['name'] }}">
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
                                <button onclick="applyFilters()" class="btn-fill" style="padding: 12px 30px; border-radius: 12px;">Terapkan Filter</button>
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
                        <span class="stars">★★★★★</span>
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
                        <h4 style="font-size: 0.95rem;">Delivery Address</h4>
                        <a href="#" onclick="openAddressPopup(event)"
                            style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Change</a>
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

                <div class="white-card hidden order-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 15px; font-size: 0.95rem;">Order Menu</h4>
                    <div class="cart-items-container"></div>
                    <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 15px 0;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Total</span>
                            <span class="totalPriceDisplay"
                                style="font-size: 1.2rem; font-weight: 800; color: var(--orange-brand);">Rp 0</span>
                        </div>
                    </div>
                    <button class="btn-fill" onclick="checkout()"
                        style="width: 100%; margin-top: 15px; padding: 12px;">Checkout</button>
                </div>

                <div class="white-card hidden discount-section"
                    style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Promo Code</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="promoInput" placeholder="TWINS20"
                            style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                        <button onclick="applyPromo()"
                            style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Apply</button>
                    </div>
                    <p id="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
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

        const products = JSON.parse(document.getElementById('products-data').textContent);

        let cart = [];
        let historyData = [];
        let discountPercent = 0;
        const isAuthenticated = document.querySelector('meta[name="auth-check"]').content === 'true';
        const loginUrl = document.querySelector('meta[name="login-url"]').content;

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
        const outletAddress = document.querySelector('meta[name="outlet-address"]')?.content || 'Alamat outlet belum tersedia';
        let deliveryAddress = document.querySelector('meta[name="outlet-address"]')?.content || '';
        let deliveryCoordinates = null;
        let deliveryContactName = document.querySelector('meta[name="user-name"]')?.content || '';
        let deliveryPhone = '';
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
                el.textContent = `Penerima: ${nameText} | No HP: ${phoneText}`;
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
            let geocodeDebounceTimer = null;
            let geocodeRequestToken = 0;

            const popupHtml = `
                <div style="text-align:left;">
                    <div style="border:1px solid #374151; border-radius:12px; padding:10px; background:#111827; margin-bottom:10px;">
                        <p style="font-size:11px; letter-spacing:0.02em; color:#9ca3af; margin:0 0 6px 0; font-weight:700;">ROUTE TRACKING</p>
                        <p style="font-size:12px; color:#f3f4f6; margin:0; line-height:1.45;" id="routeTrackingSummary">Menyiapkan rute dari outlet ke alamat tujuan...</p>
                    </div>

                    <label style="display:block; margin-bottom:6px; font-size:12px; color:#9ca3af;">Alamat Saat Ini (Outlet)</label>
                    <div style="border:1px solid #374151; border-radius:10px; padding:10px; background:#1f2937; color:#f3f4f6; font-size:13px; margin-bottom:10px; line-height:1.4;">${outletAddress}</div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                        <div>
                            <label for="recipientNameInput" style="display:block; margin-bottom:6px; font-size:12px; color:#9ca3af;">Nama Penerima</label>
                            <input id="recipientNameInput" type="text" style="width:100%; border:1px solid #374151; border-radius:10px; padding:10px; background:#111827; color:#f9fafb; font-size:13px;" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <label for="recipientPhoneInput" style="display:block; margin-bottom:6px; font-size:12px; color:#9ca3af;">No HP</label>
                            <input id="recipientPhoneInput" type="text" inputmode="tel" style="width:100%; border:1px solid #374151; border-radius:10px; padding:10px; background:#111827; color:#f9fafb; font-size:13px;" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <label for="manualAddressInput" style="display:block; margin-bottom:6px; font-size:12px; color:#9ca3af;">Alamat Lengkap</label>
                    <textarea id="manualAddressInput" rows="4" style="width:100%; border:1px solid #374151; border-radius:10px; padding:10px; resize:vertical; background:#111827; color:#f9fafb; font-size:13px; margin-bottom:10px;"></textarea>
                    <p style="font-size:12px; margin:0 0 8px 0; color:#9ca3af;">Input alamat akan menggeser peta, dan klik peta akan memperbarui teks alamat.</p>
                    <div id="addressMapCanvas" style="height:260px; border-radius:12px; overflow:hidden;"></div>
                    <div id="mapAddressResult" style="margin-top:8px; font-size:12px; color:#d1d5db; line-height:1.4;"></div>
                </div>
            `;

            Swal.fire({
                title: 'Ubah Alamat Pengiriman',
                html: popupHtml,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: 'var(--orange-brand)',
                width: 650,
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const htmlContainer = Swal.getHtmlContainer();
                    const recipientNameInput = popup.querySelector('#recipientNameInput');
                    const recipientPhoneInput = popup.querySelector('#recipientPhoneInput');
                    const manualAddressInput = popup.querySelector('#manualAddressInput');
                    const mapAddressResult = popup.querySelector('#mapAddressResult');
                    const routeTrackingSummary = popup.querySelector('#routeTrackingSummary');

                    if (htmlContainer) {
                        htmlContainer.style.maxHeight = '62vh';
                        htmlContainer.style.overflowY = 'auto';
                        htmlContainer.style.paddingRight = '4px';
                    }
                    if (popup) {
                        popup.style.maxHeight = '92vh';
                    }

                    recipientNameInput.value = (deliveryContactName || '').trim();
                    recipientPhoneInput.value = (deliveryPhone || '').trim();
                    manualAddressInput.value = (deliveryAddress || '').trim();

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
                                `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(outletAddress)}`
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
                                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`
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
                                `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`
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

                        const initialLatLng = selectedLatLng ? [selectedLatLng.lat, selectedLatLng.lng] : [-
                            6.200000, 106.816666
                        ];
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

                    initMap();
                    if (popupMap) {
                        setTimeout(() => popupMap.invalidateSize(), 100);
                    }
                },
                preConfirm: () => {
                    const popup = Swal.getPopup();
                    const recipientNameInput = popup.querySelector('#recipientNameInput');
                    const recipientPhoneInput = popup.querySelector('#recipientPhoneInput');
                    const manualAddressInput = popup.querySelector('#manualAddressInput');
                    const recipientName = (recipientNameInput.value || '').trim();
                    const recipientPhone = (recipientPhoneInput.value || '').trim();
                    const manualAddress = (manualAddressInput.value || '').trim();

                    if (!recipientName) {
                        Swal.showValidationMessage('Nama penerima wajib diisi.');
                        return false;
                    }

                    if (!recipientPhone) {
                        Swal.showValidationMessage('No HP wajib diisi.');
                        return false;
                    }

                    if (!/^\+?[0-9\s-]{8,20}$/.test(recipientPhone)) {
                        Swal.showValidationMessage('Format No HP tidak valid.');
                        return false;
                    }

                    if (!manualAddress) {
                        Swal.showValidationMessage('Alamat wajib diisi.');
                        return false;
                    }

                    return {
                        recipientName,
                        recipientPhone,
                        address: manualAddress,
                        coordinates: selectedLatLng ? {
                            lat: selectedLatLng.lat,
                            lng: selectedLatLng.lng
                        } : null
                    };
                }
            }).then((result) => {
                if (!result.isConfirmed || !result.value) return;

                deliveryContactName = result.value.recipientName;
                deliveryPhone = result.value.recipientPhone;
                deliveryAddress = result.value.address;
                deliveryCoordinates = result.value.coordinates;
                updateDeliveryAddressUI();

                Swal.fire({
                    icon: 'success',
                    title: 'Alamat berhasil diperbarui',
                    timer: 1400,
                    showConfirmButton: false,
                    background: 'var(--bg-color)',
                    color: 'var(--text-color)'
                });
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
                const matchesCategory = isAllChecked || checkedCats.length === 0 || checkedCats.includes(p.category_id);
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
                emptyMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 60px; color: var(--sub-text); font-size: 1.1rem;';
                emptyMsg.innerHTML = '<div style="margin-bottom: 15px; font-size: 3rem;">🔍</div>Item tidak ditemukan.';
                productGrid.appendChild(emptyMsg);
                return;
            }

            filtered.forEach(product => {
                const isOutOfStock = product.stok <= 0;
                const card = document.createElement('div');
                card.className = `food-card anim-zoom-in ${isOutOfStock ? 'out-of-stock' : ''}`;

                card.innerHTML = `
                    <div style="width: 100%; aspect-ratio: 1/1; overflow: hidden; border-radius: 18px; margin-bottom: 15px; position: relative; background: #fff;">
                        <img src="${product.img}" class="food-img" style="filter: ${isOutOfStock ? 'grayscale(1) opacity(0.6)' : 'none'}">

                        ${product.is_discount && !isOutOfStock ? `
                            <div style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; z-index: 2; box-shadow: 0 4px 10px rgba(239,68,68,0.3);">
                                -${product.discount_label}
                            </div>
                        ` : ''}

                        ${product.price_levels && product.price_levels.length > 0 && !isOutOfStock ? `
                            <div onclick="showWholesaleInfo('${product.id}')" class="wholesale-badge" style="position: absolute; bottom: 10px; left: 10px; background: #FFD600; color: #000; padding: 4px 8px; border-radius: 8px; font-size: 0.65rem; font-weight: 800; z-index: 2; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(255,214,0,0.3);">
                                <iconify-icon icon="solar:tag-price-bold-duotone"></iconify-icon>
                                Beli ${Math.min(...product.price_levels.map(l => l.jmlh))}+ Lebih Murah
                            </div>
                        ` : ''}

                        ${isOutOfStock ? `
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #ef4444; color: white; padding: 6px 14px; border-radius: 10px; font-size: 0.8rem; font-weight: 800; z-index: 2; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">HABIS</div>
                        ` : ''}
                    </div>
                    <h4 style="font-size: 0.9rem; color: var(--text-color); font-weight: 700; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.2; height: 2.4em;">${product.name}</h4>

                    ${!isOutOfStock ? `
                        <p style="color: #10b981; font-size: 0.85rem; font-weight: 600; margin-bottom: 12px;">Stok: ${product.stok}</p>
                    ` : '<div style="height: 12px; margin-bottom: 12px;"></div>'}

                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;">
                        <div>
                            ${product.is_discount && !isOutOfStock ? `
                                <span style="display: block; color: var(--sub-text); text-decoration: line-through; font-size: 0.8rem; margin-bottom: -2px;">
                                    ${formatRupiah(product.original_price)}
                                </span>
                            ` : ''}
                            <span style="font-weight: 800; color: ${isOutOfStock ? 'var(--sub-text)' : 'var(--orange-brand)'}; font-size: 1.05rem;">
                                ${formatRupiah(product.price)}
                            </span>
                        </div>
                        <button class="add-btn"
                                data-name="${product.name}"
                                data-price="${product.price}"
                                data-stock="${product.stok}"
                                onclick="addToCartFromEl(this)"
                                style="width: 38px; height: 38px; border-radius: 12px; background: ${isOutOfStock ? 'rgba(255,255,255,0.1)' : 'var(--btn-grad)'}; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: ${isOutOfStock ? 'none' : 'var(--glow)'};">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                `;
                productGrid.appendChild(card);
            });
        }

        function handleSearch() {
            renderProducts();
        }

        function addToCartFromEl(el) {
            const name = el.getAttribute('data-name');
            const price = parseFloat(el.getAttribute('data-price'));
            const stock = parseInt(el.getAttribute('data-stock'));

            if (stock <= 0) {
                Swal.fire('Opps!', 'Stok produk ini sedang habis.', 'error');
                return;
            }
            addToCart(name, price);
        }

        function addToCart(name, price) {
            // Temukan info stok asli dari array products
            const productInfo = products.find(p => p.name === name);
            if (productInfo && productInfo.stok <= 0) {
                Swal.fire('Maaf!', 'Stok barang ini sudah habis.', 'error');
                return;
            }

            const existingItem = cart.find(item => item.name === name);
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
            const isMobile = window.innerWidth <= 1024;
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
            let subtotal = 0;
            const cartHtmlItems = cart.map((item, index) => {
                const pInfo = products.find(p => p.name === item.name);
                let displayPrice = item.price;
                if (pInfo && pInfo.price_levels && pInfo.price_levels.length > 0) {
                    const levels = [...pInfo.price_levels].sort((a, b) => b.jmlh - a.jmlh);
                    const appliedLevel = levels.find(l => item.qty >= l.jmlh);
                    if (appliedLevel) displayPrice = appliedLevel.harga;
                }
                subtotal += (displayPrice * item.qty);
                
                return `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;">📦</div>
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
                            <span style="color: var(--orange-brand); font-weight: 700; font-size: 0.85rem;">${formatRupiah(displayPrice * item.qty)}</span>
                            ${displayPrice < item.price ? `<span style="font-size: 0.65rem; color: #10b981; font-weight: 700;">Hemat Grosir!</span>` : ''}
                            <button class="delete-item-btn" onclick="removeFromCart(${index})">🗑️</button>
                        </div>
                    </div>
                `;
            }).join('');

            let finalTotal = subtotal > 0 ? subtotal * (1 - discountPercent) : 0;
            const formattedTotal = formatRupiah(finalTotal);

            document.querySelectorAll('.cart-items-container').forEach(container => {
                container.innerHTML = cartHtmlItems;
            });
            document.querySelectorAll('.totalPriceDisplay').forEach(el => {
                el.innerText = formattedTotal;
            });

            if (isMobile) {
                const sheetContent = document.getElementById('mobileSheetContent');
                if (sheetContent) {
                    sheetContent.querySelectorAll('.cart-items-container').forEach(c => c.innerHTML = cartHtmlItems);
                    sheetContent.querySelectorAll('.totalPriceDisplay').forEach(el => el.innerText = formattedTotal);
                }
            }

            updateDeliveryAddressUI();
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

        function applyPromo() {
            const input = document.getElementById('promoInput').value.trim().toUpperCase();
            const message = document.getElementById('promoMessage');
            if (input === 'TWINS20') {
                discountPercent = 0.20;
                message.innerText = "Promo TWINS20 applied! (20% Off)";
                message.style.color = "#10b981";
            } else {
                discountPercent = 0;
                message.innerText = input === "" ? "" : "Invalid promo code.";
                message.style.color = "#ef4444";
            }
            message.style.display = 'block';
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
                renderHistory();
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
            window.open(`https://wa.me/6281234567890?text=Halo TWINS!`, '_blank');
        }

        function checkout() {
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

            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            // Biaya Service dihapus dari total akhir checkout
            const total = subtotal * (1 - discountPercent);
            historyData.unshift({
                id: Date.now(),
                date: new Date().toLocaleString('id-ID'),
                items: [...cart],
                total: total,
                recipient_name: deliveryContactName,
                recipient_phone: deliveryPhone,
                address: deliveryAddress,
                coordinates: deliveryCoordinates ? {
                    lat: deliveryCoordinates.lat,
                    lng: deliveryCoordinates.lng
                } : null
            });
            cart = [];
            discountPercent = 0;
            renderCart();
            switchPage('history');
        }

        function renderHistory() {
            if (historyData.length === 0) {
                historyList.innerHTML =
                    '<p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>';
                return;
            }
            historyList.innerHTML = historyData.map(trx => `
                <div class="history-item" style="display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 10px;">
                    <div>
                        <p style="font-weight: 700;">ID: #${trx.id.toString().slice(-6)}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text);">${trx.date}</p>
                        <p style="font-size: 0.85rem; margin-top: 8px;">${trx.items.map(i => `${i.qty}x ${i.name}`).join(', ')}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">👤 ${trx.recipient_name || '-'} | 📞 ${trx.recipient_phone || '-'}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text); margin-top: 6px;">📍 ${trx.address || '-'}</p>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--orange-brand);">${formatRupiah(trx.total)}</span>
                        <p style="color: #10b981; font-size: 0.7rem; font-weight: bold; margin-top: 5px;">BERHASIL</p>
                    </div>
                </div>
            `).join('');
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
            localStorage.setItem('twins_theme', themeName);
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
        const savedTheme = localStorage.getItem('twins_theme') || 'dark';
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
    </script>
    <script>
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
        });

        // --- DASHBOARD PREMIUM HEADER ANIMATION ---
        document.addEventListener('DOMContentLoaded', () => {
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
        // Panggil render pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            renderProducts();
        });
    </script>
</body>

</html>
