<?php

namespace App\Http\Requests;

use App\Models\Akun;
use App\Helpers\ValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateAkunRequest extends FormRequest
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
                    Akun::TIPE_BANK,
                    Akun::TIPE_KAS,
                    Akun::TIPE_E_WALLET,
                    Akun::TIPE_INVESTASI,
                    Akun::TIPE_KREDIT,
                ])
            ],
            'saldo_awal' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'nomor_rekening' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(function () {
                    return in_array($this->tipe, [Akun::TIPE_BANK, Akun::TIPE_KREDIT]);
                })
            ],
            'nama_bank' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(function () {
                    return in_array($this->tipe, [Akun::TIPE_BANK, Akun::TIPE_KREDIT]);
                })
            ],
            'nama_ewallet' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(function () {
                    return $this->tipe === Akun::TIPE_E_WALLET;
                })
            ],
            'nomor_hp' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+62|0)[0-9]{8,13}$/',
                Rule::requiredIf(function () {
                    return $this->tipe === Akun::TIPE_E_WALLET;
                })
            ],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'warna' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ValidationMessages::akun();
    }

    protected function prepareForValidation(): void
    {
        // Set default values untuk field nullable
        $this->merge([
            'saldo_awal' => $this->saldo_awal ?? 0,
            'warna' => $this->warna ?? '#6B7280',
            'aktif' => $this->aktif ?? true,
        ]);

        // Bersihkan field yang tidak diperlukan berdasarkan tipe
        if ($this->tipe !== Akun::TIPE_E_WALLET) {
            $this->merge([
                'nama_ewallet' => null,
                'nomor_hp' => null,
            ]);
        }

        if (!in_array($this->tipe, [Akun::TIPE_BANK, Akun::TIPE_KREDIT])) {
            $this->merge([
                'nomor_rekening' => null,
                'nama_bank' => null,
            ]);
        }
    }
}
