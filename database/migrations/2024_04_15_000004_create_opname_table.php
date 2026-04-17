<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->timestamp('tanggal')->useCurrent();
            $table->uuid('store_id')->nullable();
            $table->uuid('user_id')->nullable();
            
            $table->foreign('store_id')->references('uuid')->on('store')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // standard users table usually has 'id' but checking user's SQL
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname');
    }
};
