<?php

namespace App\Observers;

use App\Models\Transaccion;
use App\Models\FlujoCaja;

class TransaccionObserver
{
    /**
     * Handle the Transaccion "created" event.
     */
    public function created(Transaccion $transaccion): void
    {
        // Registrar automáticamente un ingreso en flujo_caja
        FlujoCaja::create([
            'tipo' => 'ingreso',
            'categoria' => 'Venta',
            'concepto' => 'Pago de orden #' . $transaccion->orden->numero_orden,
            'monto' => $transaccion->monto,
            'metodo_pago' => $transaccion->metodo_pago,
            'fecha' => now(),
            'flujoable_id' => $transaccion->id,
            'flujoable_type' => Transaccion::class,
            'observaciones' => 'Transacción generada del pago de orden',
        ]);
    }

    /**
     * Handle the Transaccion "updated" event.
     */
    public function updated(Transaccion $transaccion): void
    {
        //
    }

    /**
     * Handle the Transaccion "deleted" event.
     */
    public function deleted(Transaccion $transaccion): void
    {
        // Eliminar el registro asociado en flujo_caja
        $transaccion->flujo()->delete();
    }

    /**
     * Handle the Transaccion "restored" event.
     */
    public function restored(Transaccion $transaccion): void
    {
        //
    }

    /**
     * Handle the Transaccion "force deleted" event.
     */
    public function forceDeleted(Transaccion $transaccion): void
    {
        //
    }
}
