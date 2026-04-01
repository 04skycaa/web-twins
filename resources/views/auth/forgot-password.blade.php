<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Lupa Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="nama-brand">TWINS</div>
        <div class="teks-hero">
            <h1>Lupa Password?</h1>
            <p>Tenang aja! Masukkan email kamu, nanti kami kirim link buat atur ulang password biar kamu bisa lanjut belanja di Twins </p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/password.png') }}" alt="Visual 1" class="gambar-satu-password">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Lupa Password?</h2>
            <p class="subjudul-form">Masukkan email yang terdaftar untuk menerima instruksi reset password.</p>
            
            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf

                <div class="grup-input">
                    <label class="label-input">Alamat Email</label>
                    <input type="email" name="email" class="field-input" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                </div>

                <button type="submit" class="tombol-masuk" onclick="tampilkanLoading(this)">Kirim Link Reset</button>
            </form>

            <p style="margin-top: 30px; text-align: center; font-size: 14px; color: var(--warna-abu);">
                Tiba-tiba ingat? 
                <a href="{{ route('login') }}" style="color: var(--warna-utama); font-weight: 700; text-decoration: none;">Kembali ke Login</a>
            </p>
        </div>
    </div>
</div>

<div id="session-data" 
     data-errors="{{ json_encode($errors->all()) }}" 
     data-status="{{ session('status') }}">
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    lucide.createIcons();

    function tampilkanLoading(btn) {
        const form = document.getElementById('forgotForm');
        if(form.checkValidity()) {
            btn.innerHTML = 'Mengirim...';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sessionData = document.getElementById('session-data');
        const errors = JSON.parse(sessionData.dataset.errors || '[]');
        const statusMessage = sessionData.dataset.status;

        if (statusMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Email Terkirim!',
                text: statusMessage,
                confirmButtonColor: '#0477bf',
                showClass: { popup: 'animate__animated animate__fadeInDown' }
            });
        }

        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Opps!',
                text: errors[0], 
                confirmButtonColor: '#0477bf',
                showClass: { popup: 'animate__animated animate__shakeX' }
            });
        }
    });
</script>
</body>
</html>