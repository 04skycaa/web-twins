    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TWINS - Login</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body>

    <div class="kontainer-utama">
        <div id="overlaySukses" class="overlay-status overlay-sukses" style="display: none; opacity: 0;">
            <div class="kartu-status">
                <div class="container-ikon">
                    <div class="ring ring-1"></div>
                    <div class="ring ring-2"></div>
                    <div class="pusat-ikon">
                        <svg class="ikon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
                <h2 class="judul-status">Memproses...</h2>
                <p class="teks-status">Mohon tunggu sebentar, kami sedang memverifikasi akun kamu.</p>
                <div class="loader-bar">
                    <div id="progressBar" class="loader-fill"></div>
                </div>
            </div>
        </div>

        @if ($errors->any())
        <div id="overlayGagal" class="overlay-status overlay-gagal" style="display: flex; opacity: 1;">
            <div class="kartu-status">
                <div class="container-ikon">
                    <div class="ring ring-1"></div>
                    <div class="ring ring-2"></div>
                    <div class="pusat-ikon">
                        <svg class="ikon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                </div>
                <h2 class="judul-status">Login Gagal</h2>
                <p class="teks-status">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </p>
                <button class="tombol-ulang" onclick="tutupOverlay('overlayGagal')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 4v6h-6"></path>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Coba Lagi
                </button>
            </div>
        </div>
        @endif

        <div class="panel-visual">
            <div class="nama-brand">TWINS</div>
            <div class="teks-hero">
                <h1>Welcome Back</h1>
                <p>Belanja bahan kue jadi lebih gampang di Twins. Lengkap, cepat, dan siap bantu kamu bikin kue impian.</p>
            </div>

            <div class="container-visual-bawah">
                <img src="{{ asset('images/toko-luar.png') }}" alt="Visual 1" class="gambar-satu">
                <div id="wrapperGambarDua" class="wrapper-gambar-dua">
                    <img src="{{ asset('images//orang.png') }}" alt="Visual 2" class="gambar-dua">
                </div>
            </div>
        </div>

        <div class="panel-form">
            <div class="bungkus-form">
                <h2 class="judul-form">Login</h2>
                <p class="subjudul-form">Masuk ke akun Twins kamu dan mulai belanja bahan kue dengan mudah.</p>
                
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    
                    <div class="grup-input">
                        <label class="label-input">Email</label>
                        <input type="email" name="email" id="inputEmail" class="field-input" 
                            placeholder="email@example.com" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="grup-input">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="label-input">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" style="font-size: 12px; color: var(--warna-utama); text-decoration: none; font-weight: 600;">Lupa kata sandi?</a>
                            @endif
                        </div>

                        <div style="position: relative;">
                            <input type="password" name="password" id="inputConfirm" class="field-input" placeholder="••••••••" required style="padding-right: 45px;">
                            
                            <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center;">
                                <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px; color: #666;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="opsi-tambahan">
                        <input type="checkbox" name="remember" id="remember_me" style="width: 16px; height: 16px; accent-color: var(--warna-utama);">
                        <label for="remember_me">Ingat saya pada perangkat ini</label>
                    </div>

                    <button type="submit" class="tombol-masuk" onclick="mulaiAnimasi(event)">Log In</button>
                </form>

                <p style="margin-top: 40px; text-align: center; font-size: 14px; color: var(--warna-abu);">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" style="color: var(--warna-utama); font-weight: 700; text-decoration: none;">Daftar</a>
                </p>
            </div>
        </div>
    </div>

    <div id="session-status" data-value="{{ session('status') }}"></div>
    <div id="session-success" data-value="{{ session('success') }}"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusMsg = document.getElementById('session-status').dataset.value;
            const successMsg = document.getElementById('session-success').dataset.value;
            
            const messageToShow = statusMsg || successMsg;

            if (messageToShow) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: messageToShow,
                    icon: 'success',
                    confirmButtonColor: '#0477bf',
                    timer: 2000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    }
                });
            }
        });

        function togglePassword(inputId, iconId) {
            const passInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons(); 
        }

        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        function mulaiAnimasi(e) {
            const form = document.getElementById('loginForm');
            
            if (form.checkValidity()) {
                e.preventDefault();

                const wrapperDua = document.getElementById('wrapperGambarDua');
                const overlaySukses = document.getElementById('overlaySukses');
                const bar = document.getElementById('progressBar');

                wrapperDua.classList.add('image-slide-out-right');

                setTimeout(() => {
                    overlaySukses.style.display = 'flex';
                    overlaySukses.style.opacity = '1'; // Pastikan opacity terlihat

                    setTimeout(() => {
                        bar.style.width = '100%';
                    }, 100);

                    setTimeout(() => {
                        form.submit();
                    }, 2500);

                }, 600);
            }
        }

        function tutupOverlay(id) {
            const overlay = document.getElementById(id);
            overlay.style.opacity = '0';
            
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    </script>

    </body>
    </html>