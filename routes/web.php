<?php

use App\Http\Controllers\CoaCategoryController;
use Illuminate\Support\Facades\Route;

// Halaman awal langsung diarahkan ke daftar kategori agar aplikasi bisa
// dipakai tanpa halaman kosong. Redirect dipakai (bukan view) supaya URL
// tetap konsisten dan navigasi "Kembali" tidak membuka halaman perantara.
Route::redirect('/', '/coa-categories');

// Resource route standar CRUD. `show` dikecualikan karena daftar sudah memuat
// semua informasi yang dibutuhkan, jadi halaman detail terpisah tidak perlu.
//
// `parameters()` memaksa nama parameter menjadi `coaCategory` (camelCase).
// Tanpa ini Laravel memakai `coa_category`, yang akan menyulitkan route model
// binding karena type-hint controller memakai variabel `$coaCategory`.
Route::resource('coa-categories', CoaCategoryController::class)
    ->except(['show'])
    ->parameters(['coa-categories' => 'coaCategory']);
