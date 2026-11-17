<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerOrderRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role customer.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isCustomer();
    }

    /**
     * Aturan validasi pemesanan pelanggan.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'table_number' => ['nullable', 'string', 'max:10'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'table_number.max' => 'Nomor meja tidak boleh lebih dari 10 karakter.',
            'items.required' => 'Daftar item pesanan wajib diisi.',
            'items.array' => 'Format daftar pesanan tidak valid.',
            'items.min' => 'Pesanan harus memilih minimal 1 item menu.',
            'items.max' => 'Jumlah item pesanan tidak boleh melebihi 50 jenis menu.',
            'items.*.menu_id.required' => 'ID menu wajib diisi.',
            'items.*.menu_id.exists' => 'Menu yang dipilih tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah pesanan wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah pesanan harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah pesanan minimal 1 porsi.',
            'items.*.quantity.max' => 'Jumlah pesanan maksimal 99 porsi.',
        ];
    }
}
