<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="session-success" content="{{ session('success') ?? '' }}">
    <meta name="session-error" content="{{ session('error') ?? '' }}">
    <title>TWINS - Food Delivery Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        <nav class="main-nav">
            <a class="nav-link active" id="nav-home" onclick="switchPage('home')">Beranda</a>
            <a class="nav-link" id="nav-cat" onclick="scrollToCategory()">Kategori</a>
            <a class="nav-link" id="nav-history" onclick="switchPage('history')">Riwayat</a>
            <a class="nav-link" id="nav-chat" onclick="goToWhatsApp()">Chat</a>
        </nav>
        <div class="nav-btns">
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

            <div class="theme-dropdown">
                <button class="theme-btn" onclick="toggleThemeMenu()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
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
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <div class="cart-badge" id="cartBadge">0</div>
    </div>

    <div class="sheet-overlay" id="sheetOverlay" onclick="toggleBottomSheet()"></div>
    <div class="bottom-sheet" id="bottomSheet">
        <div class="handle"></div>
        <div id="mobileSheetContent"></div>
    </div>

    <div class="container" id=
    "mainContainer">
        <main class="main-content anim-fade-up" id="homePage">
            <div class="promo-banner float-hover" style="min-height: 280px; height: auto; padding: 40px;">
                <span class="badge" style="margin-bottom: 10px;">Outlet TWINS</span>
                <h1 style="margin: 5px 0 15px 0;">{{ $outlet->nama }}</h1>
                <p style="font-size: 1rem; opacity: 0.9; margin-bottom: 20px;">📍 {{ $outlet->alamat }}</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">🕒 {{ $outlet->jam_buka }}</span>
                    <span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">⭐ {{ number_format($outlet->rating, 1) }}</span>
                </div>
            </div>

            <section id="categorySection" class="search-filter-section">
                <div class="search-row">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="searchInput" placeholder="Cari menu favoritmu..." oninput="handleSearch()">
                    </div>
                    <button class="filter-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filter
                    </button>
                </div>
                
                <div class="filter-container" id="filterContainer">
                    <div class="filter-chip active" data-category="semua" onclick="filterProducts('semua', this)">Semua</div>
                    @foreach($categories as $category)
                    <div class="filter-chip" data-category="{{ $category['id'] }}" onclick="filterProducts(this.dataset.category, this)">{{ $category['name'] }}</div>
                    @endforeach
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
                            <input type="radio" name="rating" value="5" id="star5"><label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1" required><label for="star1">★</label>
                        </div>
                        <textarea name="comment" placeholder="Berikan komentar Anda..." rows="3"></textarea>
                        <button type="submit" class="btn-fill" style="margin-top: 15px; width: 100%;">Kirim Ulasan</button>
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
                                <div class="user-avatar-sm">{{ strtoupper(substr($review->user->username, 0, 1)) }}</div>
                                <strong>{{ $review->user->username }}</strong>
                            </div>
                            <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="review-rating">
                            @for($i = 0; $i < 5; $i++)
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
                <div class="white-card hidden" id="addressSection" style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="font-size: 0.95rem;">Delivery Address</h4>
                        <a href="#" style="color: var(--orange-brand); font-size: 0.75rem; text-decoration: none;">Change</a>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">📍</span>
                        <div>
                            <p style="font-size: 0.85rem; font-weight: 600;">Elm Street, 23</p>
                            <p style="font-size: 0.75rem; color: var(--sub-text); line-height: 1.4;">Alamat pengiriman default Anda.</p>
                        </div>
                    </div>
                </div>

                <div class="white-card hidden" id="orderSection" style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 15px; font-size: 0.95rem;">Order Menu</h4>
                    <div id="cartItems"></div>
                    <hr style="border: 0; border-top: 1px solid var(--card-border); margin: 15px 0;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Total</span>
                            <span class="totalPriceDisplay" style="font-size: 1.2rem; font-weight: 800; color: var(--orange-brand);">Rp 0</span>
                        </div>
                    </div>
                    <button class="btn-fill" onclick="checkout()" style="width: 100%; margin-top: 15px; padding: 12px;">Checkout</button>
                </div>

                <div id="discountSection" class="white-card hidden" style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px;">
                    <h4 style="margin-bottom: 12px; font-size: 0.9rem;">Promo Code</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="promoInput" placeholder="TWINS20" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.05); color: var(--text-color); font-size: 0.8rem;">
                        <button onclick="applyPromo()" style="background: var(--orange-brand); color: white; border: none; padding: 0 15px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem;">Apply</button>
                    </div>
                    <p id="promoMessage" style="font-size: 0.7rem; margin-top: 8px; display: none;"></p>
                </div>
            </div>
        </aside>
    </div>

    <nav class="mobile-nav">
        <div class="mob-nav-item active" id="mob-home" onclick="switchPage('home')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>
        <div class="mob-nav-item" id="mob-cat" onclick="scrollToCategory()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Kategori</span>
        </div>
        <div class="mob-nav-item" id="mob-history" onclick="switchPage('history')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Riwayat</span>
        </div>
        <div class="mob-nav-item" onclick="goToWhatsApp()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
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
        
        const addressSection = document.getElementById('addressSection');
        const orderSection = document.getElementById('orderSection');
        const discountSection = document.getElementById('discountSection');

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
        let currentFilter = 'semua';
        let discountPercent = 0;
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));

        function renderProducts() {
            productGrid.innerHTML = '';
            const searchTerm = searchInput.value.toLowerCase();
            const filtered = products.filter(p => {
                const matchesCategory = currentFilter === 'semua' || p.category === currentFilter;
                const matchesSearch = p.name.toLowerCase().includes(searchTerm);
                return matchesCategory && matchesSearch;
            });
            
            if (filtered.length === 0) {
                productGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--sub-text);">Item tidak ditemukan.</p>';
                return;
            }

            filtered.forEach(product => {
                const card = document.createElement('div');
                card.className = 'food-card anim-zoom-in';
                card.innerHTML = `
                    <div style="width: 100%; aspect-ratio: 1/1; overflow: hidden; border-radius: 10px; margin-bottom: 10px;">
                        <img src="${product.img}" class="food-img" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h4 style="font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${product.name}</h4>
                    <p style="color: var(--sub-text); font-size: 0.75rem; margin: 5px 0;">⭐ ${product.rating}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 10px;">
                        <span style="font-weight: 800; color: var(--orange-brand); font-size: 0.9rem;">${formatRupiah(product.price)}</span>
                        <button class="add-btn" onclick="addToCart('${product.name}', ${product.price})">+</button>
                    </div>
                `;
                productGrid.appendChild(card);
                if(window.observer) window.observer.observe(card);
            });
        }

        function handleSearch() { renderProducts(); }

        function filterProducts(category, element) {
            document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
            element.classList.add('active');
            currentFilter = category;
            renderProducts();
        }

        function addToCart(name, price) {
            const existingItem = cart.find(item => item.name === name);
            if (existingItem) {
                existingItem.qty += 1;
            } else {
                cart.push({ name, price, qty: 1 });
            }
            renderCart();
            const fab = document.getElementById('mobileCartBtn');
            if(fab) {
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
            if(badge) badge.innerText = totalCount;

            const mobileCartBtn = document.getElementById('mobileCartBtn');

            if (cart.length > 0) {
                addressSection.classList.remove('hidden');
                orderSection.classList.remove('hidden');
                discountSection.classList.remove('hidden');
                if (!isMobile) {
                    mainContainer.classList.add('has-sidebar');
                    if(mobileCartBtn) mobileCartBtn.style.display = 'none';
                } else {
                    mainContainer.classList.remove('has-sidebar');
                    if(mobileCartBtn) mobileCartBtn.style.display = 'flex';
                }
            } else {
                [addressSection, orderSection, discountSection].forEach(el => el.classList.add('hidden'));
                mainContainer.classList.remove('has-sidebar');
                if(mobileCartBtn) mobileCartBtn.style.display = 'none';
                toggleBottomSheet(false);
            }

            cartItemsContainer.innerHTML = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                subtotal += (item.price * item.qty);
                const div = document.createElement('div');
                div.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;';
                div.innerHTML = `
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
                    <div style="display: flex; align-items: center; gap: 10px; text-align: right;">
                        <span style="color: var(--orange-brand); font-weight: 700; font-size: 0.85rem;">${formatRupiah(item.price * item.qty)}</span>
                        <button class="delete-item-btn" onclick="removeFromCart(${index})">🗑️</button>
                    </div>
                `;
                cartItemsContainer.appendChild(div);
            });

            // Biaya Service telah dihapus dari kalkulasi total
            let finalTotal = subtotal > 0 ? subtotal * (1 - discountPercent) : 0;
            document.querySelectorAll('.totalPriceDisplay').forEach(el => {
                el.innerText = formatRupiah(finalTotal);
            });

            if (isMobile) {
                const sheetContent = document.getElementById('mobileSheetContent');
                const sidebarContent = document.getElementById('sidebarContentWrapper');
                if(sheetContent && sidebarContent) {
                    sheetContent.innerHTML = sidebarContent.innerHTML;
                }
            }
        }

        function toggleBottomSheet(force) {
            const sheet = document.getElementById('bottomSheet');
            const overlay = document.getElementById('sheetOverlay');
            if(!sheet || !overlay) return;
            
            const isActive = force !== undefined ? force : !sheet.classList.contains('active');
            
            if (isActive && cart.length > 0) {
                sheet.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                sheet.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
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
                if(mobHome) mobHome.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                renderCart();
            } else if (page === 'history') {
                historyPage.classList.remove('hidden');
                document.getElementById('nav-history').classList.add('active');
                const mobHistory = document.getElementById('mob-history');
                if(mobHistory) mobHistory.classList.add('active');
                mainContainer.classList.remove('has-sidebar');
                renderHistory();
            }
        }

        function scrollToCategory() {
            switchPage('home');
            document.querySelectorAll('.nav-link, .mob-nav-item').forEach(l => l.classList.remove('active'));
            document.getElementById('nav-cat').classList.add('active');
            const mobCat = document.getElementById('mob-cat');
            if(mobCat) mobCat.classList.add('active');

            setTimeout(() => {
                const categorySection = document.getElementById('categorySection');
                if(categorySection) {
                    categorySection.scrollIntoView({ behavior: 'smooth' });
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
                total: total
            });
            cart = [];
            discountPercent = 0;
            renderCart();
            switchPage('history');
        }

        function renderHistory() {
            if (historyData.length === 0) {
                historyList.innerHTML = '<p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>';
                return;
            }
            historyList.innerHTML = historyData.map(trx => `
                <div class="history-item" style="display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); border: 1px solid var(--card-border); padding: 15px; border-radius: 15px; margin-bottom: 10px;">
                    <div>
                        <p style="font-weight: 700;">ID: #${trx.id.toString().slice(-6)}</p>
                        <p style="font-size: 0.75rem; color: var(--sub-text);">${trx.date}</p>
                        <p style="font-size: 0.85rem; margin-top: 8px;">${trx.items.map(i => `${i.qty}x ${i.name}`).join(', ')}</p>
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
        }, { threshold: 0.1 });

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
                if(btn.getAttribute('data-theme-val') === themeName) {
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
            if(!el.classList.contains('anim-fade-up') && !el.classList.contains('anim-zoom-in')) {
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

            if(bgContainer) {
                // Initialize 3D Engine for Background
                bgContainer.style.perspective = '1200px';
                bgContainer.style.transformStyle = 'preserve-3d';

                for(let i = 0; i < 20; i++) {
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
                let targetX = 0, targetY = 0;
                let currentX = 0, currentY = 0;

                document.addEventListener("mousemove", (e) => {
                    targetX = (e.clientX - window.innerWidth / 2) * 0.08;
                    targetY = (e.clientY - window.innerHeight / 2) * 0.08;
                });

                function animate3D() {
                    currentX += (targetX - currentX) * 0.05;
                    currentY += (targetY - currentY) * 0.05;

                    // Tilt the entire bakery container & scale slightly to prevent edge cutoff
                    bgContainer.style.transform = `scale(1.1) rotateX(${-currentY * 0.4}deg) rotateY(${currentX * 0.4}deg)`;

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
        const _sessionError   = document.querySelector('meta[name="session-error"]')?.content || null;

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
    </script>
</body>
</html>