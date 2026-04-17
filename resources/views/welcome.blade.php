<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - ahlinya belanja sembako</title>

    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>
<body>

    <div class="glow-sphere"></div>

    <header>
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="main-nav" id="mainNav">
            <a href="#Beranda" class="nav-link">Beranda</a>
            <a href="#promo-outlet" class="nav-link">Promo</a>
            <a href="#outlet" class="nav-link">Outlet</a>
            <a href="#keunggulan" class="nav-link">Keunggulan</a>
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

    <section id="beranda">
        <main class="hero">
            <div class="badge">TWINS by Kelompok 4</div>
            <h1>Belanja Mudah<span>Dimana Saja</span></h1>
            <p>Setiap outlet punya pilihan terbaiknya masing-masing. Pilih outlet terdekatmu sekarang dan mulai belanja bahan kue dengan lebih cepat, mudah, dan praktis.</p>

            <div class="nft-container" id="nftContainer">
                <div class="nft-card"><img src="{{ asset('images/toko1.jpg') }}" alt="Toko"></div>
                <div class="nft-card"><img src="{{ asset('images/toko2.jpg') }}" alt="Toko"></div>
                <div class="nft-card"><img src="{{ asset('images/toko3.jpg') }}" alt="Toko"></div>
                <div class="nft-card"><img src="{{ asset('images/toko4.jpg') }}" alt="Toko"></div>
                <div class="nft-card"><img src="{{ asset('images/toko5.jpg') }}" alt="Toko"></div>
            </div>
        </main>
    </section>

    <section id="promo-outlet" class="promo-section">
        <div class="promo-header">
            <h2>PROMO <span>PRODUK</span></h2>
        </div>

        <div class="promo-slider-container" id="promoSlider">
            @forelse($promoProducts as $index => $promo)
            <div class="promo-card" data-index="{{ $index }}">
                <div class="discount-label">
                    @if($promo->tipe == 'persen')
                        {{ $promo->nilai }}% OFF
                    @else
                        Potongan Rp{{ number_format($promo->nilai, 0, ',', '.') }}
                    @endif
                </div>
                <div class="promo-img">
                    <img src="{{ $promo->image_url }}" alt="{{ $product->product_name }}">
                </div>
                <div class="promo-content">
                    <div class="outlet-info">
                        <p class="outlet-name">{{ $promo->nama_promo }}</p>
                        <p class="outlet-address">{{ $promo->outlet_address }}</p>
                    </div>
                    <span class="category-tag">{{ $promo->category }}</span>
                    <h4>{{ $promo->product_name }}</h4>
                    <div class="promo-footer">
                        <div class="price-box">
                            <span class="price-now"><small>Rp</small>{{ number_format($promo->price, 0, ',', '.') }}</span>
                        </div>
                        <button class="btn-add-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <p style="text-align: center; width: 100%; color: var(--sub-text);">Belum ada promo aktif saat ini.</p>
            @endforelse
        </div>
        <div class="promo-dots" id="promoDots"></div>
    </section>

    <section id="outlet" class="explore-section">
        <h2>Pilih Cabang<span>Terdekatmu</span></h2>

        <div class="nft-grid">
            @foreach($outlets as $index => $outlet)
            <div class="nft-item {{ $index === 1 ? 'featured' : '' }}">
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">Cabang {{ $outlet->nama }}</p>
                    </div>
                </div>
                <div class="nft-item-img">
                    <img src="{{ asset('images/toko'.(($index % 5) + 1).'.jpg') }}">
                </div>
                <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $outlet->nama }}</h4>
                <div class="bid-box">
                    <div class="bid-info" style="flex: 1; min-width: 0;">
                        <p>TWINS</p>
                        <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">📍 {{ $outlet->alamat }}</p>
                        <p>🕒 {{ $outlet->jam_buka }}</p>
                        <p>⭐ {{ number_format($outlet->rating, 1) }}</p>
                    </div>
                    <a href="{{ route('user.index', $outlet->uuid) }}" class="btn-action" style="text-decoration: none; text-align: center;">
                        Pilih
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!--<section id="cabang" class="stories-section">
        <div class="stories-header">
            <h2>Latest Stories</h2>
            <button class="btn-outline">Read more articles</button>
        </div>

        <div class="stories-grid">
            <div class="main-story">
                <div class="story-img">
                    <img src="{{ asset('images/toko-luar.png') }}" alt="LA Guide">
                </div>
                <span class="category-tag">Mulai Baking Tanpa Ribet</span>
                <h3>Temukan Semua Bahan Kue Favoritmu di Twins</h3>
                <p>Mau bikin kue tapi bahan ribet? Tenang, semua ada di Twins. Tinggal pilih, checkout, langsung baking!</p>
            </div>

            <div class="side-stories">
                <div class="side-story">
                    <div class="side-img">
                        <img src="{{ asset('images/toko1.jpg') }}" alt="London">
                    </div>
                    <div class="side-content">
                        <span class="category-tag">Shopping</span>
                        <h4>15 West London Markets You'll Love: Best Markets in West London</h4>
                        <div class="story-meta">
                            <span>Aug 12, 2024</span>
                            <span>•</span>
                            <span>4 min read</span>
                        </div>
                    </div>
                </div>

                <div class="side-story">
                    <div class="side-img">
                        <img src="https://picsum.photos/seed/story3/200/200" alt="Hotels">
                    </div>
                    <div class="side-content">
                        <span class="category-tag">Hotels</span>
                        <h4>10 incredible hotels around the world you can book with points in 2024</h4>
                        <div class="story-meta">
                            <span>Aug 10, 2024</span>
                            <span>•</span>
                            <span>7 min read</span>
                        </div>
                    </div>
                </div>

                <div class="side-story">
                    <div class="side-img">
                        <img src="https://picsum.photos/seed/story4/200/200" alt="Chicago">
                    </div>
                    <div class="side-content">
                        <span class="category-tag">Travel, Budget</span>
                        <h4>Visiting Chicago on a Budget: Affordable Eats and Attraction Deals</h4>
                        <div class="story-meta">
                            <span>Aug 07, 2024</span>
                            <span>•</span>
                            <span>6 min read</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->

    <section id="keunggulan" class="product-features-section">
        <h2 class="heading">
            Kenapa Belanja di Twins<br>Lebih Mudah & Menyenangkan?
        </h2>

        <div class="grid-container">
            <div class="feature-list left-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h18v18H3z"></path>
                            <path d="M7 12h10"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Produk Lengkap</h3>
                    <p class="feature-description">
                        Semua kebutuhan baking kamu tersedia di satu tempat, dari bahan dasar sampai dekorasi kue.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Harga Terjangkau</h3>
                    <p class="feature-description">
                        Belanja bahan kue tanpa khawatir mahal, dengan harga bersahabat untuk semua kalangan.
                    </p>
                </article>
            </div>

            <div class="product-image-container">
                <div class="product-placeholder">
                    <svg class="placeholder-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                    </svg>
                    <span class="placeholder-label">PRODUK UNGGULAN</span>
                </div>
            </div>

            <div class="feature-list right-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Pengiriman Cepat</h3>
                    <p class="feature-description">
                        Pesanan kamu diproses dengan cepat agar bisa langsung dipakai untuk baking tanpa nunggu lama.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1l3 5h6l-4.5 4 2 6-6-3.5L6.5 16l2-6L4 6h6z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Kualitas Terjamin</h3>
                    <p class="feature-description">
                        Produk berkualitas tinggi yang aman dan terpercaya untuk hasil baking yang maksimal.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <footer style="padding: 60px 8%; border-top: 1px solid var(--card-border); text-align: center; color: var(--sub-text); font-size: 14px;">
        &copy; {{ date('Y') }} TWINS - Kelompok 4.
    </footer>

    <nav class="mobile-nav">
        <div class="mob-nav-item active" onclick="switchPage('beranda')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>

        <div class="mob-nav-item" onclick="switchPage('promo-outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
            <span>Promo</span>
        </div>

        <div class="mob-nav-item" onclick="scrollToCategory('outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l18 0l-1 10l-16 0z"></path><path d="M3 11l18 0"></path><path d="M2 3l20 0l-1 6l-18 0z"></path></svg>
            <span>Outlet</span>
        </div>

        <div class="mob-nav-item" onclick="switchPage('keunggulan')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
            <span>Keunggulan</span>
        </div>
    </nav>

    <script>
        const themeBtn = document.getElementById('themeBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const body = document.body;
        const cards = Array.from(document.querySelectorAll('.nft-card'));
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        const promoCards = Array.from(document.querySelectorAll('#promoSlider .promo-card'));
        const promoDotsContainer = document.getElementById('promoDots');
        let currentPromoIndex = 1; 

        promoCards.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if(i === currentPromoIndex) dot.classList.add('active');
            dot.addEventListener('click', () => {
                currentPromoIndex = i;
                updatePromoSlider();
            });
            promoDotsContainer.appendChild(dot);
        });

        function updatePromoSlider() {
            const total = promoCards.length;
            
            promoCards.forEach((card, i) => {
                card.classList.remove('active', 'prev', 'next', 'prev2', 'next2');
                
                if (i === currentPromoIndex) {
                    card.classList.add('active');
                } else if (i === (currentPromoIndex - 1 + total) % total) {
                    card.classList.add('prev');
                } else if (i === (currentPromoIndex + 1) % total) {
                    card.classList.add('next');
                } else if (i === (currentPromoIndex - 2 + total) % total) {
                    card.classList.add('prev2');
                } else if (i === (currentPromoIndex + 2) % total) {
                    card.classList.add('next2');
                }
            });

            document.querySelectorAll('.dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === currentPromoIndex);
            });
        }

        // Klik pada kartu untuk pindah
        promoCards.forEach((card, i) => {
            card.addEventListener('click', () => {
                currentPromoIndex = i;
                updatePromoSlider();
            });
        });

        window.onload = updatePromoSlider;

        let activeIndex = Math.floor(cards.length / 2);

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

        function updateLayout() {
            const isMobile = window.innerWidth <= 768;
            const horizontalGap = isMobile ? 85 : 110;
            const radiusY = isMobile ? 25 : 40;
            const rotationAngle = isMobile ? 12 : 15;

            cards.forEach((card, i) => {
                const diff = i - activeIndex;
                const absDiff = Math.abs(diff);

                card.classList.remove('active');

                if (diff === 0) {
                    card.classList.add('active');
                    card.style.left = '50%';
                    card.style.top = '50%';
                    card.style.transform = 'translate(-50%, -50%) scale(1.2)';
                    card.style.zIndex = '500';
                    card.style.opacity = '1';
                } else {
                    const x = 50 + (diff * (horizontalGap / 10));
                    const yOffset = absDiff * absDiff * (radiusY / 10);

                    const scale = 1 - (absDiff * 0.1);
                    const rotate = diff * rotationAngle;
                    const opacity = 1 - (absDiff * 0.2);

                    card.style.left = `${x}%`;
                    card.style.top = `calc(50% + ${yOffset}px)`;
                    card.style.transform = `translate(-50%, -50%) scale(${scale}) rotate(${rotate}deg)`;
                    card.style.zIndex = 100 - absDiff;
                    card.style.opacity = Math.max(opacity, 0.4);
                }
            });
        }

        function switchPage(pageId) {
            const element = document.getElementById(pageId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
            
            document.querySelectorAll('.mob-nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }

        function scrollToCategory(id) {
            const element = document.getElementById(id);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        cards.forEach((card, index) => {
            card.addEventListener('click', () => {
                activeIndex = index;
                updateLayout();
            });
        });

        themeBtn.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            if (currentTheme === 'light') {
                body.removeAttribute('data-theme');
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                body.setAttribute('data-theme', 'light');
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('active');
            });
        });

        window.addEventListener('resize', updateLayout);
        updateLayout();

        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);

            if (params.get('verified') === '1') {
                Swal.fire({
                    title: 'Verifikasi Berhasil!',
                    text: 'Selamat bergabung di TWINS! Akun Anda sudah aktif.',
                    icon: 'success',
                    confirmButtonColor: '#0477bf',
                    showClass: {
                        popup: 'animate__animated animate__zoomIn'
                    }
                });

                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
</body>
</html>