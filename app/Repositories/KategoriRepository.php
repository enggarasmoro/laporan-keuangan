<?php

namespace App\Repositories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class KategoriRepository extends BaseRepository
{
    protected $statusColumn = 'aktif'; // Kategori uses 'aktif' column

    public function __construct(Kategori $model)
    {
        parent::__construct($model);
    }

    /**
     * Get kategoris by tipe for current user
     */
    public function getByTipe(string $tipe, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('tipe', $tipe)
                          ->get();
    }

    /**
     * Get kategoris for specific tipes
     */
    public function getByTipes(array $tipes, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->whereIn('tipe', $tipes)
                          ->get();
    }

    /**
     * Search kategoris by name
     */
    public function searchByName(string $search, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('nama', 'like', "%{$search}%")
                          ->get();
    }

    /**
     * Get kategori statistics for current user
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
                'pemasukan' => $query->where('tipe', Kategori::TIPE_PEMASUKAN)->count(),
                'pengeluaran' => $query->where('tipe', Kategori::TIPE_PENGELUARAN)->count(),
            ]
        ];
    }

    /**
     * Create default kategoris for new user
     */
    public function createDefaultKategoris(int $userId): Collection
    {
        $defaultKategoris = [
            // Kategori Pemasukan
            [
                'nama' => 'Gaji',
                'tipe' => Kategori::TIPE_PEMASUKAN,
                'icon' => 'heroicon-o-banknotes',
                'warna' => '#10B981',
                'deskripsi' => 'Pendapatan dari gaji',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Bonus',
                'tipe' => Kategori::TIPE_PEMASUKAN,
                'icon' => 'heroicon-o-gift',
                'warna' => '#3B82F6',
                'deskripsi' => 'Bonus atau tunjangan',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Investasi',
                'tipe' => Kategori::TIPE_PEMASUKAN,
                'icon' => 'heroicon-o-chart-bar',
                'warna' => '#8B5CF6',
                'deskripsi' => 'Hasil investasi',
                'user_id' => $userId,
                'aktif' => true,
            ],

            // Kategori Pengeluaran
            [
                'nama' => 'Makanan',
                'tipe' => Kategori::TIPE_PENGELUARAN,
                'icon' => 'heroicon-o-cake',
                'warna' => '#F59E0B',
                'deskripsi' => 'Pengeluaran untuk makanan',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Transportasi',
                'tipe' => Kategori::TIPE_PENGELUARAN,
                'icon' => 'heroicon-o-truck',
                'warna' => '#EF4444',
                'deskripsi' => 'Biaya transportasi',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Belanja',
                'tipe' => Kategori::TIPE_PENGELUARAN,
                'icon' => 'heroicon-o-shopping-bag',
                'warna' => '#EC4899',
                'deskripsi' => 'Belanja kebutuhan',
                'user_id' => $userId,
                'aktif' => true,
            ],
            [
                'nama' => 'Hiburan',
                'tipe' => Kategori::TIPE_PENGELUARAN,
                'icon' => 'heroicon-o-film',
                'warna' => '#6366F1',
                'deskripsi' => 'Pengeluaran hiburan',
                'user_id' => $userId,
                'aktif' => true,
            ]
        ];

        $createdKategoris = new Collection();

        foreach ($defaultKategoris as $kategoriData) {
            $createdKategoris->push($this->model->create($kategoriData));
        }

        return $createdKategoris;
    }

    /**
     * Get kategoris by tipe with icon and color
     */
    public function getActiveByTipeWithIcon(string $tipe, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where('tipe', $tipe)
                          ->where('aktif', true)
                          ->select('id', 'nama', 'tipe', 'icon', 'warna')
                          ->get();
    }

    /**
     * Get popular kategoris (most used)
     */
    public function getPopularKategoris(int $limit = 5, int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();

        // Note: This will need to be updated when Transaksi table exists
        // For now, just return active kategoris
        return $this->model->where('user_id', $userId)
                          ->where('aktif', true)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Check if kategori name exists for user
     */
    public function nameExists(string $nama, int $userId = null, int $excludeId = null): bool
    {
        $userId = $userId ?: Auth::id();

        $query = $this->model->where('user_id', $userId)
                            ->where('nama', $nama);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
