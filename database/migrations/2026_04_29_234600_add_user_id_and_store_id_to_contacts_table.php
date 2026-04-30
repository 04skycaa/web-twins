<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'user_id')) {
                $table->uuid('user_id')->nullable()->index();
            }
            if (!Schema::hasColumn('contacts', 'store_id')) {
                $table->uuid('store_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'store_id']);
        });
    }
};
