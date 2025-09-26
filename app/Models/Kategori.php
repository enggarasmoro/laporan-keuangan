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

    public function getIconWithFallbackAttribute(): string
    {
        return $this->icon ?? ($this->tipe === self::TIPE_PEMASUKAN ? '💰' : '💸');
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

    // Boot method untuk default values
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set default icon based on type if not provided
            if (empty($model->icon)) {
                $model->icon = $model->tipe === self::TIPE_PEMASUKAN ? '💰' : '💸';
            }
        });
    }
}
