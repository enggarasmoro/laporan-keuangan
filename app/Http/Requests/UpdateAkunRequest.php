<?php

namespace App\Http\Requests;

use App\Models\Akun;
use App\Helpers\ValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAkunRequest extends FormRequest
{
    public function authorize(): bool
    {
        $akun = $this->route('akun');

        // Pastikan user hanya bisa mengupdate akun miliknya sendiri
        return Auth::check() && $akun && $akun->user_id === Auth::id();
    }

    public function rules(): array
    {
        $akunId = $this->route('akun')?->id;

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

        // Set default values jika field kosong
        if (is_null($this->saldo_awal)) {
            $this->merge(['saldo_awal' => 0]);
        }

        if (is_null($this->warna)) {
            $this->merge(['warna' => '#6B7280']);
        }

        if (is_null($this->aktif)) {
            $this->merge(['aktif' => true]);
        }
    }
}
