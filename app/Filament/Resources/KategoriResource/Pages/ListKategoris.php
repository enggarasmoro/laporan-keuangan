<?php

namespace App\Filament\Resources\KategoriResource\Pages;

use App\Filament\Resources\KategoriResource;
use App\Models\Kategori;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListKategoris extends ListRecords
{
    protected static string $resource = KategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Kategori')
                ->badge($this->getModel()::forUser()->count()),

            'pemasukan' => Tab::make('Pemasukan')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType(Kategori::TIPE_PEMASUKAN))
                ->badge($this->getModel()::forUser()->byType(Kategori::TIPE_PEMASUKAN)->count()),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType(Kategori::TIPE_PENGELUARAN))
                ->badge($this->getModel()::forUser()->byType(Kategori::TIPE_PENGELUARAN)->count()),

            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->badge($this->getModel()::forUser()->active()->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa ditambahkan widget statistik di sini
        ];
    }
}
