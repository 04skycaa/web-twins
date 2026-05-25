<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="session-success" content="{{ session('success') ?? '' }}">
    <meta name="session-error" content="{{ session('error') ?? '' }}">
    <meta name="session-error-role" content="{{ session('error_role') ?? '' }}">
    <title>TWINS - ahlinya belanja sembako</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- GSAP + ScrollTrigger + Lenis untuk animasi premium -->
    <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>


<body class="hide-overflow">
    <div id="welcome-splash">
        <div class="splash-panel top"></div>
        <div class="splash-panel bottom"></div>
        <div class="splash-center">
            <div class="logo-wrapper">
                <div class="logo-energy-ring" id="energyRing"></div>
                <img src="{{ asset('images/logo.png') }}" alt="Twins Logo" class="splash-logo" id="splashLogo">
            </div>
            <div class="splash-text" id="splashText">
                <span class="splash-char">T</span>
                <span class="splash-char">W</span>
                <span class="splash-char">I</span>
                <span class="splash-char">N</span>
                <span class="splash-char">S</span>
            </div>
        </div>
    </div>
    <div class="animated-bg"></div>
    <div class="light-rays-container">
        <div class="god-ray ray1"></div>
        <div class="god-ray ray2"></div>
        <div class="god-ray ray3"></div>
        <div class="god-ray ray4"></div>
    </div>
    <div id="bakery-bg" style="position:fixed; top:0; left:0; width:100%; height:100vh; z-index:-1; overflow:hidden;"></div>
    <div class="glow-sphere"></div>

    <header id="mainHeader">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
        </div>

        <nav class="main-nav" id="mainNav">
            <a href="#heroBadge" class="nav-link" id="nav-home">Beranda</a>
            <a href="#promoTitle" class="nav-link" id="nav-promo">Promo</a>
            <a href="#outletTitle" class="nav-link" id="nav-outlet">Outlet</a>
            <a href="#featuresTitle" class="nav-link" id="nav-features">Keunggulan</a>
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
                        <form method="POST" action="{{ route('logout') }}" style="display: none;" id="logout-form-header-mob">
                            @csrf
                        </form>
                        <button onclick="document.getElementById('logout-form-header-mob').submit();" style="display: flex; align-items: center; color: #ef4444;">
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

            @guest
                <a href="{{ route('login') }}" class="btn-outline desktop-only" style="text-decoration: none;">Login</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-fill desktop-only" style="text-decoration: none;">Register</a>
                @endif
            @endguest
        </div>
    </header>

    <section id="beranda">
        <main class="hero anim-fade-up">
            <div class="badge" id="heroBadge">TWINS by Kelompok 4</div>
            <h1 id="hero-title">
                <span class="hero-text-clip"><span id="hero-word-left">Belanja Mudah</span></span><span class="hero-text-clip"><span id="hero-word-right"><span>Dimana Saja</span></span></span>
            </h1>
            <p id="hero-paragraph">Setiap outlet punya pilihan terbaiknya masing-masing. Pilih outlet terdekatmu sekarang dan mulai belanja bahan kue dengan lebih cepat, mudah, dan praktis.</p>

            <div class="nft-container anim-zoom-in" id="nftContainer">
                @php
                    $heroOutlets = $outlets->take(5);
                    // Ensure we have at least 5 cards for the 3D stack effect by repeating if necessary
                    if ($heroOutlets->count() > 0 && $heroOutlets->count() < 5) {
                        $count = $heroOutlets->count();
                        for ($i = $count; $i < 5; $i++) {
                            $heroOutlets->push($heroOutlets[$i % $count]);
                        }
                    }
                @endphp
                
                @foreach($heroOutlets as $index => $heroOutlet)
                <div class="nft-card">
                    <img src="{{ asset('images/toko'.(($index % 5) + 1).'.jpg') }}" alt="Store Image">
                </div>
                @endforeach
            </div>
        </main>
    </section>

    <section id="promo-outlet" class="promo-section" style="padding: 30px 0; overflow: hidden; background: transparent;">
        <div class="promo-header" style="margin-bottom: 20px; text-align: center;">
            <h2 id="promoTitle" style="font-size: 28px; letter-spacing: 2px; color: var(--text-color); margin: 0; text-transform: uppercase; font-weight: 800;">
                PROMO <span style="color: var(--accent-purple);">UNGGULAN</span>
            </h2>
            <p style="color: var(--sub-text); margin-top: 10px;">Penawaran spesial terbaik hanya untuk Anda</p>
        </div>

        <div class="promo-carousel-wrapper">
            <button class="promo-nav-btn prev" id="prevPromo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            
            <div class="promo-carousel-slider" id="promoSliderMain">
                @forelse($promoProducts as $promo)
                <div class="promo-carousel-item">
                    @if(isset($promo->diskon) && $promo->diskon > 0)
                        <div class="promo-badge-premium">Diskon {{ $promo->diskon }}%</div>
                    @endif
                    <img src="{{ $promo->image_banner }}" alt="{{ $promo->nama_promo }}">
                </div>
                @empty
                <div style="text-align: center; width: 100%; padding: 40px 20px; color: var(--sub-text); font-size: 16px; background: var(--card-bg); border-radius: 20px;">
                    Belum ada promo aktif saat ini.
                </div>
                @endforelse
            </div>

            <button class="promo-nav-btn next" id="nextPromo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>

        <script>
            function movePromo(direction) {
                const slider = document.getElementById('promoSliderMain');
                if (!slider) return;
                
                const itemWidth = slider.querySelector('.promo-carousel-item')?.offsetWidth || slider.offsetWidth;
                const scrollAmount = itemWidth + 20; // width + gap
                
                if (direction === 'next') {
                    const isAtEnd = slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth - 20;
                    if (isAtEnd) {
                        slider.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    }
                } else {
                    const isAtStart = slider.scrollLeft <= 10;
                    if (isAtStart) {
                        slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
                    } else {
                        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    }
                }
            }

            // Bind buttons
            document.getElementById('prevPromo')?.addEventListener('click', () => movePromo('prev'));
            document.getElementById('nextPromo')?.addEventListener('click', () => movePromo('next'));

            // Auto-play
            let promoAutoPlay = setInterval(() => { movePromo('next'); }, 6000);
            
            const sliderWrap = document.querySelector('.promo-carousel-wrapper');
            if(sliderWrap) {
                sliderWrap.addEventListener('mouseenter', () => clearInterval(promoAutoPlay));
                sliderWrap.addEventListener('mouseleave', () => {
                    clearInterval(promoAutoPlay);
                    promoAutoPlay = setInterval(() => { movePromo('next'); }, 6000);
                });
            }

            // Intersection Observer to ensure autoplay only when in view
            const promoObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // restart autoplay if needed
                    } else {
                        clearInterval(promoAutoPlay);
                    }
                });
            }, { threshold: 0.2 });
            if(sliderWrap) promoObserver.observe(sliderWrap);
        </script>
    </section>

    <section id="outlet" class="explore-section">
        <h2 id="outletTitle" data-split-text>Pilih Cabang <span>Terdekatmu</span></h2>

        <div class="nft-grid" data-stagger-grid>
            @foreach($outlets as $index => $outlet)
            <div class="nft-item float-hover {{ $index === 1 ? 'featured' : '' }}" data-stagger-item>
                <div class="owner-info">
                    <div class="owner-details">
                        <p>Outlet TWINS</p>
                        <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">Cabang {{ $outlet->nama }}</p>
                    </div>
                </div>
                <div class="nft-item-img" data-parallax-wrap>
                    <img src="{{ asset('images/toko'.(($index % 5) + 1).'.jpg') }}" data-parallax>
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
    
    <section id="tentang-toko" class="highlight-section">
        <div class="highlight-header">
            <h2 data-split-text>Tentang Toko</h2>
        </div>

        <div class="highlight-container">
            <!-- BOX 1: MEDIA BOX (Sekarang di atas untuk mobile) -->
            <div class="highlight-media-box" data-reveal-right>
                <div class="media-item image-item" data-parallax-wrap>
                    <img src="{{ asset('images/toko5.jpg') }}" alt="Store Gallery" class="main-media" data-parallax>
                    <div class="media-badge">Galeri Store</div>
                </div>

                <div class="media-group-right">
                    <div class="media-item video-item">
                        <img src="{{ asset('images/toko-luar.png') }}" alt="Video Preview" class="main-media">
                        <div class="play-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="30" height="30">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="video-meta">
                        <p>Suasana Hangat Toko Twins</p>
                        <div class="action-wrap">
                            <button class="btn-highlights-sm">Lihat Selengkapnya <span>→</span></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOX 2: TEXT BOX -->
            <div class="highlight-text-box" data-reveal-left>
                <div class="owner-profile">
                    <div class="owner-avatar">
                        <img src="{{ asset('images/logo.png') }}" alt="Twins Owner">
                    </div>
                    <div class="owner-meta">
                        <h4>Twins Bakery Team</h4>
                        <p>Kualitas & Kepercayaan</p>
                    </div>
                </div>

                <div class="star-rating">
                    <span class="stars">★★★★★</span>
                </div>

                <div class="story-content">
                    <h3 class="highlight-title">Perjalanan Menghadirkan Bahan Kue Terbaik</h3>
                    <p class="highlight-desc">Berawal dari semangat untuk mendukung setiap kreator kue di Indonesia, Twins menghadirkan bahan-bahan berkualitas pilihan. Kami percaya bahwa setiap adonan punya cerita, dan setiap cerita layak mendapatkan hasil terbaik. Tekstur sempurna dan rasa yang autentik dimulai dari sini, membawa kebahagian dari setiap panggangan kami ke meja Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="keunggulan" class="product-features-section">
        <h2 id="featuresTitle" class="heading" data-split-text>
            Kenapa Belanja di Twins<br>Lebih Mudah &amp; Menyenangkan?
        </h2>

        <div class="grid-container">
            <div class="feature-list left-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <div class="featured-product-image" data-parallax-wrap>
                    <img src="{{ asset('images/toko4.jpg') }}" alt="Produk Unggulan Twins" style="width:100%;height:100%;object-fit:cover;border-radius:20px;" data-parallax>
                </div>
            </div>

            <div class="feature-list right-side">
                <article class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

    <!-- TESTIMONIALS MARQUEE SECTION -->
    <section id="testimonials" class="testimonials-marquee-section">
        <div class="marquee-header" data-reveal-up>
            <h2 data-split-text>Suara <span>Pelanggan</span></h2>
            <p data-reveal-up>Apa kata mereka yang sudah merasakan manisnya belanja di Twins Bakery?</p>
        </div>

        <div class="marquee-container">
            <!-- Row 1: To the Right -->
            <div class="marquee-row marquee-row-right">
                <div class="marquee-track" id="trackTop">
                    @php $row1 = $testimonials->shuffle(); @endphp
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 2nd set -->
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 3rd set (Security for small data) -->
                    @foreach($row1 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Row 2: To the Left -->
            <div class="marquee-row marquee-row-left">
                <div class="marquee-track" id="trackBottom">
                    @php $row2 = $testimonials->shuffle(); @endphp
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 2nd set -->
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- Cloning 3rd set -->
                    @foreach($row2 as $testi)
                    <div class="testimonial-item-card marquee-clone">
                        <div class="testi-overlay-text">“</div>
                        <p class="testi-content">{{ $testi->comment ?? 'Sangat puas dengan kualitas bahan kue di Twins!' }}</p>
                        <div class="testi-footer">
                            <div class="testi-user-box">
                                <div class="user-avatar-main">{{ strtoupper(substr($testi->user->username, 0, 1)) }}</div>
                                <div class="user-details">
                                    <strong>{{ $testi->user->username }}</strong>
                                    <span>{{ $testi->store->nama }}</span>
                                </div>
                            </div>
                            <div class="testi-stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span class="star {{ $i < $testi->rating ? 'filled' : '' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Gradient Overlays for smooth entry/exit -->
            <div class="marquee-overlay marquee-overlay-left"></div>
            <div class="marquee-overlay marquee-overlay-right"></div>
        </div>

        <div class="add-review-cta">
            <div class="cta-inner">
                <p>Ingin berbagi pengalaman belanja Anda?</p>
                @auth
                    <button onclick="openReviewModal()" class="btn-fill main-cta">Tambah Komentar Anda <span>→</span></button>
                @else
                    <a href="{{ route('login') }}" class="btn-fill main-cta">Login untuk Menambah Komentar <span>→</span></a>
                @endauth
            </div>
        </div>
    </section>

    <!-- REVIEW MODAL -->
    @auth
    <div class="modal-overlay" id="reviewModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeReviewModal()">×</button>
            <div class="modal-header">
                <h3>Beri Ulasan <span>Twins Bakery</span></h3>
                <p>Pilih cabang dan bagikan pengalaman manismu!</p>
            </div>

            <form action="{{ route('landing.review.store') }}" method="POST" id="reviewForm">
                @csrf
                <input type="hidden" name="store_id" id="selectedStoreId" required>

                <div class="modal-body">
                    <!-- Step 1: Select Outlet Grid -->
                    <label class="form-label">1. Pilih Cabang</label>
                    <div class="outlet-selection-grid">
                        @foreach($outlets as $outlet)
                        <div class="outlet-option-card" data-id="{{ $outlet->uuid }}" onclick="selectOutlet('{{ $outlet->uuid }}', this)">
                            <div class="outlet-check">✓</div>
                            <div class="outlet-info-mini">
                                <strong>{{ $outlet->nama }}</strong>
                                <span>{{ Str::limit($outlet->alamat, 30) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Step 2: Rating -->
                    <label class="form-label">2. Berikan Bintang</label>
                    <div class="rating-selector-modal">
                        <input type="radio" name="rating" value="5" id="modal-star5"><label for="modal-star5">★</label>
                        <input type="radio" name="rating" value="4" id="modal-star4"><label for="modal-star4">★</label>
                        <input type="radio" name="rating" value="3" id="modal-star3"><label for="modal-star3">★</label>
                        <input type="radio" name="rating" value="2" id="modal-star2"><label for="modal-star2">★</label>
                        <input type="radio" name="rating" value="1" id="modal-star1" required><label for="modal-star1">★</label>
                    </div>

                    <!-- Step 3: Comment -->
                    <label class="form-label">3. Tulis Komentar</label>
                    <textarea name="comment" placeholder="Ceritakan pengalamanmu di toko ini..." rows="4"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeReviewModal()">Batal</button>
                    <button type="submit" class="btn-fill">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <!-- MODERN 3-COLUMN FOOTER -->
    <footer class="main-footer">
        <div class="footer-container">
            <!-- Col 1: Identity -->
            <div class="footer-col footer-identity">
                <div class="footer-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Twins Logo">
                    <span>TWINS</span>
                </div>
                <p class="footer-desc">Solusi terpercaya untuk kebutuhan bahan kue dan sembako berkualitas. Kami hadir di berbagai cabang untuk melayani kebutuhan dapur Anda dengan sepenuh hati.</p>
                <div class="social-links">
                    <a href="https://www.instagram.com/sweetbake.official?igsh=MTl3dW5pY3J6aHEyYg==" target="_blank" class="social-icon" title="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" target="_blank" class="social-icon" title="Youtube">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.11 1 12 1 12s0 3.89.4 5.58a2.78 2.78 0 0 0 1.94 2c1.71.42 8.6.42 8.6.42s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.89 23 12 23 12s0-3.89-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                    </a>
                    <a href="https://wa.me/6282330755390" target="_blank" class="social-icon" title="WhatsApp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.411.002 12.046c0 2.121.54 4.192 1.566 6.033L0 24l6.135-1.61a11.81 11.81 0 005.911 1.569h.005c6.632 0 12.042-5.411 12.045-12.047a11.812 11.812 0 00-3.576-8.514z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Col 2: Other Page -->
            <div class="footer-col">
                <h4>Halaman Lain</h4>
                <ul class="footer-links">
                    <li><a href="#beranda" onclick="switchPage('beranda')">Beranda</a></li>
                    <li><a href="#promo-outlet" onclick="switchPage('promo-outlet')">Promo Spesial</a></li>
                    <li><a href="#outlet" onclick="scrollToCategory('outlet')">Cabang Kami</a></li>
                    <li><a href="#tentang-toko" onclick="smoothScroll('#tentang-toko')">Tentang Toko</a></li>
                    <li><a href="#keunggulan" onclick="smoothScroll('#keunggulan')">Keunggulan Kami</a></li>
                    <li><a href="#testimonials" onclick="smoothScroll('#testimonials')">Komentar Pelanggan</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} TWINS Bakery - Premium Quality Baking Supplies. All Rights Reserved.</p>
        </div>
    </footer>

    <nav class="mobile-nav">
        <div id="mob-home" class="mob-nav-item active" onclick="switchPage('beranda')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </div>

        <div id="mob-promo" class="mob-nav-item" onclick="switchPage('promo-outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
            <span>Promo</span>
        </div>

        <div id="mob-outlet" class="mob-nav-item" onclick="scrollToCategory('outlet')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l18 0l-1 10l-16 0z"></path><path d="M3 11l18 0"></path><path d="M2 3l20 0l-1 6l-18 0z"></path></svg>
            <span>Outlet</span>
        </div>

        <div id="mob-features" class="mob-nav-item" onclick="switchPage('keunggulan')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
            <span>Keunggulan</span>
        </div>
    </nav>

    <script src="{{ asset('js/home.js') }}?v={{ time() }}"></script>

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

    <!-- Animasi premium hero beranda -->
    <script src="{{ asset('js/premium-animations.js') }}"></script>
</body>
</html>