<?php

namespace Database\Seeders;

use App\Models\CoaCategory;
use Illuminate\Database\Seeder;

class CoaCategorySeeder extends Seeder
{
    /**
     * Mengisi 5 kategori COA sesuai sheet "Contoh Master dan Transaksi".
     */
    public function run(): void
    {
        // Data diambil dari Excel. `updateOrCreate` dipakai (bukan `create`)
        // agar seeder aman dijalankan berulang: baris dicocokkan berdasarkan
        // `name` yang unik, sehingga tidak terjadi duplicate entry.
        $categories = [
            ['name' => 'Salary', 'type' => CoaCategory::TYPE_INCOME],
            ['name' => 'Other Income', 'type' => CoaCategory::TYPE_INCOME],
            ['name' => 'Family Expense', 'type' => CoaCategory::TYPE_EXPENSE],
            ['name' => 'Transport Expense', 'type' => CoaCategory::TYPE_EXPENSE],
            ['name' => 'Meal Expense', 'type' => CoaCategory::TYPE_EXPENSE],
        ];

        foreach ($categories as $category) {
            CoaCategory::updateOrCreate(
                // Kunci pencarian: nama kategori yang sudah unik di database.
                ['name' => $category['name']],
                // Nilai yang diselaraskan bila data sudah ada.
                ['type' => $category['type']],
            );
        }
    }
}
