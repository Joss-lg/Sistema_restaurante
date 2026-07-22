<?php

namespace App\Observers;

use App\Models\Gasto;
use App\Models\FlujoCaja;
use App\Models\CajaMovimiento;

class GastoObserver
{
    /**
     * Handle the Gasto "created" event.
     */
    public function created(Gasto $gasto): void
    {
        // Solo crear flujo_caja si el gasto está marcado como pagado al crearlo
        if ($gasto->estado === 'pagado') {
            $this->crearFlujoCaja($gasto);
        }
    }

    /**
     * Handle the Gasto "updated" event.
     */
    public function updated(Gasto $gasto): void
    {
        // Si cambió de pendiente a pagado, registrar en flujo_caja
        if ($gasto->wasChanged('estado') && $gasto->estado === 'pagado') {
            $this->crearFlujoCaja($gasto);
        }
    }

    /**
     * Handle the Gasto "deleted" event.
     */
    public function deleted(Gasto $gasto): void
    {
        // Eliminar el registro asociado en flujo_caja si existe
        if ($gasto->flujo) {
            $gasto->flujo()->delete();
        }
    }

    /**
     * Handle the Gasto "restored" event.
     */
    public function restored(Gasto $gasto): void
    {
        //
    }

    /**
     * Handle the Gasto "force deleted" event.
     */
    public function forceDeleted(Gasto $gasto): void
    {
        //
    }

    /**
     * Método auxiliar para crear el registro en flujo_caja
     */
    private function crearFlujoCaja(Gasto $gasto): void
    {
        // Verificar que no exista ya un registro
        if (!$gasto->flujo) {
            $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

            FlujoCaja::create([
                'caja_movimiento_id' => $cajaActiva->id ?? null,
                'tipo' => 'egreso',
                'categoria' => $gasto->categoria,
                'concepto' => $gasto->concepto,
                'monto' => $gasto->monto,
                'metodo_pago' => $gasto->metodo_pago,
                'fecha' => $gasto->fecha,
                'flujoable_id' => $gasto->id,
                'flujoable_type' => Gasto::class,
                'observaciones' => 'Gasto: ' . $gasto->documento ?? 'Sin documento',
            ]);
        }
    }
}
