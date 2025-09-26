<?php

namespace App\Actions\Akun;

use App\Models\Akun;
use App\Services\AkunService;
use Illuminate\Support\Facades\Auth;

class BulkDeleteAkunAction
{
    public function __construct(
        private AkunService $akunService
    ) {}

    public function execute(array $akunIds): array
    {
        $results = [
            'deleted' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($akunIds as $akunId) {
            try {
                $akun = Akun::findOrFail($akunId);

                // Verify ownership
                if ($akun->user_id !== Auth::id()) {
                    $results['skipped']++;
                    $results['errors'][] = "Akun '{$akun->nama}': Tidak memiliki akses";
                    continue;
                }

                // Check if can be deleted
                if (!$this->akunService->canBeDeleted($akun)) {
                    $results['skipped']++;
                    $results['errors'][] = "Akun '{$akun->nama}': Masih memiliki transaksi";
                    continue;
                }

                $this->akunService->deleteAccount($akun);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['skipped']++;
                $results['errors'][] = "Akun ID {$akunId}: " . $e->getMessage();
            }
        }

        return $results;
    }
}
