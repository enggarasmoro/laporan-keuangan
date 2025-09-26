<?php

namespace App\Policies;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KategoriPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // User bisa melihat daftar kategori miliknya sendiri
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kategori $kategori): bool
    {
        return $kategori->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // User bisa membuat kategori baru
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kategori $kategori): bool
    {
        return $kategori->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kategori $kategori): bool
    {
        // User bisa menghapus kategori miliknya sendiri
        // Tapi mungkin perlu cek apakah kategori masih memiliki transaksi
        return $kategori->user_id === $user->id && $this->canBeDeleted($kategori);
    }

    /**
     * Check if category can be deleted (no transactions or other dependencies)
     */
    private function canBeDeleted(Kategori $kategori): bool
    {
        // TODO: Uncomment when Transaksi model is created
        // Cek apakah kategori masih memiliki transaksi
        // return $kategori->transaksis()->doesntExist();

        // For now, allow deletion until Transaksi model is implemented
        return true;
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return true; // Izinkan bulk delete untuk kategori milik user
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Kategori $kategori): bool
    {
        return $kategori->user_id === $user->id;
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return true;
    }
}
