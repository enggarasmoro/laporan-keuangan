<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Http\Requests\AkunRequest;
use App\Services\AkunService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Akun;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class EditAkun extends EditRecord
{
    protected static string $resource = AkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Akun')
                ->modalDescription('Apakah Anda yakin ingin menghapus akun ini? Akun yang memiliki transaksi tidak dapat dihapus.')
                ->modalSubmitActionLabel('Hapus')
                ->before(function () {
                    $service = app(AkunService::class);
                    if (!$service->canBeDeleted($this->record)) {
                        throw new \Exception('Akun tidak dapat dihapus karena masih memiliki transaksi.');
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Akun berhasil diperbarui!';
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(AkunService::class);

        $request = new AkunRequest();
        $request->setRouteResolver(function () use ($record) {
            return new class($record) {
                public function __construct(private Model $record) {}
                public function parameter($key) { return $key === 'akun' ? $this->record : null; }
            };
        });

        // merge data sehingga Rule::requiredIf dan prepareForValidation-like logic melihat nilai yang benar
        $request->merge($data);

        // terapkan default seperti pada prepareForValidation (jika diperlukan)
        $request->merge([
            'saldo_awal' => $request->saldo_awal ?? 0,
            'warna'      => $request->warna ?? '#6B7280',
            'aktif'      => $request->aktif ?? true,
        ]);

        // bersihkan field sesuai tipe (sama seperti di Create)
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
            // ubah key error menjadi 'data.{field}' supaya Filament/Livewire memetakan error ke field form
            throw ValidationException::withMessages($this->formatValidationErrorsForFilament($validator->errors()->toArray()));
        }

        return $service->updateAccount($record, $validator->validated());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Bersihkan field yang tidak diperlukan berdasarkan tipe
        if ($data['tipe'] !== \App\Models\Akun::TIPE_E_WALLET) {
            $data['nama_ewallet'] = null;
            $data['nomor_hp'] = null;
        }

        if (!in_array($data['tipe'], [\App\Models\Akun::TIPE_BANK, \App\Models\Akun::TIPE_KREDIT])) {
            $data['nomor_rekening'] = null;
            $data['nama_bank'] = null;
        }

        // Set default values jika kosong
        if (is_null($data['saldo_awal'])) {
            $data['saldo_awal'] = 0;
        }

        if (is_null($data['warna'])) {
            $data['warna'] = '#6B7280';
        }

        if (is_null($data['aktif'])) {
            $data['aktif'] = true;
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
