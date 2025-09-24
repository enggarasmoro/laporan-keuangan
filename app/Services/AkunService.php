<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AkunService
{
    /**
     * Get all accounts for the authenticated user
     */
    public function getUserAccounts(?User $user = null, bool $activeOnly = false): Collection
    {
        $user = $user ?? Auth::user();

        $query = Akun::forUser($user->id);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('nama')->get();
    }

    /**
     * Get paginated accounts for the authenticated user
     */
    public function getPaginatedUserAccounts(?User $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $user = $user ?? Auth::user();

        return Akun::forUser($user->id)
            ->orderBy('aktif', 'desc')
            ->orderBy('nama')
            ->paginate($perPage);
    }

    /**
     * Create a new account
     */
    public function createAccount(array $data, ?User $user = null): Akun
    {
        $user = $user ?? Auth::user();

        // Ensure user_id is set
        $data['user_id'] = $user->id;

        // Set defaults for nullable fields
        $data = $this->setDefaults($data);

        // Clean fields based on account type
        $data = $this->cleanFieldsByType($data);

        return DB::transaction(function () use ($data) {
            $akun = Akun::create($data);

            // Update saldo_saat_ini to match saldo_awal
            $akun->update(['saldo_saat_ini' => $akun->saldo_awal]);

            return $akun->fresh();
        });
    }

    /**
     * Update an existing account
     */
    public function updateAccount(Akun $akun, array $data): Akun
    {
        // Verify ownership
        if ($akun->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to account');
        }

        // Don't allow changing user_id
        unset($data['user_id']);

        // Set defaults for nullable fields
        $data = $this->setDefaults($data);

        // Clean fields based on account type
        $data = $this->cleanFieldsByType($data);

        return DB::transaction(function () use ($akun, $data) {
            $akun->update($data);
            return $akun->fresh();
        });
    }

    /**
     * Delete an account (soft delete)
     */
    public function deleteAccount(Akun $akun): bool
    {
        // Verify ownership
        if ($akun->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to account');
        }

        // TODO: Uncomment when Transaksi model is created
        // Check if account has transactions
        // if ($akun->transaksis()->exists()) {
        //     throw new \Exception('Cannot delete account with existing transactions');
        // }

        return $akun->delete();
    }

    /**
     * Get account statistics for user
     */
    public function getAccountStatistics(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        $accounts = $this->getUserAccounts($user, true);

        return [
            'total_accounts' => $accounts->count(),
            'total_balance' => $accounts->sum('saldo_saat_ini'),
            'accounts_by_type' => $accounts->groupBy('tipe')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_balance' => $group->sum('saldo_saat_ini')
                ];
            })->toArray(),
            'active_accounts' => $accounts->where('aktif', true)->count(),
            'inactive_accounts' => $accounts->where('aktif', false)->count(),
        ];
    }

    /**
     * Get accounts filtered by type
     */
    public function getAccountsByType(string $type, ?User $user = null): Collection
    {
        $user = $user ?? Auth::user();

        return Akun::forUser($user->id)
            ->where('tipe', $type)
            ->active()
            ->orderBy('nama')
            ->get();
    }

    /**
     * Activate or deactivate an account
     */
    public function toggleAccountStatus(Akun $akun): Akun
    {
        // Verify ownership
        if ($akun->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to account');
        }

        $akun->update(['aktif' => !$akun->aktif]);

        return $akun->fresh();
    }

    /**
     * Set default values for nullable fields
     */
    private function setDefaults(array $data): array
    {
        $defaults = [
            'saldo_awal' => 0,
            'warna' => '#6B7280',
            'aktif' => true,
        ];

        foreach ($defaults as $field => $defaultValue) {
            if (!isset($data[$field]) || is_null($data[$field])) {
                $data[$field] = $defaultValue;
            }
        }

        return $data;
    }

    /**
     * Clean fields based on account type
     */
    private function cleanFieldsByType(array $data): array
    {
        $tipe = $data['tipe'] ?? null;

        // Clean ewallet fields if not ewallet type
        if ($tipe !== Akun::TIPE_E_WALLET) {
            $data['nama_ewallet'] = null;
            $data['nomor_hp'] = null;
        }

        // Clean bank fields if not bank or credit type
        if (!in_array($tipe, [Akun::TIPE_BANK, Akun::TIPE_KREDIT])) {
            $data['nomor_rekening'] = null;
            $data['nama_bank'] = null;
        }

        return $data;
    }

    /**
     * Validate if account can be deleted
     */
    public function canBeDeleted(Akun $akun): bool
    {
        // TODO: Uncomment when Transaksi model is created
        // return $akun->user_id === Auth::id() && $akun->transaksis()->doesntExist();

        // For now, allow deletion until Transaksi model is implemented
        return $akun->user_id === Auth::id();
    }

    /**
     * Get account options for select fields
     */
    public function getAccountOptions(?User $user = null, bool $activeOnly = true): array
    {
        $accounts = $this->getUserAccounts($user, $activeOnly);

        return $accounts->mapWithKeys(function ($akun) {
            return [$akun->id => $akun->nama . ' (' . $akun->tipe_formatted . ')'];
        })->toArray();
    }
}
