<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_requests', function (Blueprint $blueprint) {
            $blueprint->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $blueprint->uuid('product_id')->nullable();
            $blueprint->integer('jumlah_minta')->default(1);
            $blueprint->string('prioritas')->default('Sedang');
            $blueprint->string('status')->default('Pending');
            $blueprint->string('pemohon');
            $blueprint->text('alasan_permintaan')->nullable();

            $blueprint->foreign('product_id')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};
