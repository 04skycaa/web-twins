<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Indexes for cash_flows table (Buku Kas & Keuangan)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_cash_flows_store_tanggal ON cash_flows(store_id, tanggal)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_cash_flows_jenis ON cash_flows(jenis)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_cash_flows_tanggal ON cash_flows(tanggal)');

        // 2. Indexes for transactions table (Kasir / POS, Restok, & Penjualan)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_store_tanggal ON transactions(store_id, tanggal)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_jenis ON transactions(jenis)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transactions_tanggal ON transactions(tanggal)');

        // 3. Indexes for product_store table (Pemetaan Stok & Kadaluarsa Produk per Toko)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_product_store_composite ON product_store(product_id, store_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_product_store_stok ON product_store(stok)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_product_store_kadaluarsa ON product_store(kadaluarsa)');

        // 4. Indexes for debts table (Hutang & Piutang)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_debts_store_tipe_sisa ON debts(store_id, tipe, sisa)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_debts_jatuh_tempo ON debts(jatuh_tempo)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes safely
        DB::statement('DROP INDEX IF EXISTS idx_cash_flows_store_tanggal');
        DB::statement('DROP INDEX IF EXISTS idx_cash_flows_jenis');
        DB::statement('DROP INDEX IF EXISTS idx_cash_flows_tanggal');

        DB::statement('DROP INDEX IF EXISTS idx_transactions_store_tanggal');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_jenis');
        DB::statement('DROP INDEX IF EXISTS idx_transactions_tanggal');

        DB::statement('DROP INDEX IF EXISTS idx_product_store_composite');
        DB::statement('DROP INDEX IF EXISTS idx_product_store_stok');
        DB::statement('DROP INDEX IF EXISTS idx_product_store_kadaluarsa');

        DB::statement('DROP INDEX IF EXISTS idx_debts_store_tipe_sisa');
        DB::statement('DROP INDEX IF EXISTS idx_debts_jatuh_tempo');
    }
};
