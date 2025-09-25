<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Http\Requests\AkunRequest;
use App\Models\Akun;
use App\Services\AkunService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
        $request = new AkunRequest();

        $request->merge($data);

        $request->merge([
            'saldo_awal' => $request->saldo_awal ?? 0,
            'warna'      => $request->warna ?? '#6B7280',
            'aktif'      => $request->aktif ?? true,
        ]);

        if (($request->tipe ?? null) !== Akun::TIPE_E_WALLET) {
            $request->merge([
                'nama_ewallet' => null,
                'nomor_hp'     => null,
            ]);
        }

        if (!in_array($request->tipe ?? null, [Akun::TIPE_BANK, Akun::TIPE_KREDIT])) {
            $request->merge([
                'nomor_rekening' => null,
                'nama_bank'      => null,
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages(),
            method_exists($request, 'attributes') ? $request->attributes() : []
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages(
                $this->formatValidationErrorsForFilament($validator->errors()->toArray())
            );
        }

        $validated = $validator->validated();

        return app(AkunService::class)->createAccount($validated);
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

    private function formatValidationErrorsForFilament(array $errors): array
    {
        $formatted = [];

        foreach ($errors as $key => $messages) {
            $prefixedKey = 'data.' . $key;
            $formatted[$prefixedKey] = $messages;
        }

        return $formatted;
    }
}
