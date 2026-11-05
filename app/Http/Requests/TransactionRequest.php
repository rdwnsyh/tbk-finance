<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransactionRequest extends FormRequest
{
    /**
     * Tanpa autentikasi, semua request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'coa_id' => [
                'required',
                'integer',
                // Memastikan COA yang dikirim benar-benar ada. Ini penting
                // karena request bisa dibuat di luar form Vue.
                Rule::exists('coas', 'id'),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            // min:0 mencegah nominal negatif. Kolom ini nullable karena
            // UI hanya mengirim nilai pada sisi yang dipilih (sisi lain 0).
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Validasi lintas kolom dijalankan setelah aturan utama lolos.
     * Laravel memanggil method `after()` ini secara otomatis.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // Cast ke float supaya perbandingan konsisten meski input
                // dikirim sebagai string ("5000000.00") oleh form HTML.
                $debit = (float) ($this->input('debit') ?? 0);
                $credit = (float) ($this->input('credit') ?? 0);

                $hasDebit = $debit > 0;
                $hasCredit = $credit > 0;

                // Aturan bisnis: tepat satu sisi harus lebih dari 0.
                if ($hasDebit && $hasCredit) {
                    $validator->errors()->add(
                        'amount',
                        'Isi salah satu: debit atau credit, tidak keduanya.',
                    );
                } elseif (! $hasDebit && ! $hasCredit) {
                    $validator->errors()->add(
                        'amount',
                        'Nominal debit atau credit harus lebih dari 0.',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal transaksi wajib diisi.',
            'date.date' => 'Tanggal transaksi tidak valid.',
            'coa_id.required' => 'COA wajib dipilih.',
            'coa_id.integer' => 'COA yang dipilih tidak valid.',
            'coa_id.exists' => 'COA yang dipilih tidak valid.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'debit.numeric' => 'Nilai debit harus berupa angka.',
            'debit.min' => 'Nilai debit tidak boleh negatif.',
            'credit.numeric' => 'Nilai credit harus berupa angka.',
            'credit.min' => 'Nilai credit tidak boleh negatif.',
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'tanggal transaksi',
            'coa_id' => 'COA',
            'description' => 'deskripsi',
            'debit' => 'debit',
            'credit' => 'credit',
        ];
    }
}
