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
        // Tabel transaksi (jurnal sederhana: satu baris hanya debit atau kredit).
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Kolom tanggal diberi index karena laporan selalu memfilter
            // rentang bulan (WHERE date BETWEEN ...). Tanpa index, MySQL akan
            // memindai seluruh tabel setiap kali laporan dibuka.
            $table->date('date')->index();

            // restrictOnDelete mencegah COA dihapus selama masih dipakai transaksi.
            // Mengapa: transaksi adalah data historis; menghapus COA akan membuat
            // laporan lama kehilangan nama/kategori akun.
            $table->foreignId('coa_id')
                ->constrained('coas')
                ->restrictOnDelete();

            $table->string('description')->nullable();

            // DECIMAL(15,2) dipakai untuk nominal uang, bukan FLOAT, agar tidak ada
            // galat pembulatan (mis. 0.1 + 0.2). Default 0 memudahkan aturan
            // "hanya satu sisi yang terisi", karena kolom lain otomatis 0.
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
