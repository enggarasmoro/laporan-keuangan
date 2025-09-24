<?php

namespace App\Helpers;

class ValidationMessages
{
    /**
     * Get all Akun validation messages
     */
    public static function akun(): array
    {
        return [
            'nama.required' => 'Nama akun harus diisi.',
            'nama.min' => 'Nama akun minimal 2 karakter.',
            'nama.max' => 'Nama akun maksimal 255 karakter.',
            'tipe.required' => 'Tipe akun harus dipilih.',
            'tipe.in' => 'Tipe akun tidak valid.',
            'saldo_awal.numeric' => 'Saldo awal harus berupa angka.',
            'saldo_awal.min' => 'Saldo awal tidak boleh negatif.',
            'saldo_awal.max' => 'Saldo awal terlalu besar.',
            'nomor_rekening.required' => 'Nomor rekening harus diisi untuk akun bank/kredit.',
            'nomor_rekening.max' => 'Nomor rekening maksimal 50 karakter.',
            'nama_bank.required' => 'Nama bank harus diisi untuk akun bank/kredit.',
            'nama_bank.max' => 'Nama bank maksimal 100 karakter.',
            'nama_ewallet.required' => 'Nama e-wallet harus diisi untuk akun e-wallet.',
            'nama_ewallet.max' => 'Nama e-wallet maksimal 50 karakter.',
            'nomor_hp.required' => 'Nomor HP harus diisi untuk akun e-wallet.',
            'nomor_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'nomor_hp.regex' => 'Format nomor HP tidak valid. Gunakan format: 08xxx atau +62xxx.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            'warna.regex' => 'Format warna tidak valid. Gunakan format hex (#RRGGBB).',
            'aktif.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }

    /**
     * Get specific field validation messages for Akun
     */
    public static function akunField(string $field): array
    {
        $messages = self::akun();
        $fieldMessages = [];

        foreach ($messages as $key => $message) {
            if (str_starts_with($key, $field . '.')) {
                $rule = substr($key, strlen($field) + 1);
                $fieldMessages[$rule] = $message;
            }
        }

        return $fieldMessages;
    }
}
