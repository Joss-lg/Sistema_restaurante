<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * El mapa de políticas para la aplicación.
     */
    protected $policies = [
        // Aquí irían tus políticas si las tuvieras
    ];

    /**
     * Registro de servicios de autenticación/autorización.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Esta es la "llave maestra" para tu Super Admin (ID 1)
        Gate::before(function ($user, $ability) {
            if ($user->rol_id == 1) {
                return true;
            }
        });
    }
}