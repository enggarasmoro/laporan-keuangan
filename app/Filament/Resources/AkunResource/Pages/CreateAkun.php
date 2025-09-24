<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Http\Requests\CreateAkunRequest;
use App\Services\AkunService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAkun extends CreateRecord
{
    protected static string $resource = AkunResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Akun berhasil dibuat!';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(AkunService::class);

        // Validasi data menggunakan CreateAkunRequest rules
        $request = new CreateAkunRequest();
        $validator = validator($data, $request->rules(), $request->messages());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $service->createAccount($validator->validated());
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values untuk field nullable
        $data['saldo_awal'] = $data['saldo_awal'] ?? 0;
        $data['warna'] = $data['warna'] ?? '#6B7280';
        $data['aktif'] = $data['aktif'] ?? true;

        // Bersihkan field yang tidak diperlukan berdasarkan tipe
        if ($data['tipe'] !== \App\Models\Akun::TIPE_E_WALLET) {
            $data['nama_ewallet'] = null;
            $data['nomor_hp'] = null;
        }

        if (!in_array($data['tipe'], [\App\Models\Akun::TIPE_BANK, \App\Models\Akun::TIPE_KREDIT])) {
            $data['nomor_rekening'] = null;
            $data['nama_bank'] = null;
        }

        return $data;
    }
}
