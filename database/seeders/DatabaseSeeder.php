<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Entry point `php artisan db:seed`.
     */
    public function run(): void
    {
        // Urutan WAJIB: kategori -> COA -> transaksi.
        // Kalau dibalik, foreign key (coa_category_id / coa_id) akan gagal
        // karena data induknya belum tersedia.
        $this->call([
            CoaCategorySeeder::class,
            CoaSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
