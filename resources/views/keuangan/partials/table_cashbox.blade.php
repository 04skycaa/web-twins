        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>Nama Metode</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cashbox">
                        @forelse($cashboxes as $cb)
                            <tr>
                                <td style="font-weight: 600;">{{ $cb->nama_metode }}</td>
                                <td>
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="openEditCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')" title="Edit">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')" title="Hapus">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="text-align: center; padding: 30px; color: #888;">Belum ada data Cashbox.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
