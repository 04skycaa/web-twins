<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="nama-brand">TWINS</div>
        <div class="teks-hero">
            <h1>Amankan Akunmu </h1>
            <p>Yuk buat kata sandi baru biar akunmu tetap aman dan kamu bisa lanjut belanja bahan kue favorit di Twins </p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/password.png') }}" alt="Visual 1" class="gambar-satu-password">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Reset Password</h2>
            <p class="subjudul-form">Silakan masukkan email Anda dan buat password baru yang kuat.</p>
            
            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="grup-input">
                    <label class="label-input">Email</label>
                    <input type="email" name="email" class="field-input" placeholder="nama@email.com" value="{{ old('email', $request->email) }}" required autofocus readonly>
                </div>

                <div class="grup-input">
                    <label class="label-input">Password Baru</label>
                    <div class="wrapper-password" style="position: relative;">
                        <input type="password" name="password" id="inputPass" class="field-input" placeholder="Min. 8 karakter" required style="padding-right: 45px;">
                        <div class="toggle-password" onclick="togglePassword('inputPass', 'eyeIcon')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                            <i id="eyeIcon" data-lucide="eye" style="width: 20px; color: #666;"></i>
                        </div>
                    </div>
                </div>

                <div class="grup-input">
                    <label class="label-input">Konfirmasi Password</label>
                    <div class="wrapper-password" style="position: relative;">
                        <input type="password" name="password_confirmation" id="inputConfirm" class="field-input" placeholder="Ulangi password baru" required style="padding-right: 45px;">
                        <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                            <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px; color: #666;"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="tombol-masuk">Perbarui Password</button>
            </form>

            <p style="margin-top: 30px; text-align: center; font-size: 14px; color: var(--warna-abu);">
                Ingat password Anda? 
                <a href="{{ route('login') }}" style="color: var(--warna-utama); font-weight: 700; text-decoration: none;">Masuk</a>
            </p>
        </div>
    </div>
</div>

<div id="session-data" 
     data-errors="{{ json_encode($errors->all()) }}" 
     data-status="{{ session('status') }}"
     data-confirmed="{{ session('auth.password_confirmed_at') }}">
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    lucide.createIcons();

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
        const sessionData = document.getElementById('session-data');
        const errors = JSON.parse(sessionData.dataset.errors || '[]');
        const status = sessionData.dataset.status;
        const confirmed = sessionData.dataset.confirmed;

        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Konfirmasi Gagal',
                text: 'Kata sandi yang Anda masukkan salah.',
                confirmButtonColor: '#0477bf',
                showClass: { popup: 'animate__animated animate__shakeX' }
            });
        }

        if (confirmed && !errors.length) {
            const now = Math.floor(Date.now() / 1000);
            if (now - confirmed < 5) { 
                Swal.fire({
                    icon: 'success',
                    title: 'Identitas Terverifikasi',
                    text: 'Akses diberikan. Silakan lanjut.',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        }
    });
</script>
</body>
</html>