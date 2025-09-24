<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AkunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // User bisa melihat daftar akun miliknya sendiri
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Akun $akun): bool
    {
        return $akun->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // User bisa membuat akun baru
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Akun $akun): bool
    {
        return $akun->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Akun $akun): bool
    {
        // User bisa menghapus akun miliknya sendiri
        // Tapi mungkin perlu cek apakah akun masih memiliki transaksi
        return $akun->user_id === $user->id && $this->canBeDeleted($akun);
    }



    /**
     * Check if account can be deleted (no transactions or other dependencies)
     */
    private function canBeDeleted(Akun $akun): bool
    {
        // TODO: Uncomment when Transaksi model is created
        // Cek apakah akun masih memiliki transaksi
        // return $akun->transaksis()->doesntExist();

        // For now, allow deletion until Transaksi model is implemented
        return true;
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return true; // Izinkan bulk delete untuk akun milik user
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Akun $akun): bool
    {
        return $akun->user_id === $user->id;
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return true;
    }
}
