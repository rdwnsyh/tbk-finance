<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    // `coa_id` wajib diisi dari form; debit/credit juga diisi dari form.
    // Kolom yang tidak ada di sini (id/timestamps) tidak bisa diisi massal.
    protected $fillable = [
        'date',
        'coa_id',
        'description',
        'debit',
        'credit',
    ];

    /**
     * Cast untuk tipe data kolom.
     */
    protected function casts(): array
    {
        return [
            // Kolom DATE dikembalikan sebagai objek Carbon, sehingga laporan bisa
            // memakai ->format('Y-m') dan filter rentang tanggal tanpa parsing manual.
            'date' => 'date',
            // decimal:2 menjaga nominal uang tetap 2 angka di belakang koma
            // sesuai definisi DECIMAL(15,2) di database.
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    /**
     * Setiap transaksi terkait dengan satu COA.
     *
     * Nama/kode COA TIDAK disimpan di tabel transactions (menghindari duplikasi
     * data). Saat menampilkan daftar transaksi kita pakai eager loading
     * `with('coa.coaCategory')` agar tetap efisien.
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }
}
