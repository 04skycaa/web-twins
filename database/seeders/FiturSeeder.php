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
            ['id' => 1, 'nama' => 'dashboard', 'ikon' => 'solar:widget-4-bold-duotone', 'route' => 'dashboard'],
            ['id' => 2, 'nama' => 'produk', 'ikon' => 'solar:box-minimalistic-bold-duotone', 'route' => 'products.index'],
            ['id' => 3, 'nama' => 'transaksi', 'ikon' => 'solar:bill-list-bold-duotone', 'route' => 'transaksi.index'],
            ['id' => 4, 'nama' => 'keuangan', 'ikon' => 'solar:graph-up-bold-duotone', 'route' => 'keuangan.index'],
            ['id' => 5, 'nama' => 'users', 'ikon' => 'solar:users-group-rounded-bold-duotone', 'route' => 'users.index'],
            ['id' => 6, 'nama' => 'outlet', 'ikon' => 'solar:shop-2-bold-duotone', 'route' => 'outlet.index'],
            ['id' => 7, 'nama' => 'Kelola Kontak', 'ikon' => 'solar:phone-calling-bold-duotone', 'route' => 'kontak.index'],
            ['id' => 8, 'nama' => 'Transaksi Keuangan', 'ikon' => 'solar:wallet-money-bold-duotone', 'route' => 'keuangan.transaksi'],
            ['id' => 9, 'nama' => 'Laporan', 'ikon' => 'solar:document-text-bold-duotone', 'route' => 'laporan.index'],
            ['id' => 10, 'nama' => 'Absensi', 'ikon' => 'solar:calendar-check-bold-duotone', 'route' => 'absensi.index'],
        ];

        foreach ($features as $feature) {
            Fitur::updateOrCreate(['id' => $feature['id']], $feature);
        }
    }
}
