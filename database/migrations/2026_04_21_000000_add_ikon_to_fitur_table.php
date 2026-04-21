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
        Schema::table('fitur', function (Blueprint $table) {
            $table->string('ikon')->nullable()->after('nama');
            $table->string('route')->nullable()->after('ikon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitur', function (Blueprint $table) {
            $table->dropColumn(['ikon', 'route']);
        });
    }
};
