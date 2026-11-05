<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coa extends Model
{
    // Hanya kolom yang diisi dari form yang boleh diisi massal.
    // `code` boleh diisi karena user memang memasukkan kode akun sendiri.
    protected $fillable = [
        'code',
        'name',
        'coa_category_id',
    ];

    /**
     * Cast yang membuat data dari database lebih aman dipakai di PHP.
     */
    protected function casts(): array
    {
        return [
            // Kode akun dikembalikan sebagai integer supaya konsisten saat
            // diurutkan/dibandingkan (MySQL bisa mengembalikan string).
            'code' => 'integer',
        ];
    }

    /**
     * COA dimiliki oleh satu kategori (income/expense).
     *
     * Relasi ini yang dipakai laporan untuk mengambil `type` kategori
     * dalam satu query (eager loading), sehingga tidak terjadi N+1.
     */
    public function coaCategory(): BelongsTo
    {
        return $this->belongsTo(CoaCategory::class);
    }

    /**
     * Satu COA bisa dipakai oleh banyak transaksi.
     *
     * `withCount('transactions')` dipakai untuk mencegah COA dihapus
     * saat masih memiliki transaksi.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
