<?php

use App\Http\Controllers\CoaCategoryController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Halaman awal langsung diarahkan ke daftar kategori agar aplikasi bisa
// dipakai tanpa halaman kosong. Redirect dipakai (bukan view) supaya URL
// tetap konsisten dan navigasi "Kembali" tidak membuka halaman perantara.
Route::redirect('/', '/coa-categories');

// Resource route standar CRUD. `show` dikecualikan karena daftar sudah memuat
// semua informasi yang dibutuhkan, jadi halaman detail terpisah tidak perlu.
//
// `parameters()` memaksa nama parameter route menjadi camelCase agar cocok
// dengan type-hint controller dan route model binding.
Route::resource('coa-categories', CoaCategoryController::class)
    ->except(['show'])
    ->parameters(['coa-categories' => 'coaCategory']);

Route::resource('coas', CoaController::class)
    ->except(['show'])
    ->parameters(['coas' => 'coa']);

Route::resource('transactions', TransactionController::class)
    ->except(['show'])
    ->parameters(['transactions' => 'transaction']);
