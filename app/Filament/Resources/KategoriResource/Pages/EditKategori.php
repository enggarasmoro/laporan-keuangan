<?php

namespace App\Filament\Resources\KategoriResource\Pages;

use App\Filament\Resources\KategoriResource;
use App\Http\Requests\KategoriRequest;
use App\Services\KategoriService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

class EditKategori extends EditRecord
{
    protected static string $resource = KategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Kategori')
                ->modalDescription('Apakah Anda yakin ingin menghapus kategori ini?')
                ->modalSubmitActionLabel('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Kategori berhasil diperbarui';
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(KategoriService::class);

        $request = new KategoriRequest();
        $request->setRouteResolver(function () use ($record) {
            return new class($record) {
                public function __construct(private Model $record) {}
                public function parameter($key) { return $key === 'kategori' ? $this->record : null; }
            };
        });

        // merge data sehingga Rule::requiredIf dan prepareForValidation-like logic melihat nilai yang benar
        $request->merge($data);

        // terapkan default seperti pada prepareForValidation (jika diperlukan)
        $request->merge([
            'icon'  => $request->icon ?? '❓',
            'warna' => $request->warna ?? '#6B7280',
            'aktif' => $request->aktif ?? true,
        ]);

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

        return $service->updateCategory($record, $validator->validated());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['user_id']);

        // Set default values jika kosong
        if (is_null($data['warna'])) {
            $data['warna'] = '#6B7280';
        }

        if (is_null($data['aktif'])) {
            $data['aktif'] = true;
        }

        if (!array_key_exists('icon', $data) || is_null($data['icon'])) {
            $data['icon'] = '❓';
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
