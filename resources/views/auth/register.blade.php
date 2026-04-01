<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="nama-brand">TWINS</div>
        <div class="teks-hero">
            <h1>Join the Family</h1>
            <p>Daftar sekarang dan nikmati kemudahan belanja bahan kue premium hanya dalam satu aplikasi.</p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/toko-luar.png') }}" alt="Visual 1" class="gambar-satu">
            <div id="wrapperGambarDua" class="wrapper-gambar-dua-register">
                <img src="{{ asset('images/orang2.png') }}" alt="Visual 2" class="gambar-dua-register">
            </div>
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
                    <input type="text" name="name" class="field-input" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="grup-input">
                    <label class="label-input">Email</label>
                    <input type="email" name="email" class="field-input" placeholder="nama@email.com" value="{{ old('email') }}" required>
                </div>

                <div class="grup-input">
                    <label class="label-input">Nomor Handphone</label>
                    <input type="tel" name="no_hp" id="inputTel" class="field-input" placeholder="0812xxxxxxx" value="{{ old('no_hp') }}" required>
                </div>

                <div class="grup-input">
                    <label class="label-input">Password</label>
                    <div class="wrapper-password">
                        <input type="password" name="password" id="inputPass" class="field-input" placeholder="Min. 8 karakter" required>
                        <div class="toggle-password" onclick="togglePassword('inputPass', 'eyeIcon')">
                            <i id="eyeIcon" data-lucide="eye" style="width: 20px;"></i>
                        </div>
                    </div>
                </div>

                <div class="grup-input">
                    <label class="label-input">Konfirmasi Password</label>
                    <div class="wrapper-password">
                        <input type="password" name="password_confirmation" id="inputConfirm" class="field-input" placeholder="Ulangi password" required>
                        <div class="toggle-password" onclick="togglePassword('inputConfirm', 'eyeIconConfirm')">
                            <i id="eyeIconConfirm" data-lucide="eye" style="width: 20px;"></i>
                        </div>
                    </div>
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
     data-success="{{ session('success') }}">
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
        const successMessage = sessionData.dataset.success;

        if (errors.length > 0) {
            let listHtml = '<ul style="text-align: left; font-size: 14px; color: #555; list-style-type: none; padding: 0;">';
            errors.forEach(function(msg) {
                listHtml += `<li style="margin-bottom: 8px; display: flex; align-items: center;">
                    <span style="color: #ef4444; margin-right: 8px;">●</span> ${msg}
                </li>`;
            });
            listHtml += '</ul>';

            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal',
                html: listHtml,
                confirmButtonColor: '#0477bf',
                confirmButtonText: 'Coba Lagi',
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
        }

        if (successMessage) {
            Swal.fire({
                title: 'Berhasil!',
                text: successMessage,
                icon: 'success',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__backInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__backOutUp'
                }
            });
        }
    });
</script>
</body>
</html>