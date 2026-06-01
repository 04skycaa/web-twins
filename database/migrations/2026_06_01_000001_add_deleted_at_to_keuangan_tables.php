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
        if (Schema::hasTable('cash_flows')) {
            Schema::table('cash_flows', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        
        if (Schema::hasTable('debts')) {
            Schema::table('debts', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        
        if (Schema::hasTable('detail_debts')) {
            Schema::table('detail_debts', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cash_flows')) {
            Schema::table('cash_flows', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        
        if (Schema::hasTable('debts')) {
            Schema::table('debts', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        
        if (Schema::hasTable('detail_debts')) {
            Schema::table('detail_debts', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
