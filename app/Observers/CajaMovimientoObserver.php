<?php

namespace App\Observers;

use App\Models\CajaMovimiento;
use App\Models\FlujoCaja;

class CajaMovimientoObserver
{
    /**
     * Handle the CajaMovimiento "created" event.
     */
    public function created(CajaMovimiento $cajaMovimiento): void
    {
        // Solo registrar si es un Ingreso completado
        if ($cajaMovimiento->tipo === 'Ingreso' && $cajaMovimiento->estado === 'Completado') {
            FlujoCaja::create([
                'tipo' => 'ingreso',
                'categoria' => 'Venta',
                'concepto' => $cajaMovimiento->concepto,
                'monto' => $cajaMovimiento->monto,
                'metodo_pago' => $cajaMovimiento->metodo_pago ?? 'No especificado',
                'fecha' => $cajaMovimiento->created_at,
                'flujoable_id' => $cajaMovimiento->id,
                'flujoable_type' => CajaMovimiento::class,
                'observaciones' => $cajaMovimiento->comentarios,
            ]);
        }
    }

    /**
     * Handle the CajaMovimiento "updated" event.
     */
    public function updated(CajaMovimiento $cajaMovimiento): void
    {
        // Si fue actualizado a Completado, registrar en FlujoCaja
        if ($cajaMovimiento->estado === 'Completado' && $cajaMovimiento->getOriginal('estado') !== 'Completado') {
            if ($cajaMovimiento->tipo === 'Ingreso') {
                $existente = FlujoCaja::where('flujoable_id', $cajaMovimiento->id)
                    ->where('flujoable_type', CajaMovimiento::class)
                    ->first();

                if (!$existente) {
                    FlujoCaja::create([
                        'tipo' => 'ingreso',
                        'categoria' => 'Venta',
                        'concepto' => $cajaMovimiento->concepto,
                        'monto' => $cajaMovimiento->monto,
                        'metodo_pago' => $cajaMovimiento->metodo_pago ?? 'No especificado',
                        'fecha' => $cajaMovimiento->updated_at,
                        'flujoable_id' => $cajaMovimiento->id,
                        'flujoable_type' => CajaMovimiento::class,
                        'observaciones' => $cajaMovimiento->comentarios,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the CajaMovimiento "deleted" event.
     */
    public function deleted(CajaMovimiento $cajaMovimiento): void
    {
        // Soft delete del registro en FlujoCaja asociado
        FlujoCaja::where('flujoable_id', $cajaMovimiento->id)
            ->where('flujoable_type', CajaMovimiento::class)
            ->delete();
    }

    /**
     * Handle the CajaMovimiento "restored" event.
     */
    public function restored(CajaMovimiento $cajaMovimiento): void
    {
        // Restaurar el registro en FlujoCaja
        FlujoCaja::where('flujoable_id', $cajaMovimiento->id)
            ->where('flujoable_type', CajaMovimiento::class)
            ->restore();
    }
}
