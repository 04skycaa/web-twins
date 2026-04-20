<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fitur;

class FiturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['id' => 1, 'nama' => 'dashboard'],
            ['id' => 2, 'nama' => 'produk'],
            ['id' => 3, 'nama' => 'transaksi'],
            ['id' => 4, 'nama' => 'keuangan'],
            ['id' => 5, 'nama' => 'users'],
            ['id' => 6, 'nama' => 'outlet'],
            ['id' => 7, 'nama' => 'Kelola Kontak'],
            ['id' => 8, 'nama' => 'Transaksi Keuangan'],
            ['id' => 9, 'nama' => 'Laporan'],
        ];

        foreach ($features as $feature) {
            Fitur::updateOrCreate(['id' => $feature['id']], $feature);
        }
    }
}
