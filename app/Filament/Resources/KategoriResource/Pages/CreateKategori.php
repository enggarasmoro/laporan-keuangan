<?php

namespace App\Filament\Resources\KategoriResource\Pages;

use App\Filament\Resources\KategoriResource;
use App\Http\Requests\KategoriRequest;
use App\Services\KategoriService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Kategori;

class CreateKategori extends CreateRecord
{
    protected static string $resource = KategoriResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['warna'] = $data['warna'] ?? '#6B7280';
        $data['aktif'] = $data['aktif'] ?? true;
        $data['icon']  = $data['icon'] ?? '❓';

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Kategori berhasil dibuat';
    }
    protected function handleRecordCreation(array $data): Kategori
    {
        $request = new KategoriRequest();

        $request->merge($data);
        $request->merge($data);

        $request->merge([
            'warna'      => $request->warna ?? '#6B7280',
            'aktif'      => $request->aktif ?? true,
            'icon'       => $request->icon ?? '❓'
        ]);

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

        return app(KategoriService::class)->createCategory($validated);
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
