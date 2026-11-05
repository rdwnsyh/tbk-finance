<?php

namespace Database\Seeders;

use App\Models\Coa;
use App\Models\CoaCategory;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    /**
     * Mengisi 8 COA sesuai sheet "Contoh Master dan Transaksi".
     *
     * Seeder ini harus dijalankan SETELAH CoaCategorySeeder karena
     * `coa_category_id` membutuhkan kategori yang sudah ada.
     */
    public function run(): void
    {
        // `pluck('id', 'name')` menghasilkan array [nama kategori => id].
        // Tujuannya agar kita tidak melakukan query kategori di dalam loop
        // (mencegah N+1) dan tidak bergantung pada id yang bersifat auto-increment.
        $categoryIds = CoaCategory::pluck('id', 'name');

        $coas = [
            ['code' => 401, 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
            ['code' => 402, 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
            ['code' => 403, 'name' => 'Profit Trading', 'category' => 'Other Income'],
            ['code' => 601, 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
            ['code' => 602, 'name' => 'Bensin', 'category' => 'Transport Expense'],
            ['code' => 603, 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ['code' => 604, 'name' => 'Makan Siang', 'category' => 'Meal Expense'],
            // Ejaan "Makana" dipertahankan persis seperti di file Excel.
            ['code' => 605, 'name' => 'Makana Pokok Bulanan', 'category' => 'Meal Expense'],
        ];

        foreach ($coas as $coa) {
            Coa::updateOrCreate(
                // Kode COA unik, jadi aman dipakai sebagai kunci pencarian.
                ['code' => $coa['code']],
                [
                    'name' => $coa['name'],
                    'coa_category_id' => $categoryIds[$coa['category']],
                ],
            );
        }
    }
}
