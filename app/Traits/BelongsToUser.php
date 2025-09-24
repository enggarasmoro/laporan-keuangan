<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    /**
     * Boot trait untuk otomatis filter berdasarkan user
     */
    protected static function bootBelongsToUser(): void
    {
        static::creating(function ($model) {
            if (is_null($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });

        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where($builder->getModel()->getTable() . '.user_id', Auth::id());
            }
        });
    }

    /**
     * Scope untuk filter berdasarkan user tertentu
     */
    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        $userId = $userId ?? Auth::id();
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk mengabaikan global scope user
     */
    public function scopeWithoutUserScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('user');
    }

    /**
     * Cek apakah model ini milik user yang sedang login
     */
    public function belongsToCurrentUser(): bool
    {
        return $this->user_id === Auth::id();
    }
}
