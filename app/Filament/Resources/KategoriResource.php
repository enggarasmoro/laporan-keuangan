<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriResource\Pages;
use App\Filament\Tables\KategoriTable;
use App\Models\Kategori;
use App\Services\KategoriService;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

class KategoriResource extends Resource
{
    protected static ?string $model = Kategori::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategori';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Kategori')
                            ->placeholder('Masukkan nama kategori'),

                        Select::make('tipe')
                            ->label('Tipe Kategori')
                            ->options(Kategori::getTipeOptions())
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset icon when type changes
                                $set('icon', null);
                                // Set default color based on type
                                if ($state === Kategori::TIPE_PEMASUKAN) {
                                    $set('warna', '#10B981');
                                } else {
                                    $set('warna', '#EF4444');
                                }
                            }),

                        Select::make('icon')
                            ->label('Icon')
                            ->options(function (callable $get) {
                                $tipe = $get('tipe');
                                if ($tipe === Kategori::TIPE_PEMASUKAN) {
                                    return Kategori::getPemasukanIcons();
                                } elseif ($tipe === Kategori::TIPE_PENGELUARAN) {
                                    return Kategori::getPengeluaranIcons();
                                }
                                return [];
                            })
                            ->searchable()
                            ->allowHtml()
                            ->helperText('Pilih icon yang sesuai dengan kategori Anda')
                            ->placeholder(function (callable $get) {
                                $tipe = $get('tipe');
                                if (empty($tipe)) {
                                    return 'Pilih tipe kategori terlebih dahulu...';
                                }
                                return 'Pilih icon...';
                            })
                            ->disabled(fn (callable $get) => empty($get('tipe')))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Auto set color based on category type and icon
                                $tipe = $get('tipe');
                                $currentColor = $get('warna');

                                // Only auto-set color if it's still default or empty
                                if ($currentColor === '#6B7280' || $currentColor === '#10B981' || $currentColor === '#EF4444' || empty($currentColor)) {
                                    if ($tipe === Kategori::TIPE_PEMASUKAN) {
                                        $colors = ['#10B981', '#059669', '#047857']; // Green variants
                                        $set('warna', $colors[array_rand($colors)]);
                                    } else {
                                        // Set different colors based on icon category
                                        $colors = match($state) {
                                            '🍽️', '☕', '🍕', '🥗', '🍜' => ['#EF4444', '#DC2626'], // Food - Red
                                            '🚗', '🏍️', '🚌', '⛽', '🚕' => ['#F59E0B', '#D97706'], // Transport - Orange
                                            '🛍️', '👕', '📱', '🏠', '📚' => ['#EC4899', '#DB2777'], // Shopping - Pink
                                            '📄', '💡', '💧', '📺', '📞' => ['#6366F1', '#5B21B6'], // Bills - Indigo
                                            '🎮', '🎬', '🎵', '⚽', '🎪' => ['#8B5CF6', '#7C3AED'], // Entertainment - Purple
                                            '🏥', '💊', '🦷', '👓', '💆' => ['#14B8A6', '#0D9488'], // Health - Teal
                                            '👶', '🎓', '🧸', '👨‍👩‍👧‍👦' => ['#F59E0B', '#D97706'], // Family - Orange
                                            default => ['#6B7280', '#4B5563'] // Default - Gray
                                        };
                                        $set('warna', $colors[array_rand($colors)]);
                                    }
                                }
                            }),

                        ColorPicker::make('warna')
                            ->label('Warna')
                            ->default('#6B7280')
                            ->helperText('Pilih warna untuk membedakan kategori'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Tambahan')
                    ->schema([
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Opsional: deskripsi singkat tentang kategori ini'),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Kategori yang tidak aktif tidak akan muncul saat membuat transaksi'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(KategoriTable::make())
            ->filters(KategoriTable::getFilters())
            ->defaultSort(KategoriTable::getDefaultSort(), KategoriTable::getDefaultSortDirection())
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading(KategoriTable::getEmptyStateHeading())
            ->emptyStateDescription(KategoriTable::getEmptyStateDescription())
            ->emptyStateIcon(KategoriTable::getEmptyStateIcon())
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Kategori')
                    ->modalDescription('Apakah Anda yakin ingin menghapus kategori ini? Kategori yang memiliki transaksi tidak dapat dihapus.')
                    ->modalSubmitActionLabel('Hapus')
                    ->before(function (Kategori $record) {
                        $service = app(KategoriService::class);
                        if (!$service->canBeDeleted($record)) {
                            throw new \Exception('Kategori tidak dapat dihapus karena masih memiliki transaksi.');
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Kategori Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus kategori yang dipilih? Kategori yang memiliki transaksi tidak akan dihapus.')
                        ->modalSubmitActionLabel('Hapus'),
                ]),
            ])
            ->defaultSort('nama');
    }

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan kategori milik user yang sedang login
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
            'index' => Pages\ListKategoris::route('/'),
            'create' => Pages\CreateKategori::route('/create'),
            'view' => Pages\ViewKategori::route('/{record}'),
            'edit' => Pages\EditKategori::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $service = app(KategoriService::class);
        return (string) $service->getUserCategories(activeOnly: true)->count();
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }
}
