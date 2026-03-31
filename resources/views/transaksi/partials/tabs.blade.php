<div class="tabs-container">
    <a href="{{ route('transaksi.index') }}" class="tab-item {{ request()->routeIs('transaksi.index') || request()->routeIs('transaksi.riwayat') ? 'active' : '' }}">
        <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
        Riwayat Transaksi
    </a>
    <a href="{{ route('transaksi.diskon') }}" class="tab-item {{ request()->routeIs('transaksi.diskon') ? 'active' : '' }}">
        <iconify-icon icon="solar:sale-bold-duotone"></iconify-icon>
        Manajemen Diskon
    </a>
</div>

<style>
    .tabs-container {
        display: flex;
        gap: 10px;
        background: #fff;
        padding: 10px;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        margin-bottom: 25px;
        overflow-x: auto;
    }
    .tab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        color: #64748b;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .tab-item:hover {
        background: #f8fafc;
        color: #334155;
    }
    .tab-item.active {
        background: #e0f2fe;
        color: #0ea5e9;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(14, 165, 233, 0.1);
    }
    .tab-item iconify-icon {
        font-size: 20px;
    }
</style>
