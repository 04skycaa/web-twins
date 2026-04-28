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
        Schema::create('payment_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_order_id')->constrained('payment_orders')->cascadeOnDelete();
            $table->string('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('payment_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_order_items');
    }
};
