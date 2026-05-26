<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Register</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="header-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo TWINS" class="logo-brand-bulat">
            <span class="nama-brand-teks">TWINS</span>
        </div>
        <div class="teks-hero">
            <h1>Gabung Sekarang</h1>
            <p>Daftar dan nikmati kemudahan belanja bahan kue premium hanya dalam satu aplikasi.</p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/register.png') }}" alt="Visual 1" class="gambar-register">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Daftar Akun</h2>
            <p class="subjudul-form">Lengkapi data di bawah ini untuk mulai bergabung dengan Twins.</p>
                        <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="grup-input">
                    <label class="label-input">Nama Lengkap</label>
                    <input type="text" name="name" class="field-input @error('name') is-invalid @enderror" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" autofocus>
                    @error('name')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Gmail</label>
                    <input type="email" name="email" class="field-input @error('email') is-invalid @enderror" placeholder="nama@gmail.com" value="{{ old('email') }}">
                    @error('email')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Nomor Handphone</label>
                    <input type="tel" name="no_hp" id="inputTel" class="field-input @error('no_hp') is-invalid @enderror" placeholder="0812xxxxxxx" value="{{ old('no_hp') }}">
                    @error('no_hp')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Password</label>
                    <div class="wrapper-password">
                        <input type="password" name="password" id="inputPass" class="field-input @error('password') is-invalid @enderror" placeholder="Min. 8 karakter">
                        <div class="toggle-password" onclick="togglePassword('inputPass', 'eyeIcon')">
                            <i id="eyeIcon" data-lucide="eye" style="width: 20px;"></i>
                        </div>
                    </div>
                    @error('password')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Konfirmasi Password</label>
                    <div class="wrapper-password">
                        <input type="password" name="password_confirmation" id="inputConfirm" class="field-input @error('password_confirmation') is-invalid @enderror" placeholder="Ulangi password">
                        <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')">
                            <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px;"></i>
                        </div>
                    </div>
                    @error('password_confirmation')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="tombol-masuk">Buat Akun</button>
            </form>

            <p style="margin-top: 30px; text-align: center; font-size: 14px; color: var(--warna-abu);">
                Sudah punya akun? 
                <a href="{{ route('login') }}" style="color: var(--warna-utama); font-weight: 700; text-decoration: none;">Masuk</a>
            </p>
        </div>
    </div>
</div>

<div id="session-data" 
     data-errors="{{ json_encode($errors->all()) }}" 
     data-error="{{ session('error') }}" 
     data-success="{{ session('success') }}">
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>