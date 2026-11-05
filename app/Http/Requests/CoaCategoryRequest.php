<?php

namespace App\Http\Requests;

use App\Models\CoaCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoaCategoryRequest extends FormRequest
{
    /**
     * Satu FormRequest dipakai untuk store dan update.
     *
     * Aplikasi ini tidak memakai autentikasi, sehingga semua request boleh
     * diproses. Kalau nanti ada login, method ini tinggal diubah menjadi
     * pengecekan hak akses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang sama untuk create dan update.
     */
    public function rules(): array
    {
        // Saat update, route model binding sudah mengisi parameter `coaCategory`.
        // Saat store nilainya null; `ignore(null)` berarti tidak ada baris yang
        // dikecualikan dari aturan unik.
        $coaCategory = $this->route('coaCategory');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Nama harus unik, TETAPI saat edit abaikan baris ini sendiri.
                // Tanpa `ignore`, menyimpan tanpa mengubah nama akan dianggap
                // duplikat dengan dirinya sendiri.
                Rule::unique('coa_categories', 'name')->ignore($coaCategory?->id),
            ],
            'type' => [
                'required',
                // Konstanta model dipakai agar tidak ada salah ketik string
                // yang bisa lolos validasi (mis. "incomes").
                Rule::in([CoaCategory::TYPE_INCOME, CoaCategory::TYPE_EXPENSE]),
            ],
        ];
    }

    /**
     * Pesan error berbahasa Indonesia agar mudah dipahami pengguna.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'type.required' => 'Tipe kategori wajib dipilih.',
            'type.in' => 'Tipe kategori harus income atau expense.',
        ];
    }

    /**
     * Nama atribut yang dipakai di pesan error (membuat pesan lebih natural).
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kategori',
            'type' => 'tipe kategori',
        ];
    }
}
