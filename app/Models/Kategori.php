<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasDefaultValues;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Kategori extends Model
{
    use HasFactory, BelongsToUser, HasDefaultValues;

    protected $table = 'kategoris';

    protected $fillable = [
        'user_id',
        'nama',
        'tipe',
        'deskripsi',
        'icon',
        'warna',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    protected $attributes = [
        'warna' => '#6B7280',
        'aktif' => true,
    ];

    // Konstanta untuk tipe kategori
    public const TIPE_PEMASUKAN = 'pemasukan';
    public const TIPE_PENGELUARAN = 'pengeluaran';

    public static function getTipeOptions(): array
    {
        return [
            self::TIPE_PEMASUKAN => 'Pemasukan',
            self::TIPE_PENGELUARAN => 'Pengeluaran',
        ];
    }

    public static function getIconOptions(): array
    {
        return [
            // Pemasukan Icons
            '💰' => '💰 Uang/Gaji',
            '💵' => '💵 Uang Tunai',
            '💳' => '💳 Kartu/Transfer',
            '🎁' => '🎁 Bonus/Hadiah',
            '📈' => '📈 Investasi/Dividen',
            '🏪' => '🏪 Bisnis/Usaha',
            '💼' => '💼 Pekerjaan',
            '🎯' => '🎯 Target/Reward',
            '🏆' => '🏆 Achievement',

            // Pengeluaran Icons - Makanan & Minuman
            '🍽️' => '🍽️ Makan & Minum',
            '☕' => '☕ Kopi/Minuman',
            '🍕' => '🍕 Fast Food',
            '🥗' => '🥗 Makanan Sehat',
            '🍜' => '🍜 Makanan Lokal',

            // Transportasi
            '🚗' => '🚗 Mobil',
            '🏍️' => '🏍️ Motor',
            '🚌' => '🚌 Transportasi Umum',
            '⛽' => '⛽ Bensin',
            '🚕' => '🚕 Taxi/Ojek Online',

            // Belanja
            '🛍️' => '🛍️ Belanja',
            '👕' => '👕 Pakaian',
            '📱' => '📱 Gadget/Elektronik',
            '🏠' => '🏠 Rumah Tangga',
            '📚' => '📚 Buku/Pendidikan',

            // Tagihan & Utilitas
            '📄' => '📄 Tagihan',
            '💡' => '💡 Listrik',
            '💧' => '💧 Air',
            '📺' => '📺 Internet/TV',
            '📞' => '📞 Telepon/Pulsa',

            // Hiburan
            '🎮' => '🎮 Gaming',
            '🎬' => '🎬 Film/Bioskop',
            '🎵' => '🎵 Musik/Streaming',
            '⚽' => '⚽ Olahraga',
            '🎪' => '🎪 Hiburan Lain',

            // Kesehatan
            '🏥' => '🏥 Kesehatan',
            '💊' => '💊 Obat-obatan',
            '🦷' => '🦷 Dokter Gigi',
            '👓' => '👓 Kacamata/Optik',
            '💆' => '💆 Perawatan',

            // Keluarga & Anak
            '👶' => '👶 Kebutuhan Bayi',
            '🎓' => '🎓 Pendidikan Anak',
            '🧸' => '🧸 Mainan Anak',
            '👨‍👩‍👧‍👦' => '👨‍👩‍👧‍👦 Keluarga',

            // Lain-lain
            '💸' => '💸 Pengeluaran Lain',
            '❓' => '❓ Tidak Terkategorikan',
            '📊' => '📊 Administrasi',
        ];
    }

    public static function getPemasukanIcons(): array
    {
        return [
            '💰' => '💰 Uang/Gaji',
            '💵' => '💵 Uang Tunai',
            '💳' => '💳 Kartu/Transfer',
            '🎁' => '🎁 Bonus/Hadiah',
            '📈' => '📈 Investasi/Dividen',
            '🏪' => '🏪 Bisnis/Usaha',
            '💼' => '💼 Pekerjaan',
            '🎯' => '🎯 Target/Reward',
            '🏆' => '🏆 Achievement',
            '📊' => '📊 Administrasi',
        ];
    }

    public static function getPengeluaranIcons(): array
    {
        return [
            // Makanan & Minuman
            '🍽️' => '🍽️ Makan & Minum',
            '☕' => '☕ Kopi/Minuman',
            '🍕' => '🍕 Fast Food',
            '🥗' => '🥗 Makanan Sehat',
            '🍜' => '🍜 Makanan Lokal',

            // Transportasi
            '🚗' => '🚗 Mobil',
            '🏍️' => '🏍️ Motor',
            '🚌' => '🚌 Transportasi Umum',
            '⛽' => '⛽ Bensin',
            '🚕' => '🚕 Taxi/Ojek Online',

            // Belanja
            '🛍️' => '🛍️ Belanja',
            '👕' => '👕 Pakaian',
            '📱' => '📱 Gadget/Elektronik',
            '🏠' => '🏠 Rumah Tangga',
            '📚' => '📚 Buku/Pendidikan',

            // Tagihan & Utilitas
            '📄' => '📄 Tagihan',
            '💡' => '💡 Listrik',
            '💧' => '💧 Air',
            '📺' => '📺 Internet/TV',
            '📞' => '📞 Telepon/Pulsa',

            // Hiburan
            '🎮' => '🎮 Gaming',
            '🎬' => '🎬 Film/Bioskop',
            '🎵' => '🎵 Musik/Streaming',
            '⚽' => '⚽ Olahraga',
            '🎪' => '🎪 Hiburan Lain',

            // Kesehatan
            '🏥' => '🏥 Kesehatan',
            '💊' => '💊 Obat-obatan',
            '🦷' => '🦷 Dokter Gigi',
            '👓' => '👓 Kacamata/Optik',
            '💆' => '💆 Perawatan',

            // Keluarga & Anak
            '👶' => '👶 Kebutuhan Bayi',
            '🎓' => '🎓 Pendidikan Anak',
            '🧸' => '🧸 Mainan Anak',
            '👨‍👩‍👧‍👦' => '👨‍👩‍👧‍👦 Keluarga',

            // Lain-lain
            '💸' => '💸 Pengeluaran Lain',
            '❓' => '❓ Tidak Terkategorikan',
            '📊' => '📊 Administrasi',
        ];
    }

    // Scope untuk filter berdasarkan user
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return $query->where('user_id', $userId);
    }

    // Scope untuk kategori aktif saja
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    // Scope untuk filter berdasarkan tipe
    public function scopeByType($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // TODO: Uncomment when Transaksi model is created
    // public function transaksis(): HasMany
    // {
    //     return $this->hasMany(Transaksi::class, 'kategori_id');
    // }

    // Accessors
    public function getTipeFormattedAttribute(): string
    {
        return self::getTipeOptions()[$this->tipe] ?? $this->tipe;
    }



    // Methods
    public function isPemasukan(): bool
    {
        return $this->tipe === self::TIPE_PEMASUKAN;
    }

    public function isPengeluaran(): bool
    {
        return $this->tipe === self::TIPE_PENGELUARAN;
    }

    /**
     * Get default values untuk model ini
     */
    protected function getDefaultValues(): array
    {
        return [
            'warna' => '#6B7280',
            'aktif' => true,
        ];
    }

    // =====================================
    // ICON METHODS (from HasIcon trait)
    // =====================================

    /**
     * Get icon with fallback based on category type
     */
    public function getIconWithFallbackAttribute(): string
    {
        if (!empty($this->icon)) {
            return $this->icon;
        }

        // Fallback icon based on type
        return match($this->tipe ?? null) {
            'pemasukan' => '💰',
            'pengeluaran' => '💸',
            default => '❓'
        };
    }

    /**
     * Get formatted icon display with name
     */
    public function getIconDisplayAttribute(): string
    {
        $icon = $this->icon_with_fallback;
        return $icon . ' ' . $this->nama;
    }

    /**
     * Scope for categories with custom icons
     */
    public function scopeWithCustomIcon($query)
    {
        return $query->whereNotNull('icon');
    }

    /**
     * Scope for categories using default icons
     */
    public function scopeWithDefaultIcon($query)
    {
        return $query->whereNull('icon');
    }

    // =====================================
    // COLOR METHODS (from HasColorCoding trait)
    // =====================================

    /**
     * Get predefined colors for different category types
     */
    public static function getColorOptions(): array
    {
        return [
            // Green variants for income
            '#10B981' => 'Hijau Terang',
            '#059669' => 'Hijau Sedang',
            '#047857' => 'Hijau Tua',

            // Red variants for expenses
            '#EF4444' => 'Merah Terang',
            '#DC2626' => 'Merah Sedang',
            '#B91C1C' => 'Merah Tua',

            // Orange variants
            '#F59E0B' => 'Orange Terang',
            '#D97706' => 'Orange Sedang',

            // Blue variants
            '#3B82F6' => 'Biru Terang',
            '#2563EB' => 'Biru Sedang',

            // Purple variants
            '#8B5CF6' => 'Ungu Terang',
            '#7C3AED' => 'Ungu Sedang',

            // Pink variants
            '#EC4899' => 'Pink Terang',
            '#DB2777' => 'Pink Sedang',

            // Teal variants
            '#14B8A6' => 'Teal Terang',
            '#0D9488' => 'Teal Sedang',

            // Gray variants
            '#6B7280' => 'Abu-abu Terang',
            '#4B5563' => 'Abu-abu Sedang',
        ];
    }

    /**
     * Get smart color suggestions based on category type
     */
    public static function getSmartColorSuggestions(string $tipe): array
    {
        return match($tipe) {
            self::TIPE_PEMASUKAN => [
                '#10B981', '#059669', '#047857', // Greens
                '#14B8A6', '#0D9488', // Teals
                '#3B82F6', '#2563EB', // Blues
            ],
            self::TIPE_PENGELUARAN => [
                '#EF4444', '#DC2626', '#B91C1C', // Reds
                '#F59E0B', '#D97706', // Oranges
                '#EC4899', '#DB2777', // Pinks
                '#8B5CF6', '#7C3AED', // Purples
            ],
            default => ['#6B7280', '#4B5563'] // Grays
        };
    }

    // Boot method untuk default values
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set default icon based on type if not provided
            if (empty($model->icon)) {
                $model->icon = $model->tipe === self::TIPE_PEMASUKAN ? '💰' : '💸';
            }

            // Set smart color based on type if not provided
            if (empty($model->warna)) {
                $suggestions = self::getSmartColorSuggestions($model->tipe);
                $model->warna = $suggestions[0] ?? '#6B7280';
            }
        });
    }
}
