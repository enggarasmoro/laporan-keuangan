<?php

namespace App\Repositories;

use App\Models\Akun;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AkunRepository extends BaseRepository
{
    protected $statusColumn = 'aktif'; // Akun uses 'aktif' column

    public function __construct(Akun $model)
    {
        parent::__construct($model);
    }

    /**
     * Get akuns by tipe for current user
     */
    public function getByTipe(string $tipe, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('tipe', $tipe)
                          ->get();
    }

    /**
     * Get akuns for specific tipes
     */
    public function getByTipes(array $tipes, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->whereIn('tipe', $tipes)
                          ->get();
    }

    /**
     * Search akuns by name
     */
    public function searchByName(string $search, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('nama', 'like', "%{$search}%")
                          ->get();
    }

    /**
     * Get akun statistics for current user
     */
    public function getStatistics(int $userId = null): array
    {
        $userId = $userId ?: Auth::id();

        $query = $this->model->where('user_id', $userId);

        return [
            'total' => $query->count(),
            'active' => $query->where('aktif', true)->count(),
            'inactive' => $query->where('aktif', false)->count(),
            'by_tipe' => [
                'kas' => $query->where('tipe', Akun::TIPE_KAS)->count(),
                'bank' => $query->where('tipe', Akun::TIPE_BANK)->count(),
                'e-wallet' => $query->where('tipe', Akun::TIPE_E_WALLET)->count(),
                'investasi' => $query->where('tipe', Akun::TIPE_INVESTASI)->count(),
                'kredit' => $query->where('tipe', Akun::TIPE_KREDIT)->count(),
            ]
        ];
    }

    /**
     * Create default akuns for new user
     */
    public function createDefaultAkuns(int $userId): Collection
    {
        $defaultAkuns = [
            [
                'nama' => 'Kas',
                'tipe' => Akun::TIPE_KAS,
                'saldo_awal' => 0,
                'deskripsi' => 'Kas default',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Bank BCA',
                'tipe' => Akun::TIPE_BANK,
                'nama_bank' => 'BCA',
                'saldo_awal' => 0,
                'deskripsi' => 'Rekening Bank BCA',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Bank Mandiri',
                'tipe' => Akun::TIPE_BANK,
                'nama_bank' => 'Mandiri',
                'saldo_awal' => 0,
                'deskripsi' => 'Rekening Bank Mandiri',
                'user_id' => $userId,
                'aktif' => true,
            ]
        ];

        $createdAkuns = new Collection();

        foreach ($defaultAkuns as $akunData) {
            $createdAkuns->push($this->model->create($akunData));
        }

        return $createdAkuns;
    }

    /**
     * Get active akuns with balance for current user
     */
    public function getActiveWithBalance(int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('aktif', true)
                          ->select('id', 'nama', 'tipe', 'saldo_awal')
                          ->get();
    }

    /**
     * Update saldo for akun
     */
    public function updateSaldo(int $id, float $newSaldo, int $userId = null): bool
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('id', $id)
                          ->where('user_id', $userId)
                          ->update(['saldo_awal' => $newSaldo]);
    }

    /**
     * Update both saldo_awal and saldo_saat_ini for akun
     */
    public function updateBalance(int $id, float $saldoAwal, float $saldoSaatIni = null, int $userId = null): bool
    {
        $userId = $userId ?: Auth::id();
        $saldoSaatIni = $saldoSaatIni ?? $saldoAwal; // Default saldo_saat_ini to saldo_awal

        return $this->model->where('id', $id)
                          ->where('user_id', $userId)
                          ->update([
                              'saldo_awal' => $saldoAwal,
                              'saldo_saat_ini' => $saldoSaatIni
                          ]);
    }
}
