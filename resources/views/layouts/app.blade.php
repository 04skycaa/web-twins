<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TWINS Dashboard - Sidebar & Topbar</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</head>

<body>

    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="logo-img">
        </div>

        <nav class="menu-nav">
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:widget-4-bold-duotone"></iconify-icon>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/products') }}" class="menu-item {{ request()->is('products*') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                <span>Manajemen Produk</span>
            </a>

            <a href="#" class="menu-item {{ request()->is('promo*') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:tag-price-bold-duotone"></iconify-icon>
                <span>Promo & Marketing</span>
            </a>

            <a href="#" class="menu-item {{ request()->is('transaksi*') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                <span>Manajemen Transaksi</span>
            </a>

            <a href="#" class="menu-item {{ request()->is('keuangan*') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                <span>Keuangan</span>
            </a>

            <a href="#" class="menu-item {{ request()->is('outlet*') ? 'active' : '' }}">
                <div class="curve-helper"></div>
                <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                <span>Operasional & Outlet</span>
            </a>
        </nav>
    </aside>

    <div class="page-container">
        <header class="topbar">
            <div class="topbar-left">
                <i id="topbar-icon" data-lucide="layout-grid"></i>
                <h2 id="topbar-title">Dashboard</h2>
            </div>

            <div class="topbar-right">
                <div class="topbar-center">
                    <div class="greeting" id="greeting-text">Selamat Pagi</div>
                    <div class="datetime">
                        <span id="date-text">Selasa, 10 Maret 2026</span><br>
                        <span class="time-bold" id="time-text">06:00:15</span>
                    </div>

                </div>
                <div class="user-profile">
                    <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                    <span>agatca</span>
                </div>
                <button class="btn-logout">
                    <iconify-icon icon="solar:logout-3-bold-duotone"></iconify-icon>
                    <span>Logout</span>
                </button>
            </div>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
        function updateDateTime() {
            const now = new Date();
            const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', optionsDate);
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('date-text').innerText = dateStr;
            document.getElementById('time-text').innerText = timeStr;
            const hour = now.getHours();
            let greeting = "Selamat Malam";
            if (hour < 11) greeting = "Selamat Pagi";
            else if (hour < 15) greeting = "Selamat Siang";
            else if (hour < 19) greeting = "Selamat Sore";
            document.getElementById('greeting-text').innerText = greeting;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
        function setActive(element, title, iconName) {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
            document.getElementById('page-title').innerText = title;
            document.getElementById('topbar-title').innerText = title;
            const topIcon = document.getElementById('topbar-icon');
            topIcon.setAttribute('data-lucide', iconName);
            lucide.createIcons();
        }
    </script>
</body>
</html> 