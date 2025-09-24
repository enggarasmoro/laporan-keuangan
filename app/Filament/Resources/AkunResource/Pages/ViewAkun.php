<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Services\AkunService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;

class ViewAkun extends ViewRecord
{
    protected static string $resource = AkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Akun')
                    ->schema([
                        TextEntry::make('nama')
                            ->label('Nama Akun')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('tipe')
                            ->label('Tipe Akun')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                \App\Models\Akun::TIPE_BANK => 'info',
                                \App\Models\Akun::TIPE_KAS => 'success',
                                \App\Models\Akun::TIPE_E_WALLET => 'warning',
                                \App\Models\Akun::TIPE_INVESTASI => 'primary',
                                \App\Models\Akun::TIPE_KREDIT => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => \App\Models\Akun::getTipeOptions()[$state] ?? $state),

                        TextEntry::make('saldo_awal')
                            ->label('Saldo Awal')
                            ->money('IDR'),

                        TextEntry::make('saldo_saat_ini')
                            ->label('Saldo Saat Ini')
                            ->money('IDR')
                            ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                            ->size('lg')
                            ->weight('bold'),

                        IconEntry::make('aktif')
                            ->label('Status')
                            ->boolean(),

                        ColorEntry::make('warna')
                            ->label('Warna'),
                    ])
                    ->columns(3),

                Section::make('Detail Bank/Kredit')
                    ->schema([
                        TextEntry::make('nama_bank')
                            ->label('Nama Bank')
                            ->placeholder('—'),

                        TextEntry::make('nomor_rekening')
                            ->label('Nomor Rekening')
                            ->placeholder('—')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->visible(fn () => in_array($this->record->tipe, [\App\Models\Akun::TIPE_BANK, \App\Models\Akun::TIPE_KREDIT])),

                Section::make('Detail E-Wallet')
                    ->schema([
                        TextEntry::make('nama_ewallet')
                            ->label('Nama E-Wallet')
                            ->placeholder('—'),

                        TextEntry::make('nomor_hp')
                            ->label('Nomor HP')
                            ->placeholder('—')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->visible(fn () => $this->record->tipe === \App\Models\Akun::TIPE_E_WALLET),

                Section::make('Informasi Tambahan')
                    ->schema([
                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada deskripsi')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
