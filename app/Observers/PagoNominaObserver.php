<?php

namespace App\Observers;

use App\Models\PagoNomina;
use App\Models\FlujoCaja;
use App\Models\CajaMovimiento;

class PagoNominaObserver
{
    /**
     * Handle the PagoNomina "created" event.
     */
    public function created(PagoNomina $pagoNomina): void
    {
        // Si ya se crea directamente como 'pagado', registrar el egreso en flujo_caja
        if ($pagoNomina->estado === 'pagado') {
            $this->crearFlujoCaja($pagoNomina);
        }
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
            $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

            FlujoCaja::create([
                'caja_movimiento_id' => $cajaActiva->id ?? null,
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
