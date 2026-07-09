<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Modulo;
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
         * Le da acceso total al Super Admin (ID 1) o a quien tenga el rol de 'admin'.
         */
        Gate::before(function (User $user, string $ability) {
            // Bypass para el usuario raíz
            if ($user->id === 1 || $user->rol_id === 1) {
                return true;
            }

            // Cargar la relación de rol si no está cargada
            if (!$user->relationLoaded('rol')) {
                $user->load('rol');
            }
            
            // Comparar el slug del rol
            if ($user->rol && ($user->rol->slug === 'admin' || $user->rol->slug === 'administrador')) {
                return true;
            }
        });

        /**
         * REGISTRO DINÁMICO DE GATES (PERMISOS)
         * Registra cada combinación de "Módulo.Acción" para poder usar directivas
         * como @can('Empleados.crear') directamente en las vistas Blade.
         */
        try {
            $acciones = ['mostrar', 'crear', 'editar', 'eliminar', 'gestionar'];
            
            foreach (Modulo::all() as $modulo) {
                foreach ($acciones as $accion) {
                    // Crea nombres de Gate como "Empleados.crear"
                    $gateName = $modulo->nombre . '.' . $accion;
                    
                    Gate::define($gateName, function (User $user) use ($modulo, $accion) {
                        return $user->tienePermiso($modulo->id, $accion);
                    });
                }
            }
        } catch (\Exception $e) {
            // Se silencia la excepción para que no falle al ejecutar migraciones 
            // por primera vez cuando la tabla 'modulos' aún no existe.
        }

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