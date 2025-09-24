<?php

namespace App\Filament\Widgets;

use App\Models\Akun;
use App\Services\AkunService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AkunStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $service = app(AkunService::class);
        $stats = $service->getAccountStatistics();

        return [
            Stat::make('Total Akun', $stats['total_accounts'])
                ->description('Jumlah akun yang dimiliki')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make('Total Saldo', 'Rp ' . number_format($stats['total_balance'], 0, ',', '.'))
                ->description('Jumlah seluruh saldo')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($stats['total_balance'] >= 0 ? 'success' : 'danger'),

            Stat::make('Akun Aktif', $stats['active_accounts'])
                ->description('dari ' . $stats['total_accounts'] . ' total akun')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Akun Bank', $stats['accounts_by_type'][Akun::TIPE_BANK]['count'] ?? 0)
                ->description('Rp ' . number_format($stats['accounts_by_type'][Akun::TIPE_BANK]['total_balance'] ?? 0, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info'),

            Stat::make('E-Wallet', $stats['accounts_by_type'][Akun::TIPE_E_WALLET]['count'] ?? 0)
                ->description('Rp ' . number_format($stats['accounts_by_type'][Akun::TIPE_E_WALLET]['total_balance'] ?? 0, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('warning'),

            Stat::make('Kas', $stats['accounts_by_type'][Akun::TIPE_KAS]['count'] ?? 0)
                ->description('Rp ' . number_format($stats['accounts_by_type'][Akun::TIPE_KAS]['total_balance'] ?? 0, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
