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
        // 1. Index on transaction_detail(transaction_id) for high performance joins
        DB::statement('CREATE INDEX IF NOT EXISTS idx_transaction_detail_transaction_id ON transaction_detail(transaction_id)');

        // 2. Index on payment_orders(paid_at) to avoid full table scans on payment/order date ranges
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payment_orders_paid_at ON payment_orders(paid_at)');

        // 3. Index on payment_order_items(payment_order_id) for high performance online checkout joins
        DB::statement('CREATE INDEX IF NOT EXISTS idx_payment_order_items_order_id ON payment_order_items(payment_order_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_transaction_detail_transaction_id');
        DB::statement('DROP INDEX IF EXISTS idx_payment_orders_paid_at');
        DB::statement('DROP INDEX IF EXISTS idx_payment_order_items_order_id');
    }
};
