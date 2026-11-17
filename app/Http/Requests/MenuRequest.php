<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Pembatasan role sudah ditangani oleh middleware route
    }

    /**
     * Aturan validasi yang diterapkan pada request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_available' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia untuk setiap field.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'name.required' => 'Nama menu wajib diisi.',
            'name.string' => 'Nama menu harus berupa teks.',
            'name.max' => 'Nama menu maksimal 150 karakter.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.integer' => 'Harga menu harus berupa angka bulat.',
            'price.min' => 'Harga menu tidak boleh kurang dari 0.',
            'stock.required' => 'Stok menu wajib diisi.',
            'stock.integer' => 'Stok menu harus berupa angka bulat.',
            'stock.min' => 'Stok menu tidak boleh kurang dari 0.',
            'min_stock.required' => 'Batas minimum stok wajib diisi.',
            'min_stock.integer' => 'Batas minimum stok harus berupa angka bulat.',
            'min_stock.min' => 'Batas minimum stok tidak boleh kurang dari 0.',
            'is_available.boolean' => 'Status ketersediaan menu harus bernilai benar atau salah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran file gambar tidak boleh melebihi 2MB.',
        ];
    }
}
