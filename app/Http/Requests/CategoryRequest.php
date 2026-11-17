<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Pembatasan role sudah ditangani middleware route
    }

    /**
     * Aturan validasi yang diterapkan pada request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia untuk kolom name.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
        ];
    }
}
