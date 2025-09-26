<?php

namespace App\Providers;

use App\Models\Akun;
use App\Models\Kategori;
use App\Policies\AkunPolicy;
use App\Policies\KategoriPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Akun::class => AkunPolicy::class,
        Kategori::class => KategoriPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
