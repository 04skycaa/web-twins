<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Food Delivery Dashboard</title>
    
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    
</head>
<body id="body">

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

            <button class="theme-toggle" id="themeBtn">
                <svg id="moonIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                <svg id="sunIcon" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
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

    <div class="container" id="mainContainer">
        <main class="main-content" id="homePage">
            <div class="promo-banner">
                <span style="color: var(--orange-brand); font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">Special Promo</span>
                <h1>TWINS - AHLINYA BAHAN KUE</h1>
                <p style="font-size: 1.1rem; opacity: 0.9;">Belanja lebih mudah dari mana saja</p>
                <button class="btn-fill" style="width: fit-content; margin-top: 20px; padding: 15px 30px;">Order Now</button>
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
                    <div class="filter-chip" data-category="olahan" onclick="filterProducts('olahan', this)">Tepung & Olahan</div>
                    <div class="filter-chip" data-category="pengembang" onclick="filterProducts('pengembang', this)">Pengembang</div>
                    <div class="filter-chip" data-category="perasa" onclick="filterProducts('perasa', this)">Perasa & Pewarna</div>
                </div>

                <div class="food-grid" id="productGrid"></div>
            </section>
        </main>

        <main class="main-content hidden" id="historyPage">
            <h2 style="margin-bottom: 25px;">Riwayat Transaksi</h2>
            <div id="historyList">
                <p style="color: var(--sub-text); text-align: center; padding: 50px;">Belum ada riwayat pesanan.</p>
            </div>
        </main>

        <aside class="sidebar" id="sidebarArea">
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
        const themeBtn = document.getElementById('themeBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        
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

        const products = [
            { id: 1, name: 'Tepung Terigu Segi Tiga', price: 13000, category: 'olahan', img: "{{ asset('images/terigu.jpg') }}", rating: 4.8 },
            { id: 2, name: 'Tepung Beras', price: 8000, category: 'olahan', img: "{{ asset('images/beras.webp') }}", rating: 4.5 },
            { id: 3, name: 'Fernipan', price: 5000, category: 'pengembang', img: "{{ asset('images/fernipan.jpeg') }}", rating: 4.9 },
            { id: 4, name: 'Baking Powder', price: 5300, category: 'pengembang', img: "{{ asset('images/backingpowder.jpg') }}", rating: 4.7 },
            { id: 5, name: 'Pasta Vanilla 60ml', price: 6000, category: 'perasa', img: "{{ asset('images/vanila.webp') }}", rating: 4.6 },
            { id: 6, name: 'Pewarna Makanan Merah', price: 5000, category: 'perasa', img: "{{ asset('images/merah.jpg') }}", rating: 4.4 }
        ];

        let cart = [];
        let historyData = [];
        let currentFilter = 'semua';
        let discountPercent = 0;

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
                card.className = 'food-card';
                card.innerHTML = `
                    <img src="${product.img}" class="food-img">
                    <h4>${product.name}</h4>
                    <p style="color: var(--sub-text); font-size: 0.8rem; margin: 5px 0;">⭐ ${product.rating}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 10px;">
                        <span style="font-weight: 800; color: var(--orange-brand);">${formatRupiah(product.price)}</span>
                        <button class="add-btn" onclick="addToCart('${product.name}', ${product.price})">+</button>
                    </div>
                `;
                productGrid.appendChild(card);
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

        themeBtn.addEventListener('click', () => {
            if (body.hasAttribute('data-theme')) {
                body.removeAttribute('data-theme');
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                body.setAttribute('data-theme', 'light');
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        });

        window.addEventListener('resize', renderCart);
        renderProducts();
        renderCart();
    </script>
</body>
</html>