<?php

namespace App\Observers;

use App\Models\PagoNomina;
use App\Models\FlujoCaja;

class PagoNominaObserver
{
    /**
     * Handle the PagoNomina "created" event.
     */
    public function created(PagoNomina $pagoNomina): void
    {
        //
    }

    /**
     * Handle the PagoNomina "updated" event.
     */
    public function updated(PagoNomina $pagoNomina): void
    {
        // Si cambió de pendiente a pagado, registrar egreso en flujo_caja
        if ($pagoNomina->wasChanged('estado') && $pagoNomina->estado === 'pagado') {
            $this->crearFlujoCaja($pagoNomina);
        }
    }

    /**
     * Handle the PagoNomina "deleted" event.
     */
    public function deleted(PagoNomina $pagoNomina): void
    {
        // Eliminar el registro asociado en flujo_caja si existe
        if ($pagoNomina->flujo) {
            $pagoNomina->flujo()->delete();
        }
    }

    /**
     * Handle the PagoNomina "restored" event.
     */
    public function restored(PagoNomina $pagoNomina): void
    {
        //
    }

    /**
     * Handle the PagoNomina "force deleted" event.
     */
    public function forceDeleted(PagoNomina $pagoNomina): void
    {
        //
    }

    /**
     * Método auxiliar para crear el registro en flujo_caja
     */
    private function crearFlujoCaja(PagoNomina $pagoNomina): void
    {
        // Verificar que no exista ya un registro
        if (!$pagoNomina->flujo) {
            FlujoCaja::create([
                'tipo' => 'egreso',
                'categoria' => 'Nómina',
                'concepto' => 'Pago de nómina - ' . $pagoNomina->empleado->nombre . ' (' . $pagoNomina->periodo . ')',
                'monto' => $pagoNomina->monto_neto,
                'metodo_pago' => $pagoNomina->metodo_pago,
                'fecha' => $pagoNomina->fecha_pago ?? now(),
                'flujoable_id' => $pagoNomina->id,
                'flujoable_type' => PagoNomina::class,
                'observaciones' => 'Nómina del período: ' . $pagoNomina->periodo,
            ]);
        }
    }
}
