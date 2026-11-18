<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isCashier();
    }

    public function rules(): array
    {
        $isSplitRoute = $this->routeIs('payments.pay-split');

        return [
            'payer_name' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cash,qris'],
            'cash_received' => ['nullable', 'required_if:payment_method,cash', 'integer', 'min:0'],
            'items' => [$isSplitRoute ? 'required' : 'nullable', 'array', 'min:1'],
            'items.*.detail_id' => ['required', 'integer', 'min:1'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran harus cash atau qris.',
            'cash_received.required_if' => 'Nominal uang tunai diterima wajib diisi untuk pembayaran cash.',
            'cash_received.integer' => 'Nominal uang tunai harus berupa angka bulat.',
            'cash_received.min' => 'Nominal uang tunai tidak valid.',
            'items.required' => 'Minimal harus ada 1 item yang dipilih untuk split bill.',
            'items.min' => 'Minimal harus ada 1 item yang dipilih untuk split bill.',
            'items.*.detail_id.required' => 'ID detail item wajib diisi.',
            'items.*.qty.required' => 'Jumlah porsi item wajib diisi.',
            'items.*.qty.min' => 'Jumlah porsi minimal 1.',
        ];
    }
}