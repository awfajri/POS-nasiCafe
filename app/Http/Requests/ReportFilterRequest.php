<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    /**
     * Pembatasan akses sudah ditangani middleware route (auth + role:owner).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Tanggal awal wajib diisi.',
            'start_date.date' => 'Tanggal awal harus berupa tanggal yang valid.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ];
    }

    /**
     * Isi default: awal bulan ini s.d. hari ini (jika query kosong).
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('start_date') && ! $this->filled('end_date')) {
            $this->merge([
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]);
        }
    }
}
