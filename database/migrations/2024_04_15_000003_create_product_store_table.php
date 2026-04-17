<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_store', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('product_id')->nullable();
            $table->uuid('store_id')->nullable();
            $table->integer('stok')->default(0);
            $table->date('kadaluarsa')->nullable();
            $table->boolean('status_aktif')->default(true);
            
            $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('uuid')->on('store')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_store');
    }
};
