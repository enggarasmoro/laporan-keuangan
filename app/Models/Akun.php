<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasDefaultValues;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Akun extends Model
{
    use HasFactory, BelongsToUser, HasDefaultValues;

    protected $table = 'akuns';

    protected $fillable = [
        'user_id',
        'nama',
        'tipe',
        'saldo_awal',
        'saldo_saat_ini',
        'nomor_rekening',
        'nama_bank',
        'nama_ewallet',
        'nomor_hp',
        'deskripsi',
        'warna',
        'aktif',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_saat_ini' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    protected $attributes = [
        'saldo_awal' => 0,
        'saldo_saat_ini' => 0,
        'warna' => '#6B7280',
        'aktif' => true,
    ];

    // Konstanta untuk tipe akun
    public const TIPE_BANK = 'bank';
    public const TIPE_KAS = 'kas';
    public const TIPE_E_WALLET = 'e-wallet';
    public const TIPE_INVESTASI = 'investasi';
    public const TIPE_KREDIT = 'kredit';

    public static function getTipeOptions(): array
    {
        return [
            self::TIPE_BANK => 'Bank',
            self::TIPE_KAS => 'Kas',
            self::TIPE_E_WALLET => 'E-Wallet',
            self::TIPE_INVESTASI => 'Investasi',
            self::TIPE_KREDIT => 'Kredit',
        ];
    }

    // Scope untuk filter berdasarkan user
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return $query->where('user_id', $userId);
    }

    // Scope untuk akun aktif saja
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // TODO: Uncomment when Transaksi model is created
    // public function transaksis(): HasMany
    // {
    //     return $this->hasMany(Transaksi::class, 'akun_id');
    // }

    // Accessors
    public function getTipeFormattedAttribute(): string
    {
        return self::getTipeOptions()[$this->tipe] ?? $this->tipe;
    }

    public function getSaldoFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo_saat_ini, 0, ',', '.');
    }

    // Methods
    public function isEwallet(): bool
    {
        return $this->tipe === self::TIPE_E_WALLET;
    }

    public function isBankOrKredit(): bool
    {
        return in_array($this->tipe, [self::TIPE_BANK, self::TIPE_KREDIT]);
    }

    public function getRequiredFieldsForType(): array
    {
        if ($this->isEwallet()) {
            return ['nama_ewallet', 'nomor_hp'];
        }

        if ($this->isBankOrKredit()) {
            return ['nama_bank', 'nomor_rekening'];
        }

        return [];
    }

    /**
     * Get default values untuk model ini
     */
    protected function getDefaultValues(): array
    {
        return [
            'saldo_awal' => 0,
            'saldo_saat_ini' => 0,
            'warna' => '#6B7280',
            'aktif' => true,
        ];
    }

    // Boot method untuk default values
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->saldo_saat_ini)) {
                $model->saldo_saat_ini = $model->saldo_awal ?? 0;
            }
        });
    }
}
