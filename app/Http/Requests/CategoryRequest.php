<?php

namespace App\Http\Requests;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    // role sudah dibatasi middleware route
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
                // cegah slug kembar, misal "Nasi Spesial" dan "Nasi-Spesial"
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value)) {
                        return;
                    }

                    $current = $this->route('category');
                    $currentId = $current instanceof Category ? $current->id : $current;

                    $taken = Category::where('slug', Str::slug($value))
                        ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
                        ->exists();

                    if ($taken) {
                        $fail('Nama kategori terlalu mirip dengan kategori lain.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}