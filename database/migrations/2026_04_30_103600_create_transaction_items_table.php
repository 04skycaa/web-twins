<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transaction_items')) {
            Schema::create('transaction_items', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('transaction_id')->index();
                $table->uuid('product_id')->index();
                $table->integer('qty')->default(0);
                $table->decimal('harga_beli', 15, 2)->default(0);
                $table->decimal('harga_jual_baru', 15, 2)->default(0);
                $table->date('kadaluarsa')->nullable();
                $table->timestamps();

                $table->foreign('transaction_id')->references('uuid')->on('transactions')->onDelete('cascade');
                $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
