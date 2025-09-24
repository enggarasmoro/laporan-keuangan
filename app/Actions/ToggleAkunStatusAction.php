<?php

namespace App\Actions;

use App\Models\Akun;
use App\Services\AkunService;
use Illuminate\Support\Facades\Auth;

class ToggleAkunStatusAction
{
    public function __construct(
        private AkunService $akunService
    ) {}

    public function execute(Akun $akun): Akun
    {
        // Verify ownership
        if ($akun->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized access to account');
        }

        return $this->akunService->toggleAccountStatus($akun);
    }
}
