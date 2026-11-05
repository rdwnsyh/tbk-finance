<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoaCategory extends Model
{
    // Konstanta dipakai agar nilai `type` tidak ditulis sebagai string mentah
    // di banyak tempat (validasi, seeder, service laporan). Salah ketik seperti
    // "incomes" akan langsung terlihat, bukan menjadi bug data yang diam-diam.
    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    // Kolom yang boleh diisi massal dari form. Kolom id/timestamps sengaja
    // tidak dimasukkan agar tidak bisa dimanipulasi lewat request.
    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * Satu kategori memiliki banyak COA.
     *
     * Relasi ini dipakai untuk menampilkan data COA per kategori, dan
     * `withCount('coas')` dipakai nanti untuk memblokir penghapusan kategori
     * yang masih dipakai (pesan error lebih ramah daripada error foreign key).
     */
    public function coas(): HasMany
    {
        return $this->hasMany(Coa::class);
    }
}
