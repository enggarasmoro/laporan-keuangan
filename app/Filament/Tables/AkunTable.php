<?php

namespace App\Filament\Tables;

use App\Models\Akun;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AkunTable
{
    public static function make(): array
    {
        return [
            ColorColumn::make('warna')
                ->label('')
                ->width(20)
                ->tooltip('Warna akun'),

            TextColumn::make('nama')
                ->label('Nama Akun')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn (Akun $record): ?string => $record->deskripsi),

            TextColumn::make('tipe')
                ->label('Tipe')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Akun::TIPE_BANK => 'info',
                    Akun::TIPE_KAS => 'success',
                    Akun::TIPE_E_WALLET => 'warning',
                    Akun::TIPE_INVESTASI => 'primary',
                    Akun::TIPE_KREDIT => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Akun::getTipeOptions()[$state] ?? $state)
                ->searchable(),

            TextColumn::make('detail_akun')
                ->label('Detail')
                ->getStateUsing(function (Akun $record): ?string {
                    if ($record->isEwallet()) {
                        return $record->nama_ewallet . ' - ' . $record->nomor_hp;
                    }

                    if ($record->isBankOrKredit()) {
                        return $record->nama_bank . ' - ' . $record->nomor_rekening;
                    }

                    return '—';
                })
                ->placeholder('—')
                ->copyable()
                ->toggleable(),

            TextColumn::make('saldo_awal')
                ->label('Saldo Awal')
                ->money('IDR')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('saldo_saat_ini')
                ->label('Saldo Saat Ini')
                ->money('IDR')
                ->sortable()
                ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                ->weight('medium'),

            IconColumn::make('aktif')
                ->label('Status')
                ->boolean()
                ->sortable()
                ->tooltip(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),

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
                ->label('Tipe Akun')
                ->options(Akun::getTipeOptions())
                ->multiple(),

            SelectFilter::make('aktif')
                ->label('Status')
                ->options([
                    1 => 'Aktif',
                    0 => 'Tidak Aktif',
                ]),

            Filter::make('has_balance')
                ->label('Memiliki Saldo')
                ->query(fn (Builder $query): Builder => $query->where('saldo_saat_ini', '>', 0))
                ->toggle(),

            Filter::make('negative_balance')
                ->label('Saldo Negatif')
                ->query(fn (Builder $query): Builder => $query->where('saldo_saat_ini', '<', 0))
                ->toggle(),

            Filter::make('bank_accounts')
                ->label('Hanya Bank/Kredit')
                ->query(fn (Builder $query): Builder =>
                    $query->whereIn('tipe', [Akun::TIPE_BANK, Akun::TIPE_KREDIT])
                )
                ->toggle(),

            Filter::make('digital_accounts')
                ->label('Hanya Digital (E-Wallet)')
                ->query(fn (Builder $query): Builder =>
                    $query->where('tipe', Akun::TIPE_E_WALLET)
                )
                ->toggle(),
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
        return 'Belum ada akun';
    }

    public static function getEmptyStateDescription(): string
    {
        return 'Mulai dengan membuat akun pertama Anda untuk melacak keuangan.';
    }

    public static function getEmptyStateIcon(): string
    {
        return 'heroicon-o-credit-card';
    }
}
