<?php

namespace Database\Seeders;

use App\Models\Coa;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Mengisi 3 transaksi contoh sesuai sheet "Contoh Master dan Transaksi".
     *
     * Harus dijalankan SETELAH CoaSeeder karena `coa_id` membutuhkan COA
     * yang sudah ada.
     */
    public function run(): void
    {
        // Array [kode COA => id] agar tidak query COA di dalam loop.
        $coaIds = Coa::pluck('id', 'code');

        $transactions = [
            [
                'date' => '2022-01-01',
                'coa_code' => 401,
                // Ejaan "Persuhaan" dipertahankan persis seperti di Excel.
                'description' => 'Gaji Di Persuhaan A',
                'debit' => 0,
                'credit' => 5000000,
            ],
            [
                'date' => '2022-01-02',
                'coa_code' => 402,
                'description' => 'Gaji Ketum',
                'debit' => 0,
                'credit' => 7000000,
            ],
            [
                'date' => '2022-01-10',
                'coa_code' => 602,
                'description' => 'Bensin Anak',
                'debit' => 25000,
                'credit' => 0,
            ],
        ];

        foreach ($transactions as $transaction) {
            // Transaksi tidak punya kolom unik. Kombinasi tanggal + COA + deskripsi
            // dipakai sebagai "kunci alami" agar seeder idempotent: dijalankan dua
            // kali pun tidak menggandakan baris yang sama.
            Transaction::updateOrCreate(
                [
                    'date' => $transaction['date'],
                    'coa_id' => $coaIds[$transaction['coa_code']],
                    'description' => $transaction['description'],
                ],
                [
                    'debit' => $transaction['debit'],
                    'credit' => $transaction['credit'],
                ],
            );
        }
    }
}
