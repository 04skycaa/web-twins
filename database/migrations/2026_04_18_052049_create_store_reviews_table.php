<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_reviews', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('store_id');
            $table->uuid('user_id');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('store_id')->references('uuid')->on('store')->onDelete('cascade');
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
        });

        // Add check constraint for rating 1-5
        DB::statement('ALTER TABLE store_reviews ADD CONSTRAINT rating_range_check CHECK (rating >= 1 AND rating <= 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_reviews');
    }
};
