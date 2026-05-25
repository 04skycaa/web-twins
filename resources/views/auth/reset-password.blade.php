<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Reset Password</title>
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
            <h1>Password Baru</h1>
            <p>Buat kata sandi baru yang kuat untuk menjaga keamanan akun belanja Twins Anda.</p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/password.png') }}" alt="Visual 1" class="gambar-satu-password">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Buat Password Baru</h2>
            <p class="subjudul-form">Masukkan password baru kamu untuk kembali ke akun Twins dan lanjut belanja bahan kue favoritmu </p>
            
            <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="grup-input">
                    <label class="label-input">Email</label>
                    <input type="email" name="email" class="field-input @error('email') is-invalid @enderror" 
                           value="{{ old('email', $request->email) }}" required readonly 
                           style="background-color: #f9f9f9; cursor: not-allowed;">
                    @error('email')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Password Baru</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="inputPass" class="field-input @error('password') is-invalid @enderror" 
                               placeholder="Minimal 8 karakter" required autofocus style="padding-right: 45px;">
                        
                        <div class="toggle-password" onclick="togglePassword('inputPass', 'eyeIcon')" 
                             style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center;">
                            <i id="eyeIcon" data-lucide="eye" style="width: 20px; color: #666;"></i>
                        </div>
                    </div>
                    @error('password')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grup-input">
                    <label class="label-input">Ulangi Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password_confirmation" id="inputConfirm" class="field-input @error('password_confirmation') is-invalid @enderror" 
                               placeholder="Konfirmasi password baru" required style="padding-right: 45px;">
                        
                        <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')" 
                             style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center;">
                            <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px; color: #666;"></i>
                        </div>
                    </div>
                    @error('password_confirmation')
                        <span class="pesan-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="tombol-masuk" onclick="tampilkanLoading(this)">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</div>

<div id="session-data" 
     data-errors="{{ json_encode($errors->all()) }}" 
     data-error="{{ session('error') }}">
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>