<?php

namespace App\Actions\Kategori;

use App\Models\Kategori;
use App\Services\KategoriService;
use Illuminate\Support\Facades\Auth;

class BulkDeleteKategoriAction
{
    public function __construct(
        private KategoriService $kategoriService
    ) {}

    public function execute(array $kategoriIds): array
    {
        $results = [
            'deleted' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($kategoriIds as $kategoriId) {
            try {
                $kategori = Kategori::findOrFail($kategoriId);

                // Verify ownership
                if ($kategori->user_id !== Auth::id()) {
                    $results['skipped']++;
                    $results['errors'][] = "Kategori '{$kategori->nama}': Tidak memiliki akses";
                    continue;
                }

                // Check if can be deleted
                if (!$this->kategoriService->canBeDeleted($kategori)) {
                    $results['skipped']++;
                    $results['errors'][] = "Kategori '{$kategori->nama}': Masih memiliki transaksi";
                    continue;
                }

                $this->kategoriService->deleteCategory($kategori);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['skipped']++;
                $results['errors'][] = "Kategori ID {$kategoriId}: " . $e->getMessage();
            }
        }

        return $results;
    }
}
