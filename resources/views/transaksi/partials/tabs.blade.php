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

