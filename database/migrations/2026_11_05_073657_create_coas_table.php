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
        // Tabel Chart of Account (daftar akun).
        Schema::create('coas', function (Blueprint $table) {
            $table->id();

            // Kode akun unik (contoh: 401). unsignedInteger dipakai karena kode COA
            // selalu bilangan bulat positif dan tidak membutuhkan nilai negatif.
            $table->unsignedInteger('code')->unique();

            $table->string('name');

            // restrictOnDelete mencegah kategori dihapus selama masih punya COA.
            // Mengapa: kalau kategori hilang, tipe income/expense COA ikut hilang
            // dan laporan Profit/Loss menjadi tidak bisa dihitung.
            $table->foreignId('coa_category_id')
                ->constrained('coa_categories')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};
