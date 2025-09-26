<?php

namespace App\Traits;

trait HasIcon
{
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

    /**
     * Set default icon based on category type
     */
    public function setDefaultIcon(): void
    {
        if (empty($this->icon)) {
            $this->icon = match($this->tipe ?? null) {
                'pemasukan' => '💰',
                'pengeluaran' => '💸',
                default => '❓'
            };
        }
    }

    /**
     * Boot trait untuk set default icon
     */
    protected static function bootHasIcon(): void
    {
        static::creating(function ($model) {
            if (empty($model->icon) && method_exists($model, 'setDefaultIcon')) {
                $model->setDefaultIcon();
            }
        });
    }
}
