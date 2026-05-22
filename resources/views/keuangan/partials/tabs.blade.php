<div class="tab-navigation">
    @php $__keuangan_tab = request('tab', 'cashbox'); @endphp
    <a href="javascript:void(0)" class="tab-pill {{ $__keuangan_tab === 'cashbox' ? 'active' : '' }}" id="pill-cashbox" onclick="switchTab('cashbox')">
        <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
        <span>Cashbox</span>
    </a>
    <a href="javascript:void(0)" class="tab-pill {{ $__keuangan_tab === 'arus-uang' ? 'active' : '' }}" id="pill-arus-uang" onclick="switchTab('arus-uang')">
        <iconify-icon icon="solar:round-transfer-horizontal-bold-duotone"></iconify-icon>
        <span>Arus Uang</span>
    </a>
</div>
