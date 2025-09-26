<?php

namespace App\Services;

use App\Models\Kategori;
use App\Models\User;
use App\Repositories\KategoriRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KategoriService
{
    protected $kategoriRepository;

    public function __construct(KategoriRepository $kategoriRepository)
    {
        $this->kategoriRepository = $kategoriRepository;
    }
    /**
     * Get all categories for the authenticated user
     */
    public function getUserCategories(?User $user = null, bool $activeOnly = false, ?string $tipe = null): Collection
    {
        $user = $user ?? Auth::user();

        if ($tipe && $activeOnly) {
            return $this->kategoriRepository->getActiveByTipeWithIcon($tipe, $user->id);
        } elseif ($tipe) {
            return $this->kategoriRepository->getByTipe($tipe, $user->id);
        } elseif ($activeOnly) {
            return $this->kategoriRepository->getActiveForUser($user->id);
        }

        return $this->kategoriRepository->getAllForUser($user->id);
    }

    /**
     * Get paginated categories for the authenticated user
     */
    public function getPaginatedUserCategories(?User $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $user = $user ?? Auth::user();

        return $this->kategoriRepository->getPaginated($perPage, $user->id);
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data, ?User $user = null): Kategori
    {
        $user = $user ?? Auth::user();

        // Ensure user_id is set
        $data['user_id'] = $user->id;

        // Set defaults for nullable fields
        $data = $this->setDefaults($data);

        return DB::transaction(function () use ($data) {
            return $this->kategoriRepository->create($data);
        });
    }

    /**
     * Update an existing category
     */
    public function updateCategory(Kategori $kategori, array $data): Kategori
    {
        // Verify ownership
        if ($kategori->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to category');
        }

        // Don't allow changing user_id
        unset($data['user_id']);

        // Set defaults for nullable fields
        $data = $this->setDefaults($data);

        return DB::transaction(function () use ($kategori, $data) {
            $kategori->update($data);
            return $kategori->fresh();
        });
    }

    /**
     * Delete a category (soft delete)
     */
    public function deleteCategory(Kategori $kategori): bool
    {
        // Verify ownership
        if ($kategori->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to category');
        }

        // TODO: Uncomment when Transaksi model is created
        // Check if category has transactions
        // if ($kategori->transaksis()->exists()) {
        //     throw new \Exception('Cannot delete category with existing transactions');
        // }

        return $kategori->delete();
    }

    /**
     * Get category statistics for user
     */
    public function getCategoryStatistics(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        $categories = $this->getUserCategories($user, true);

        return [
            'total_categories' => $categories->count(),
            'categories_by_type' => $categories->groupBy('tipe')->map(function ($group) {
                return [
                    'count' => $group->count(),
                ];
            })->toArray(),
            'active_categories' => $categories->where('aktif', true)->count(),
            'inactive_categories' => $categories->where('aktif', false)->count(),
            'pemasukan_categories' => $categories->where('tipe', Kategori::TIPE_PEMASUKAN)->count(),
            'pengeluaran_categories' => $categories->where('tipe', Kategori::TIPE_PENGELUARAN)->count(),
        ];
    }

    /**
     * Get categories filtered by type
     */
    public function getCategoriesByType(string $type, ?User $user = null): Collection
    {
        $user = $user ?? Auth::user();

        return $this->kategoriRepository->getActiveByTipeWithIcon($type, $user->id);
    }

    /**
     * Activate or deactivate a category
     */
    public function toggleCategoryStatus(Kategori $kategori): Kategori
    {
        // Verify ownership
        if ($kategori->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to category');
        }

        $kategori->update(['aktif' => !$kategori->aktif]);

        return $kategori->fresh();
    }

    /**
     * Set default values for nullable fields
     */
    private function setDefaults(array $data): array
    {
        $defaults = [
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
     * Validate if category can be deleted
     */
    public function canBeDeleted(Kategori $kategori): bool
    {
        // TODO: Uncomment when Transaksi model is created
        // return $kategori->user_id === Auth::id() && $kategori->transaksis()->doesntExist();

        // For now, allow deletion until Transaksi model is implemented
        return $kategori->user_id === Auth::id();
    }

    /**
     * Get category options for select fields
     */
    public function getCategoryOptions(?User $user = null, bool $activeOnly = true, ?string $tipe = null): array
    {
        $categories = $this->getUserCategories($user, $activeOnly, $tipe);

        return $categories->mapWithKeys(function ($kategori) {
            $icon = $kategori->icon ? $kategori->icon . ' ' : '';
            return [$kategori->id => $icon . $kategori->nama . ' (' . $kategori->tipe_formatted . ')'];
        })->toArray();
    }

    /**
     * Create default categories for new user
     */
    public function createDefaultCategories(User $user): void
    {
        $defaultCategories = [
            // Pemasukan categories
            ['nama' => 'Gaji', 'tipe' => Kategori::TIPE_PEMASUKAN, 'icon' => '💰', 'warna' => '#10B981'],
            ['nama' => 'Bonus', 'tipe' => Kategori::TIPE_PEMASUKAN, 'icon' => '🎁', 'warna' => '#8B5CF6'],
            ['nama' => 'Investasi', 'tipe' => Kategori::TIPE_PEMASUKAN, 'icon' => '📈', 'warna' => '#3B82F6'],
            ['nama' => 'Lain-lain', 'tipe' => Kategori::TIPE_PEMASUKAN, 'icon' => '💵', 'warna' => '#6B7280'],

            // Pengeluaran categories
            ['nama' => 'Makan & Minum', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '🍽️', 'warna' => '#EF4444'],
            ['nama' => 'Transportasi', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '🚗', 'warna' => '#F59E0B'],
            ['nama' => 'Belanja', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '🛍️', 'warna' => '#EC4899'],
            ['nama' => 'Tagihan', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '📄', 'warna' => '#6366F1'],
            ['nama' => 'Hiburan', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '🎮', 'warna' => '#8B5CF6'],
            ['nama' => 'Kesehatan', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '🏥', 'warna' => '#14B8A6'],
            ['nama' => 'Lain-lain', 'tipe' => Kategori::TIPE_PENGELUARAN, 'icon' => '💸', 'warna' => '#6B7280'],
        ];

        foreach ($defaultCategories as $categoryData) {
            $categoryData['user_id'] = $user->id;
            Kategori::create($categoryData);
        }
    }
}
