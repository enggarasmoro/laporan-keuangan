<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunResource\Pages;
use App\Models\Akun;
use App\Services\AkunService;
use App\Helpers\ValidationMessages;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AkunResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Akun';

    protected static ?string $modelLabel = 'Akun';

    protected static ?string $pluralModelLabel = 'Akun';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Akun')
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->placeholder('Masukkan nama akun')
                            ->validationMessages(ValidationMessages::akunField('nama')),

                        Select::make('tipe')
                            ->label('Tipe Akun')
                            ->required()
                            ->options(Akun::getTipeOptions())
                            ->live() // Untuk trigger perubahan form
                            ->afterStateUpdated(function (callable $set, $state) {
                                // Reset field yang tidak diperlukan saat tipe berubah
                                if ($state !== Akun::TIPE_E_WALLET) {
                                    $set('nama_ewallet', null);
                                    $set('nomor_hp', null);
                                }
                                if (!in_array($state, [Akun::TIPE_BANK, Akun::TIPE_KREDIT])) {
                                    $set('nama_bank', null);
                                    $set('nomor_rekening', null);
                                }
                            })
                            ->validationMessages(ValidationMessages::akunField('tipe')),

                        TextInput::make('saldo_awal')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->minValue(0)
                            ->maxValue(999999999999.99)
                            ->validationMessages(ValidationMessages::akunField('saldo_awal')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Bank/Kredit')
                    ->schema([
                        TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->maxLength(100)
                            ->placeholder('Contoh: BCA, Mandiri, BNI')
                            ->required(fn (Get $get): bool => in_array($get('tipe'), [Akun::TIPE_BANK, Akun::TIPE_KREDIT]))
                            ->validationMessages(ValidationMessages::akunField('nama_bank')),

                        TextInput::make('nomor_rekening')
                            ->label('Nomor Rekening')
                            ->maxLength(50)
                            ->placeholder('Masukkan nomor rekening')
                            ->required(fn (Get $get): bool => in_array($get('tipe'), [Akun::TIPE_BANK, Akun::TIPE_KREDIT]))
                            ->validationMessages(ValidationMessages::akunField('nomor_rekening')),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('tipe'), [Akun::TIPE_BANK, Akun::TIPE_KREDIT])),

                Forms\Components\Section::make('Detail E-Wallet')
                    ->schema([
                        TextInput::make('nama_ewallet')
                            ->label('Nama E-Wallet')
                            ->maxLength(50)
                            ->placeholder('Contoh: GoPay, OVO, Dana')
                            ->required(fn (Get $get): bool => $get('tipe') === Akun::TIPE_E_WALLET)
                            ->validationMessages(ValidationMessages::akunField('nama_ewallet')),

                        TextInput::make('nomor_hp')
                            ->label('Nomor HP')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Contoh: 08123456789')
                            ->regex('/^(\+62|0)[0-9]{8,13}$/')
                            ->required(fn (Get $get): bool => $get('tipe') === Akun::TIPE_E_WALLET)
                            ->validationMessages(ValidationMessages::akunField('nomor_hp')),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('tipe') === Akun::TIPE_E_WALLET),

                Forms\Components\Section::make('Pengaturan Tambahan')
                    ->schema([
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->placeholder('Deskripsi opsional untuk akun ini')
                            ->rows(3)
                            ->columnSpanFull()
                            ->validationMessages(ValidationMessages::akunField('deskripsi')),

                        ColorPicker::make('warna')
                            ->label('Warna')
                            ->default('#6B7280')
                            ->validationMessages(ValidationMessages::akunField('warna')),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan jika akun sudah tidak digunakan')
                            ->validationMessages(ValidationMessages::akunField('aktif')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('warna')
                    ->label('')
                    ->width(20),

                TextColumn::make('nama')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

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
                    ->formatStateUsing(fn (string $state): string => Akun::getTipeOptions()[$state] ?? $state),

                TextColumn::make('nama_bank')
                    ->label('Bank')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('nomor_rekening')
                    ->label('No. Rekening')
                    ->placeholder('—')
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('nama_ewallet')
                    ->label('E-Wallet')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->placeholder('—')
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('saldo_saat_ini')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipe')
                    ->label('Tipe Akun')
                    ->options(Akun::getTipeOptions()),

                SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Akun')
                    ->modalDescription('Apakah Anda yakin ingin menghapus akun ini? Akun yang memiliki transaksi tidak dapat dihapus.')
                    ->modalSubmitActionLabel('Hapus')
                    ->before(function (Akun $record) {
                        $service = app(AkunService::class);
                        if (!$service->canBeDeleted($record)) {
                            throw new \Exception('Akun tidak dapat dihapus karena masih memiliki transaksi.');
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Akun Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus akun yang dipilih? Akun yang memiliki transaksi tidak akan dihapus.')
                        ->modalSubmitActionLabel('Hapus'),
                ]),
            ])
            ->defaultSort('nama');
    }

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan akun milik user yang sedang login
        return parent::getEloquentQuery()->forUser(Auth::id());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAkuns::route('/'),
            'create' => Pages\CreateAkun::route('/create'),
            'view' => Pages\ViewAkun::route('/{record}'),
            'edit' => Pages\EditAkun::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $service = app(AkunService::class);
        return (string) $service->getUserAccounts(activeOnly: true)->count();
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }
}


