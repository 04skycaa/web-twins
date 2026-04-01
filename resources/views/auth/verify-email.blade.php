<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS - Verifikasi Email</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="kontainer-utama">
    <div class="panel-visual">
        <div class="nama-brand">TWINS</div>
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
                {{ __('Kami telah mengirimkan link verifikasi ke email kamu. Belum menerima email? Klik tombol di bawah untuk kirim ulang.') }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <div style="background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border-left: 5px solid #198754;">
                    {{ __('Link verifikasi baru telah dikirim ke alamat email yang kamu daftarkan.') }}
                </div>
            @endif

            <div class="aksi-verifikasi" style="display: flex; flex-direction: column; gap: 15px;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="tombol-masuk" style="width: 100%;">
                        {{ __('Kirim Ulang Email Verifikasi') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="text-align: center;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: var(--warna-abu); font-size: 14px; text-decoration: underline; cursor: pointer; font-family: 'Outfit', sans-serif;">
                        {{ __('Keluar / Log Out') }}
                    </button>
                </form>
            </div>

            <p style="margin-top: 40px; text-align: center; font-size: 13px; color: var(--warna-abu); line-height: 1.6;">
                Butuh bantuan? Hubungi WhatsApp admin kami jika kamu mengalami kendala dalam verifikasi.
            </p>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>