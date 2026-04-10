<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// IMPORTANTE: Añadir estas dos líneas
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * LÓGICA DE ADMINISTRADOR GLOBAL (OLLINTEM PRO)
         * El Gate::before se ejecuta antes de cualquier otra validación de permisos.
         * Si el usuario tiene el rol 'admin', le permite el paso a todo automáticamente.
         */
        Gate::before(function (User $user, string $ability) {
            if ($user->rol === 'admin') {
                return true;
            }
        });
    }
}