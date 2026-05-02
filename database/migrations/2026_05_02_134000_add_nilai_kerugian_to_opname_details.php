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
        if (!Schema::hasColumn('opname_detail', 'nilai_kerugian')) {
            Schema::table('opname_detail', function (Blueprint $table) {
                $table->decimal('nilai_kerugian', 15, 2)->default(0);
            });
        }
        
        if (!Schema::hasColumn('opname_detail', 'alasan_selisih')) {
            Schema::table('opname_detail', function (Blueprint $table) {
                $table->text('alasan_selisih')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_detail', function (Blueprint $table) {
            $table->dropColumn(['nilai_kerugian', 'alasan_selisih']);
        });
    }
};
