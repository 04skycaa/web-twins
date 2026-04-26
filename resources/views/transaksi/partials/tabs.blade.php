<div class="tab-navigation">
    <a href="{{ route('transaksi.index') }}" class="tab-pill {{ request()->routeIs('transaksi.index') || request()->routeIs('transaksi.riwayat') ? 'active' : '' }}">
        <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
        <span>Riwayat Transaksi</span>
    </a>
    <a href="{{ route('transaksi.diskon') }}" class="tab-pill {{ request()->routeIs('transaksi.diskon') ? 'active' : '' }}">
        <iconify-icon icon="solar:sale-bold-duotone"></iconify-icon>
        <span>Manajemen Diskon</span>
    </a>
</div>

<style>
    :root {
        --primary-blue: #0081C9;
        --light-blue: #EEF7FF;
        --border-blue: #5EB7EB;
    }

    .tab-navigation {
        display: flex;
        justify-content: center;
        gap: 16px;
        margin-bottom: 30px;
    }

    .tab-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 24px;
        border-radius: 50px;
        border: 2px solid var(--primary-blue);
        background: white;
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 15px;
    }

    .tab-pill.active {
        background: var(--primary-blue);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 129, 201, 0.2);
    }

    .tab-pill iconify-icon {
        font-size: 24px;
    }
    
    @media (max-width: 768px) {
        .tab-navigation {
            flex-direction: column;
            align-items: stretch;
        }
        .tab-pill {
            justify-content: center;
        }
    }
</style>
