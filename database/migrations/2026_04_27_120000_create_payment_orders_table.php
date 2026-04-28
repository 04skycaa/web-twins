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
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('user_id')->nullable();
            $table->string('outlet_id')->nullable();

            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->decimal('delivery_distance_km', 8, 2)->default(0);

            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 4)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->string('currency', 10)->default('IDR');
            $table->string('payment_gateway', 30)->default('midtrans');
            $table->string('snap_token')->nullable();

            $table->string('payment_status', 30)->default('pending');
            $table->string('midtrans_transaction_status', 50)->nullable();
            $table->string('midtrans_payment_type', 50)->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_fraud_status', 30)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->json('meta')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();

            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
