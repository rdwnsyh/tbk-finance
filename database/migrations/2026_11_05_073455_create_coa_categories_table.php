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
        // Tabel master kategori COA.
        Schema::create('coa_categories', function (Blueprint $table) {
            $table->id();

            // Nama kategori unik agar tidak ada dua kategori dengan makna sama,
            // sehingga laporan tidak terpecah ke dua baris yang sebenarnya identik.
            $table->string('name')->unique();

            // `type` disimpan di level database sebagai enum, bukan hanya divalidasi
            // di aplikasi. Alasannya, laporan Profit/Loss menentukan sisi income/expense
            // dari kolom ini, jadi nilainya harus tetap valid walau data diubah
            // lewat proses lain (mis. seeder/import).
            $table->enum('type', ['income', 'expense']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tabel COA yang mereferensikan kategori dihapus lebih dulu oleh migrasinya
        // sendiri karena foreign key memakai restrictOnDelete.
        Schema::dropIfExists('coa_categories');
    }
};
