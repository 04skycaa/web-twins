<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Verifikasi Email</title>
    <link rel="icon" href="{{ asset('images/logo2.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="header-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo TWINS" class="logo-brand-bulat">
            <span class="nama-brand-teks">TWINS</span>
        </div>
        <div class="teks-hero">
            <h1>Satu Langkah Lagi! </h1>
            <p>Hampir selesai! Cek email kamu dan verifikasi akun biar kamu bisa langsung mulai belanja bahan kue favorit di Twins</p>
        </div>

        <div class="container-visual-bawah">
            <img src="{{ asset('images/verif.png') }}" alt="Visual 1" class="gambar-satu-verif">
        </div>
    </div>

    <div class="panel-form">
        <div class="bungkus-form">
            <h2 class="judul-form">Verifikasi Email</h2>
            
            <p class="subjudul-form" style="margin-bottom: 25px;">
                {{ __('Kami telah mengirimkan 6 digit kode OTP ke email kamu. Masukkan kode tersebut di bawah untuk mengaktifkan akun.') }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <div style="background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border-left: 5px solid #198754;">
                    {{ __('Kode OTP baru telah dikirim ke alamat email Anda.') }}
                </div>
            @endif

            @if (session('error'))
                <div style="background-color: #f8d7da; color: #842029; padding: 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border-left: 5px solid #f5c2c7;">
                    {{ session('error') }}
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

            <form method="POST" action="{{ route('register.verify-otp') }}" id="verifyOtpForm" style="margin-bottom: 20px;">
                @csrf
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
                    {{ __('Verifikasi Akun') }}
                </button>
            </form>

            <div class="countdown-timer" id="countdownContainer" style="font-size: 13px; color: var(--warna-abu); text-align: center; margin-bottom: 15px; display: none;">
                Kirim ulang OTP tersedia dalam <span id="timerSeconds" style="font-weight: bold; color: var(--warna-utama);">60</span> detik.
            </div>

            <div class="aksi-verifikasi" style="display: flex; flex-direction: column; gap: 15px;">
                <form method="POST" action="{{ route('register.resend-otp') }}" id="resendForm">
                    @csrf
                    <button type="submit" class="tombol-masuk" id="btnResend" style="width: 100%; background-color: white; color: var(--warna-utama); border: 2.5px solid var(--warna-utama); box-shadow: none; margin-top: 0; padding: 14px;">
                        {{ __('Kirim Ulang OTP') }}
                    </button>
                </form>

                <div style="text-align: center;">
                    <a href="{{ route('register.verify-cancel') }}" style="color: var(--warna-abu); font-size: 14px; text-decoration: underline; font-family: 'Outfit', sans-serif;">
                        {{ __('Batal & Kembali ke Registrasi') }}
                    </a>
                </div>
            </div>

            <p style="margin-top: 40px; text-align: center; font-size: 13px; color: var(--warna-abu); line-height: 1.6;">
                Butuh bantuan? Hubungi WhatsApp admin kami jika kamu mengalami kendala dalam verifikasi.
            </p>
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
                // Allow only numbers
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

        // Start timer automatically on page load if status is sent
        @if (session('status') == 'verification-link-sent' || session('success'))
            startTimer();
        @endif

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