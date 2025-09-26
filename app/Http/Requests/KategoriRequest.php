<?php

namespace App\Http\Requests;

use App\Models\Kategori;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'min:2', 'max:255'],
            'tipe' => [
                'required',
                Rule::in([
                    Kategori::TIPE_PEMASUKAN,
                    Kategori::TIPE_PENGELUARAN,
                ])
            ],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
            'warna' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori harus diisi.',
            'nama.min' => 'Nama kategori minimal 2 karakter.',
            'nama.max' => 'Nama kategori maksimal 255 karakter.',
            'tipe.required' => 'Tipe kategori harus dipilih.',
            'tipe.in' => 'Tipe kategori tidak valid.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            'icon.max' => 'Icon maksimal 10 karakter.',
            'warna.regex' => 'Format warna tidak valid. Gunakan format hex (#RRGGBB).',
            'aktif.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama Kategori',
            'tipe' => 'Tipe Kategori',
            'deskripsi' => 'Deskripsi',
            'icon' => 'Icon',
            'warna' => 'Warna',
            'aktif' => 'Status Aktif',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default values untuk field nullable
        $this->merge([
            'warna' => $this->warna ?? '#6B7280',
            'aktif' => $this->aktif ?? true,
        ]);

        // Set default icon based on type if not provided
        if (empty($this->icon)) {
            $defaultIcon = $this->tipe === Kategori::TIPE_PEMASUKAN ? '💰' : '💸';
            $this->merge(['icon' => $defaultIcon]);
        }
    }
}
