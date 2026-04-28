<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_order_items', function (Blueprint $table) {
            // Tambah field untuk diskon per item
            $table->decimal('discount_percent', 5, 4)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percent');
            $table->decimal('final_price', 15, 2)->default(0)->after('discount_amount')->comment('Harga akhir setelah diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'final_price']);
        });
    }
};
