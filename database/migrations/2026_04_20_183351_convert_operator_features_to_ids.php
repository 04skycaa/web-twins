<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Operator;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mapping = [
            'dashboard' => 1,
            'produk' => 2,
            'manajemen produk' => 2,
            'transaksi' => 3,
            'transaksi & bonus' => 3,
            'keuangan' => 4,
            'users' => 5,
            'manajemen user' => 5,
            'outlet' => 6,
            'operasional outlet' => 6,
            'kelola kontak' => 7,
            'transaksi keuangan' => 8,
            'laporan' => 9,
        ];

        $operators = Operator::all();

        foreach ($operators as $operator) {
            if (!$operator->fitur || !is_array($operator->fitur)) {
                continue;
            }

            $newFeatures = [];
            foreach ($operator->fitur as $feat) {
                if (is_numeric($feat)) {
                    $newFeatures[] = (int)$feat;
                    continue;
                }

                $lowFeat = strtolower($feat);
                if (isset($mapping[$lowFeat])) {
                    $newFeatures[] = $mapping[$lowFeat];
                } elseif ($feat === 'all_access') {
                    $newFeatures[] = $feat; 
                }
            }

            $operator->fitur = array_unique($newFeatures);
            $operator->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing is complex as the mapping isn't 1:1 for names, but we'll try basic ones
        $mapping = [
            1 => 'dashboard',
            2 => 'produk',
            3 => 'transaksi',
            4 => 'keuangan',
            5 => 'users',
            6 => 'outlet',
            7 => 'kelola kontak',
            8 => 'transaksi keuangan',
            9 => 'laporan',
        ];

        $operators = Operator::all();

        foreach ($operators as $operator) {
            if (!$operator->fitur || !is_array($operator->fitur)) {
                continue;
            }

            $oldFeatures = [];
            foreach ($operator->fitur as $feat) {
                if (isset($mapping[$feat])) {
                    $oldFeatures[] = $mapping[$feat];
                } else {
                    $oldFeatures[] = $feat;
                }
            }

            $operator->fitur = array_unique($oldFeatures);
            $operator->save();
        }
    }
};
