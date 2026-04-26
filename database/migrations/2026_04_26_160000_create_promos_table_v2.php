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
        // 1. Create promo table
        Schema::create('promo', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nama_promo');
            $table->string('kode_promo')->nullable()->unique();
            $table->string('tipe'); // Poster, Diskon, Voucer
            $table->decimal('nilai', 15, 2)->default(0);
            $table->timestamp('tanggal_mulai');
            $table->timestamp('tanggal_selesai');
            $table->boolean('status')->nullable()->default(true);
            $table->string('image_banner')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 2. Create promo_products table
        Schema::create('promo_products', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('promo_id')->nullable();
            $table->uuid('product_id')->nullable();
            $table->string('tipe_diskon')->nullable()->default('persen');
            $table->decimal('nilai_diskon', 15, 2)->nullable()->default(0);

            $table->foreign('promo_id')->references('uuid')->on('promo')->onDelete('cascade');
            $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
        });

        // 3. Create promo_store table
        Schema::create('promo_store', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('promo_id')->nullable();
            $table->uuid('store_id')->nullable();

            $table->foreign('promo_id')->references('uuid')->on('promo')->onDelete('cascade');
            $table->foreign('store_id')->references('uuid')->on('store')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_store');
        Schema::dropIfExists('promo_products');
        Schema::dropIfExists('promo');
    }
};
