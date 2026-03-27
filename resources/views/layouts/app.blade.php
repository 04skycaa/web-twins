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
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text">TWINS</span>
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
                @if(request()->routeIs('dashboard'))
                    <i id="topbar-icon" data-lucide="layout-grid"></i>
                    <h2 id="topbar-title">Dashboard</h2>
                @elseif(request()->is('products*'))
                    <i id="topbar-icon" data-lucide="package"></i>
                    <h2 id="topbar-title">Manajemen Produk</h2>
                @elseif(request()->is('promo*'))
                    <i id="topbar-icon" data-lucide="ticket-percent"></i>
                    <h2 id="topbar-title">Promo & Marketing</h2>
                @elseif(request()->is('transaksi*'))
                    <i id="topbar-icon" data-lucide="receipt"></i>
                    <h2 id="topbar-title">Manajemen Transaksi</h2>
                @elseif(request()->is('keuangan*'))
                    <i id="topbar-icon" data-lucide="trending-up"></i>
                    <h2 id="topbar-title">Keuangan</h2>
                @elseif(request()->is('outlet*'))
                    <i id="topbar-icon" data-lucide="store"></i>
                    <h2 id="topbar-title">Operasional & Outlet</h2>
                @else
                    <i id="topbar-icon" data-lucide="layers"></i>
                    <h2 id="topbar-title">Halaman</h2>
                @endif
            </div>

            <div class="topbar-right">
                <div class="topbar-center">
                    <div class="greeting" id="greeting-text">Selamat Pagi</div>
                    <div class="datetime">
                        <span id="date-text"></span><br>
                        <span class="time-bold" id="time-text"></span>
                    </div>
                </div>
                <div class="user-profile">
                    <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                    <span>{{ Auth::user()->name ?? 'Guest' }}</span>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <button class="btn-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
            
            const dateEl = document.getElementById('date-text');
            const timeEl = document.getElementById('time-text');
            const greetEl = document.getElementById('greeting-text');

            if(dateEl) dateEl.innerText = dateStr;
            if(timeEl) timeEl.innerText = timeStr;
            
            const hour = now.getHours();
            let greeting = "Selamat Malam";
            if (hour < 11) greeting = "Selamat Pagi";
            else if (hour < 15) greeting = "Selamat Siang";
            else if (hour < 19) greeting = "Selamat Sore";
            if(greetEl) greetEl.innerText = greeting;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        function setActive(element, title, iconName) {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
            
            const titleEl = document.getElementById('topbar-title');
            const topIcon = document.getElementById('topbar-icon');
            
            if(titleEl) titleEl.innerText = title;
            if(topIcon) {
                topIcon.setAttribute('data-lucide', iconName);
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>