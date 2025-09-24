<?php

namespace App\Traits;

trait HasDefaultValues
{
    /**
     * Set default values untuk field nullable
     */
    protected function setDefaultValues(array $attributes = []): array
    {
        $defaults = $this->getDefaultValues();

        foreach ($defaults as $field => $defaultValue) {
            if (!isset($attributes[$field]) || is_null($attributes[$field])) {
                $attributes[$field] = $defaultValue;
            }
        }

        return $attributes;
    }

    /**
     * Get default values untuk model ini
     * Override method ini di model untuk menentukan default values
     */
    protected function getDefaultValues(): array
    {
        return [];
    }

    /**
     * Boot trait untuk otomatis set default values saat creating
     */
    protected static function bootHasDefaultValues(): void
    {
        static::creating(function ($model) {
            if (method_exists($model, 'getDefaultValues')) {
                $defaults = $model->getDefaultValues();

                foreach ($defaults as $field => $defaultValue) {
                    if (is_null($model->$field)) {
                        $model->$field = $defaultValue;
                    }
                }
            }
        });
    }
}
