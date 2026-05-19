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
         * Ahora compara el slug del rol de forma dinámica.
         */
        Gate::before(function (User $user, string $ability) {
            // Cargar la relación de rol si no está cargada
            if (!$user->relationLoaded('rol')) {
                $user->load('rol');
            }
            
            // Comparar el slug del rol, no el enum estático
            if ($user->rol && ($user->rol->slug === 'admin' || $user->rol->slug === 'administrador')) {
                return true;
            }
        });
    }
}