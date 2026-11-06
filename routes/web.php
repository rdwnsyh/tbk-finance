<?php

use App\Http\Controllers\CoaCategoryController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/dashboard', DashboardController::class);

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

// Laporan laba/rugi memakai route GET dengan filter rentang bulan.
// Parameter dikirim lewat query string (bukan segment) agar rentang bisa
// di-bookmark dan halaman bisa di-refresh tanpa mengubah state.
Route::get('profit-loss/export', [ProfitLossController::class, 'export'])
    ->name('profit-loss.export');

Route::get('profit-loss/export/csv', [ProfitLossController::class, 'exportCsv'])
    ->name('profit-loss.export.csv');

Route::get('profit-loss/export/pdf', [ProfitLossController::class, 'exportPdf'])
    ->name('profit-loss.export.pdf');

Route::get('profit-loss', [ProfitLossController::class, 'index'])
    ->name('profit-loss.index');
