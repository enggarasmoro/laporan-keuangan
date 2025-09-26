<?php

namespace App\Actions\Kategori;

use App\Models\Kategori;
use App\Services\KategoriService;
use Illuminate\Support\Facades\Auth;

class ToggleKategoriStatusAction
{
    public function __construct(
        private KategoriService $kategoriService
    ) {}

    public function execute(Kategori $kategori): Kategori
    {
        // Verify ownership
        if ($kategori->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to category');
        }

        return $this->kategoriService->toggleCategoryStatus($kategori);
    }
}
