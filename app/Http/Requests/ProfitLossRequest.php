<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfitLossRequest extends FormRequest
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
        // Nullable karena parameter boleh kosong; controller akan memakai
        // periode default dari data transaksi.
        return [
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Validasi lintas field dijalankan setelah aturan format lolos.
     * Laravel memanggil method `after()` ini secara otomatis.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // Jangan menambah error jika formatnya sudah salah; pesan
                // format lebih dulu ditampilkan.
                if ($validator->errors()->count() > 0) {
                    return;
                }

                $start = $this->input('start_month');
                $end = $this->input('end_month');

                // Format Y-m bisa dibandingkan sebagai string karena urutannya
                // sudah mengikuti urutan waktu (tahun lalu bulan).
                if ($start && $end && $end < $start) {
                    $validator->errors()->add(
                        'end_month',
                        'Bulan akhir tidak boleh lebih awal dari bulan mulai.',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'start_month.date_format' => 'Format bulan mulai harus YYYY-MM.',
            'end_month.date_format' => 'Format bulan akhir harus YYYY-MM.',
        ];
    }

    public function attributes(): array
    {
        return [
            'start_month' => 'bulan mulai',
            'end_month' => 'bulan akhir',
            'search' => 'pencarian bulan',
        ];
    }
}
