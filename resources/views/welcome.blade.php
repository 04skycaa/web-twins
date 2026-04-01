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
            <a href="#outlet" class="nav-link">Outlet</a>
            <a href="#cabang" class="nav-link">Cabang</a>
            <a href="#keunggulan" class="nav-link">Keunggulan</a>
        </nav>
        
        <div class="nav-btns">
            <button class="theme-toggle" id="themeBtn">
                <svg id="sunIcon" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg id="moonIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-fill" style="text-decoration: none;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline" style="text-decoration: none;">Login</a>
                    
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-fill" style="text-decoration: none;">Register</a>
                    @endif
                @endauth
            @endif

            <button class="menu-toggle" id="menuToggle">
                <span></span><span></span><span></span>
            </button>
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

    <section id="outlet" class="explore-section">
        <h2>Pilih Cabang<span>Terdekatmu</span></h2>
        
        <div class="nft-grid">
            <div class="nft-item">
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p>Cabang Jember</p>
                    </div>
                </div>
                <div class="nft-item-img">
                    <img src="images/toko1.jpg">
                </div>
                <h4>Outlet Jember - Mastrip</h4>
                <div class="bid-box">
                    <div class="bid-info">
                        <p>TWINS</p>
                        <p>📍 Jl. Mastrip No. 12</p>
                        <p>🕒 Buka: 08.00 - 21.00</p>
                        <p>⭐ Rating: 4.8</p>
                    </div>
                    <button class="btn-action">Pilih Outlet</button>
                </div>
            </div>

            <div class="nft-item featured">
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p>Cabang Situbondo</p>
                    </div>
                </div>
                <div class="nft-item-img">
                    <img src="images/toko2.jpg">
                </div>
                <h4>Outlet Situbondo - Basuki Rahmat</h4>
                <div class="bid-box">
                    <div class="bid-info">
                        <p>TWINS</p>
                        <p>📍 Jl. Basuki Rahmat No. 12</p>
                        <p>🕒 Buka: 08.00 - 21.00</p>
                        <p>⭐ Rating: 4.8</p>
                    </div>
                    <button class="btn-action">Pilih Outlet</button>
                </div>
            </div>

            <div class="nft-item">
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p>Cabang Banyuwangi</p>
                    </div>
                </div>
                <div class="nft-item-img">
                    <img src="images/toko3.jpg">
                </div>
                <h4>Outlet Banyuwangi - Musi</h4>
                <div class="bid-box">
                    <div class="bid-info">
                        <p>TWINS</p>
                        <p>📍 Jl. Musi No. 12</p>
                        <p>🕒 Buka: 08.00 - 21.00</p>
                        <p>⭐ Rating: 4.8</p>
                    </div>
                    <button class="btn-action">Pilih Outlet</button>
                </div>
            </div>
        </div>
    </section>

    <section id="cabang" class="stories-section">
        <div class="stories-header">
            <h2>Latest Stories</h2>
            <button class="btn-outline">Read more articles</button>
        </div>

        <div class="stories-grid">
            <div class="main-story">
                <div class="story-img">
                    <img src="https://picsum.photos/seed/story1/800/450" alt="LA Guide">
                </div>
                <span class="category-tag">Food and Drink</span>
                <h3>Los Angeles food & drink guide: 10 things to try in Los Angeles, California</h3>
                <div class="story-meta">
                    <span>Aug 14, 2024</span>
                    <span>•</span>
                    <span>5 min read</span>
                </div>
                <p>It seems that in California almost any problem can be solved with a combination of avocados, yoga, and dogs. After all, food health and mental wellness are top priorities...</p>
            </div>

            <div class="side-stories">
                <div class="side-story">
                    <div class="side-img">
                        <img src="https://picsum.photos/seed/story2/200/200" alt="London">
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
    </section>

    <!-- SECTION FEATURED PRODUCT (Air Humidifiers) -->
    <section id="keunggulan" class="product-features-section">
        <h2 class="heading">
            Air humidifiers create a<br>balanced indoor environment
        </h2>

        <div class="grid-container">
            <!-- Sisi Kiri -->
            <div class="feature-list left-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 8h10"/><path d="M7 12h10"/><path d="M7 16h10"/></svg>
                    </div>
                    <h3 class="feature-title">Control Panel</h3>
                    <p class="feature-description">
                        Tombol daya pada perangkat Anda berfungsi sebagai gerbang kenyamanan untuk mengaktifkan fungsi.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6v6a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3z"/><path d="M9 3v12"/><path d="M3 9h12"/><path d="M11.5 11.5 19 19"/><path d="M19 11.5 11.5 19"/></svg>
                    </div>
                    <h3 class="feature-title">Optional UV-C Light</h3>
                    <p class="feature-description">
                        Desain kisi-kisi atau ventilasi untuk mencegah partikel besar atau benda asing masuk ke dalam sistem.
                    </p>
                </article>
            </div>

            <!-- Bagian Gambar -->
            <div class="product-image-container">
                <div class="product-placeholder">
                    <svg class="placeholder-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="placeholder-label">PRODUK UTAMA</span>
                </div>
            </div>

            <!-- Sisi Kanan -->
            <div class="feature-list right-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12h.01"/><path d="M16 12h.01"/><path d="M20 12h.01"/><path d="M12 16h.01"/><path d="M16 16h.01"/><path d="M20 16h.01"/><path d="M12 20h.01"/><path d="M16 20h.01"/><path d="M20 20h.01"/><path d="M12 8h.01"/><path d="M16 8h.01"/><path d="M20 8h.01"/><path d="M12 4h.01"/><path d="M16 4h.01"/><path d="M20 4h.01"/><path d="M8 12H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/><path d="M8 20H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h4"/></svg>
                    </div>
                    <h3 class="feature-title">Air Output</h3>
                    <p class="feature-description">
                        Menawarkan pengaturan keluaran kabut yang dapat disesuaikan dengan tingkat kelembapan pilihan Anda.
                    </p>
                </article>

                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2"/><path d="M9 2v20"/><path d="M15 2v20"/><path d="M5 12h14"/></svg>
                    </div>
                    <h3 class="feature-title">Filtration System</h3>
                    <p class="feature-description">
                        Karbon aktif secara efektif menghilangkan bau, bahan kimia, dan senyawa organik yang mudah menguap.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <footer style="padding: 60px 8%; border-top: 1px solid var(--card-border); text-align: center; color: var(--sub-text); font-size: 14px;">
        &copy; {{ date('Y') }} TWINS - Kelompok 4. All Rights Reserved.
    </footer>

    <script>
        const themeBtn = document.getElementById('themeBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const body = document.body;
        const cards = Array.from(document.querySelectorAll('.nft-card'));
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        
        let activeIndex = Math.floor(cards.length / 2);

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

        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            
            // Opsional: Animasi tombol jadi silang (X)
            menuToggle.classList.toggle('is-active');
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