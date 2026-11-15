<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payer_name' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cash,qris'],
            'amount_paid' => ['required', 'integer', 'min:1'],
            'cash_received' => ['nullable', 'required_if:payment_method,cash', 'integer', 'gte:amount_paid'],
            'paid_items' => ['required', 'array', 'min:1'],
            'paid_items.*.transaction_item_id' => ['required', 'integer', 'min:1'],
            'paid_items.*.qty' => ['required', 'integer', 'min:1'],
            'paid_items.*.subtotal' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran harus cash atau qris.',
            'amount_paid.required' => 'Nominal pembayaran wajib diisi.',
            'amount_paid.min' => 'Nominal pembayaran minimal Rp 1.',
            'cash_received.required_if' => 'Nominal uang tunai diterima wajib diisi untuk pembayaran cash.',
            'cash_received.gte' => 'Uang tunai yang diterima tidak boleh kurang dari nominal tagihan.',
            'paid_items.required' => 'Minimal harus ada 1 item yang dibayar.',
            'paid_items.min' => 'Minimal harus ada 1 item yang dibayar.',
        ];
    }
}