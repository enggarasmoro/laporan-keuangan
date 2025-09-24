<?php

namespace App\Filament\Resources\AkunResource\Pages;

use App\Filament\Resources\AkunResource;
use App\Services\AkunService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\Akun;
use Illuminate\Database\Eloquent\Builder;

class ListAkuns extends ListRecords
{
    protected static string $resource = AkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Akun')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::forUser()->count()),

            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('aktif', true))
                ->badge(fn () => $this->getModel()::forUser()->where('aktif', true)->count()),

            'bank' => Tab::make('Bank')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe', Akun::TIPE_BANK))
                ->badge(fn () => $this->getModel()::forUser()->where('tipe', Akun::TIPE_BANK)->count()),

            'kas' => Tab::make('Kas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe', Akun::TIPE_KAS))
                ->badge(fn () => $this->getModel()::forUser()->where('tipe', Akun::TIPE_KAS)->count()),

            'ewallet' => Tab::make('E-Wallet')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe', Akun::TIPE_E_WALLET))
                ->badge(fn () => $this->getModel()::forUser()->where('tipe', Akun::TIPE_E_WALLET)->count()),

            'investasi' => Tab::make('Investasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe', Akun::TIPE_INVESTASI))
                ->badge(fn () => $this->getModel()::forUser()->where('tipe', Akun::TIPE_INVESTASI)->count()),

            'kredit' => Tab::make('Kredit')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe', Akun::TIPE_KREDIT))
                ->badge(fn () => $this->getModel()::forUser()->where('tipe', Akun::TIPE_KREDIT)->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa ditambahkan widget statistik di sini
        ];
    }
}
