<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoaRequest extends FormRequest
{
    /**
     * Tidak ada autentikasi pada aplikasi ini, jadi semua request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Saat update, parameter route `coa` sudah berisi model hasil binding.
        // Saat store nilainya null; `ignore(null)` berarti tanpa pengecualian.
        $coa = $this->route('coa');

        return [
            'code' => [
                'required',
                // Kode COA harus bilangan bulat karena kolomnya unsignedInteger.
                'integer',
                'min:1',
                // Batas maksimum INT UNSIGNED di MySQL (2^32 - 1).
                'max:4294967295',
                // Kode unik, tetapi saat edit abaikan baris itu sendiri.
                Rule::unique('coas', 'code')->ignore($coa?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'coa_category_id' => [
                'required',
                'integer',
                // Memastikan kategori benar-benar ada, sehingga tidak mungkin
                // menyimpan COA dengan foreign key yang menggantung.
                Rule::exists('coa_categories', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode COA wajib diisi.',
            'code.integer' => 'Kode COA harus berupa angka bulat.',
            'code.min' => 'Kode COA minimal 1.',
            'code.max' => 'Kode COA terlalu besar.',
            'code.unique' => 'Kode COA sudah digunakan.',
            'name.required' => 'Nama COA wajib diisi.',
            'name.string' => 'Nama COA harus berupa teks.',
            'name.max' => 'Nama COA maksimal 255 karakter.',
            'coa_category_id.required' => 'Kategori wajib dipilih.',
            'coa_category_id.integer' => 'Kategori yang dipilih tidak valid.',
            'coa_category_id.exists' => 'Kategori yang dipilih tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'kode COA',
            'name' => 'nama COA',
            'coa_category_id' => 'kategori',
        ];
    }
}
