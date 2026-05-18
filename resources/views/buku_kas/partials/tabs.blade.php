<div class="tab-navigation">
    @php $__bk_tab = request('tab', 'pengeluaran'); @endphp
    <a href="javascript:void(0)" class="tab-pill {{ $__bk_tab === 'pengeluaran' ? 'active' : '' }}" id="pill-pengeluaran" onclick="switchTab('pengeluaran')">
        <iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>
        <span>Pengeluaran</span>
    </a>
    <a href="javascript:void(0)" class="tab-pill {{ $__bk_tab === 'pemasukan' ? 'active' : '' }}" id="pill-pemasukan" onclick="switchTab('pemasukan')">
        <iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>
        <span>Pemasukan Lainnya</span>
    </a>
    <a href="javascript:void(0)" class="tab-pill {{ $__bk_tab === 'hutang' ? 'active' : '' }}" id="pill-hutang" onclick="switchTab('hutang')">
        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
        <span>Hutang</span>
    </a>
    <a href="javascript:void(0)" class="tab-pill {{ $__bk_tab === 'piutang' ? 'active' : '' }}" id="pill-piutang" onclick="switchTab('piutang')">
        <iconify-icon icon="solar:hand-money-bold-duotone"></iconify-icon>
        <span>Piutang</span>
    </a>
</div>
