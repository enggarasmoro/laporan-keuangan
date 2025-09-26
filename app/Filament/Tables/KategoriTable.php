<?php

namespace App\Filament\Tables;

use App\Models\Kategori;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KategoriTable
{
    public static function make(): array
    {
        return [
            ColorColumn::make('warna')
                ->label('')
                ->width(20)
                ->tooltip('Warna kategori'),

            TextColumn::make('icon')
                ->label('Icon')
                ->default('—')
                ->alignCenter()
                ->size('lg')
                ->tooltip('Icon kategori'),

            TextColumn::make('nama')
                ->label('Nama Kategori')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn (Kategori $record): ?string => $record->deskripsi),

            TextColumn::make('tipe')
                ->label('Tipe')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Kategori::TIPE_PEMASUKAN => 'success',
                    Kategori::TIPE_PENGELUARAN => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Kategori::getTipeOptions()[$state] ?? $state)
                ->searchable(),

            TextColumn::make('deskripsi')
                ->label('Deskripsi')
                ->limit(50)
                ->placeholder('—')
                ->tooltip(fn (Kategori $record): ?string => $record->deskripsi)
                ->toggleable(),

            IconColumn::make('aktif')
                ->label('Status')
                ->boolean()
                ->sortable()
                ->tooltip(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),

            // TODO: Uncomment when Transaksi model is created
            // TextColumn::make('transaksis_count')
            //     ->label('Transaksi')
            //     ->counts('transaksis')
            //     ->sortable()
            //     ->badge()
            //     ->color('gray'),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->dateTime('d M Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('tipe')
                ->label('Tipe Kategori')
                ->options(Kategori::getTipeOptions())
                ->multiple(),

            SelectFilter::make('aktif')
                ->label('Status')
                ->options([
                    1 => 'Aktif',
                    0 => 'Tidak Aktif',
                ]),

            Filter::make('pemasukan_only')
                ->label('Hanya Pemasukan')
                ->query(fn (Builder $query): Builder =>
                    $query->where('tipe', Kategori::TIPE_PEMASUKAN)
                )
                ->toggle(),

            Filter::make('pengeluaran_only')
                ->label('Hanya Pengeluaran')
                ->query(fn (Builder $query): Builder =>
                    $query->where('tipe', Kategori::TIPE_PENGELUARAN)
                )
                ->toggle(),

            Filter::make('has_icon')
                ->label('Memiliki Icon')
                ->query(fn (Builder $query): Builder => $query->whereNotNull('icon'))
                ->toggle(),

            // TODO: Uncomment when Transaksi model is created
            // Filter::make('used_categories')
            //     ->label('Kategori Terpakai')
            //     ->query(fn (Builder $query): Builder => $query->has('transaksis'))
            //     ->toggle(),
        ];
    }

    public static function getDefaultSort(): string
    {
        return 'nama';
    }

    public static function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    public static function getRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    public static function shouldPaginate(): bool
    {
        return true;
    }

    public static function getEmptyStateHeading(): string
    {
        return 'Belum ada kategori';
    }

    public static function getEmptyStateDescription(): string
    {
        return 'Mulai dengan membuat kategori pertama Anda untuk mengorganisir transaksi.';
    }

    public static function getEmptyStateIcon(): string
    {
        return 'heroicon-o-tag';
    }
}
