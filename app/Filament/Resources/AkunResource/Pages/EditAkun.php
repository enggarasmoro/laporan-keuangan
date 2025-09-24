<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Http\Requests\UpdateAkunRequest;
use App\Services\AkunService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

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

        // Validasi data menggunakan UpdateAkunRequest rules
        $request = new UpdateAkunRequest();
        $request->setRouteResolver(function () use ($record) {
            return new class($record) {
                public function __construct(private Model $record) {}
                public function parameter($key) { return $key === 'akun' ? $this->record : null; }
            };
        });

        $validator = validator($data, $request->rules(), $request->messages());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
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
}
