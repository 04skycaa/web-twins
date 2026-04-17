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
        Schema::create('stock_card', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('product_id')->nullable();
            $table->integer('jmlh');
            $table->text('keterangan')->nullable();
            $table->uuid('store_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('uuid')->on('store')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_card');
    }
};
