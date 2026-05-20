<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Verifikasi OTP Lupa Password</title>
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
            <h1>Verifikasi Akun</h1>
            <p>Masukkan 6 digit kode OTP yang kami kirimkan ke email Anda untuk melanjutkan pengaturan ulang kata sandi.</p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/password.png') }}" alt="Visual 1" class="gambar-satu-password">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Verifikasi OTP</h2>
            <p class="subjudul-form" style="margin-bottom: 25px;">
                Kode verifikasi telah dikirim ke <strong style="color: var(--warna-gelap);">{{ $email }}</strong>.
            </p>

            @if (session('status'))
                <div style="background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border-left: 5px solid #198754;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="background-color: #f8d7da; color: #842029; padding: 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border-left: 5px solid #f5c2c7;">
                    {{ $errors->first() }}
                </div>
            @endif

            <style>
                .container-otp {
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                    margin-bottom: 25px;
                }
                .otp-field {
                    width: 50px !important;
                    height: 52px !important;
                    text-align: center !important;
                    font-size: 22px !important;
                    font-weight: 700 !important;
                    padding: 0 !important;
                    border-radius: 12px !important;
                }
            </style>

            <form method="POST" action="{{ route('password.verify-otp') }}" id="verifyOtpForm" style="margin-bottom: 20px;">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="container-otp">
                    <input type="text" maxlength="1" class="field-input otp-field" required autofocus autocomplete="off">
                    <input type="text" maxlength="1" class="field-input otp-field" required autocomplete="off">
                    <input type="text" maxlength="1" class="field-input otp-field" required autocomplete="off">
                    <input type="text" maxlength="1" class="field-input otp-field" required autocomplete="off">
                    <input type="text" maxlength="1" class="field-input otp-field" required autocomplete="off">
                    <input type="text" maxlength="1" class="field-input otp-field" required autocomplete="off">
                </div>
                <input type="hidden" name="otp" id="otp_code">

                <button type="submit" class="tombol-masuk" style="width: 100%; margin-top: 5px;">
                    {{ __('Verifikasi OTP') }}
                </button>
            </form>

            <div class="countdown-timer" id="countdownContainer" style="font-size: 13px; color: var(--warna-abu); text-align: center; margin-bottom: 15px; display: none;">
                Kirim ulang kode tersedia dalam <span id="timerSeconds" style="font-weight: bold; color: var(--warna-utama);">60</span> detik.
            </div>

            <div class="aksi-verifikasi" style="display: flex; flex-direction: column; gap: 15px;">
                <form method="POST" action="{{ route('password.email') }}" id="resendForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="tombol-masuk" id="btnResend" style="width: 100%; background-color: white; color: var(--warna-utama); border: 2.5px solid var(--warna-utama); box-shadow: none; margin-top: 0; padding: 14px;">
                        {{ __('Kirim Ulang Kode') }}
                    </button>
                </form>

                <p style="text-align: center; margin-top: 10px;">
                    <a href="{{ route('password.request') }}" style="color: var(--warna-abu); font-size: 14px; text-decoration: underline;">
                        Kembali
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/auth.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fields = document.querySelectorAll('.otp-field');
        const hiddenInput = document.getElementById('otp_code');
        const form = document.getElementById('verifyOtpForm');

        fields.forEach((field, index) => {
            field.addEventListener('input', (e) => {
                field.value = field.value.replace(/[^0-9]/g, '');

                if (field.value.length === 1 && index < fields.length - 1) {
                    fields[index + 1].focus();
                }

                updateHiddenInput();
            });

            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && field.value.length === 0 && index > 0) {
                    fields[index - 1].value = '';
                    fields[index - 1].focus();
                }
            });

            field.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    fields.forEach((f, i) => {
                        f.value = pasteData[i];
                    });
                    updateHiddenInput();
                    fields[5].focus();
                }
            });
        });

        function updateHiddenInput() {
            let code = '';
            fields.forEach(f => code += f.value);
            hiddenInput.value = code;
        }

        form.addEventListener('submit', function(e) {
            updateHiddenInput();
            if (hiddenInput.value.length !== 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Opps!',
                    text: 'Silakan masukkan 6 digit kode OTP yang lengkap.',
                    confirmButtonColor: '#0477bf'
                });
            } else {
                tampilkanLoading(document.querySelector('#verifyOtpForm button[type="submit"]'));
            }
        });

        // Countdown Timer for Resend Button
        const btnResend = document.getElementById('btnResend');
        const countdownContainer = document.getElementById('countdownContainer');
        const timerSeconds = document.getElementById('timerSeconds');
        
        let countdownTime = 60;
        let timerInterval;

        function startTimer() {
            btnResend.disabled = true;
            btnResend.style.opacity = '0.5';
            btnResend.style.cursor = 'not-allowed';
            countdownContainer.style.display = 'block';
            
            countdownTime = 60;
            timerSeconds.innerText = countdownTime;

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                countdownTime--;
                timerSeconds.innerText = countdownTime;

                if (countdownTime <= 0) {
                    clearInterval(timerInterval);
                    btnResend.disabled = false;
                    btnResend.style.opacity = '1';
                    btnResend.style.cursor = 'pointer';
                    countdownContainer.style.display = 'none';
                }
            }, 1000);
        }

        // Start timer automatically
        startTimer();

        document.getElementById('resendForm').addEventListener('submit', function(e) {
            if (btnResend.disabled) {
                e.preventDefault();
                return;
            }
            tampilkanLoading(btnResend);
        });
    });
</script>
</body>
</html>
