<?php

namespace App\Traits;

trait HasColorCoding
{
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

            // Orange variants for transport
            '#F59E0B' => 'Orange Terang',
            '#D97706' => 'Orange Sedang',
            '#B45309' => 'Orange Tua',

            // Blue variants for utilities
            '#3B82F6' => 'Biru Terang',
            '#2563EB' => 'Biru Sedang',
            '#1D4ED8' => 'Biru Tua',

            // Purple variants for entertainment
            '#8B5CF6' => 'Ungu Terang',
            '#7C3AED' => 'Ungu Sedang',
            '#6D28D9' => 'Ungu Tua',

            // Pink variants for shopping
            '#EC4899' => 'Pink Terang',
            '#DB2777' => 'Pink Sedang',
            '#BE185D' => 'Pink Tua',

            // Teal variants for health
            '#14B8A6' => 'Teal Terang',
            '#0D9488' => 'Teal Sedang',
            '#0F766E' => 'Teal Tua',

            // Gray variants for others
            '#6B7280' => 'Abu-abu Terang',
            '#4B5563' => 'Abu-abu Sedang',
            '#374151' => 'Abu-abu Tua',
        ];
    }

    /**
     * Get color suggestions based on category type and icon
     */
    public function getSuggestedColors(): array
    {
        if ($this->tipe === 'pemasukan') {
            return ['#10B981', '#059669', '#047857'];
        }

        // Color suggestions based on icon for expenses
        return match($this->icon ?? '') {
            '🍽️', '☕', '🍕', '🥗', '🍜' => ['#EF4444', '#DC2626'], // Food - Red
            '🚗', '🏍️', '🚌', '⛽', '🚕' => ['#F59E0B', '#D97706'], // Transport - Orange
            '🛍️', '👕', '📱', '🏠', '📚' => ['#EC4899', '#DB2777'], // Shopping - Pink
            '📄', '💡', '💧', '📺', '📞' => ['#3B82F6', '#2563EB'], // Bills - Blue
            '🎮', '🎬', '🎵', '⚽', '🎪' => ['#8B5CF6', '#7C3AED'], // Entertainment - Purple
            '🏥', '💊', '🦷', '👓', '💆' => ['#14B8A6', '#0D9488'], // Health - Teal
            '👶', '🎓', '🧸', '👨‍👩‍👧‍👦' => ['#F59E0B', '#D97706'], // Family - Orange
            default => ['#6B7280', '#4B5563'] // Default - Gray
        };
    }

    /**
     * Auto set color based on type and icon
     */
    public function setSmartColor(): void
    {
        if (empty($this->warna) || $this->warna === '#6B7280') {
            $colors = $this->getSuggestedColors();
            $this->warna = $colors[array_rand($colors)];
        }
    }

    /**
     * Get hex color as RGB array
     */
    public function getColorRgb(): array
    {
        $hex = str_replace('#', '', $this->warna ?? '#6B7280');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Check if color is dark (for text contrast)
     */
    public function isColorDark(): bool
    {
        $rgb = $this->getColorRgb();
        $brightness = (($rgb['r'] * 299) + ($rgb['g'] * 587) + ($rgb['b'] * 114)) / 1000;
        return $brightness < 128;
    }

    /**
     * Get contrasting text color (white or black)
     */
    public function getContrastTextColor(): string
    {
        return $this->isColorDark() ? '#FFFFFF' : '#000000';
    }

    /**
     * Boot trait untuk set smart color
     */
    protected static function bootHasColorCoding(): void
    {
        static::creating(function ($model) {
            if (method_exists($model, 'setSmartColor')) {
                $model->setSmartColor();
            }
        });

        static::updating(function ($model) {
            // Auto update color if icon changed but color is still default
            if ($model->isDirty('icon') && ($model->warna === '#6B7280' || empty($model->warna))) {
                if (method_exists($model, 'setSmartColor')) {
                    $model->setSmartColor();
                }
            }
        });
    }
}
