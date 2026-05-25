<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// IMPORTANTE: Añadir estas dos líneas
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Transaccion;
use App\Models\Gasto;
use App\Models\PagoNomina;
use App\Models\CajaMovimiento;
use App\Observers\TransaccionObserver;
use App\Observers\GastoObserver;
use App\Observers\PagoNominaObserver;
use App\Observers\CajaMovimientoObserver;

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

        // ==========================================
        // REGISTRO DE OBSERVERS PARA FLUJO DE CAJA
        // ==========================================
        // Al crear un ingreso en caja, registra automáticamente el ingreso en flujo_caja
        CajaMovimiento::observe(CajaMovimientoObserver::class);
        
        // Al crear una transacción, registra automáticamente el ingreso en flujo_caja
        Transaccion::observe(TransaccionObserver::class);
        
        // Al crear o cambiar a pagado un gasto, registra el egreso en flujo_caja
        Gasto::observe(GastoObserver::class);
        
        // Al cambiar a pagado un pago de nómina, registra el egreso en flujo_caja
        PagoNomina::observe(PagoNominaObserver::class);
    }
}