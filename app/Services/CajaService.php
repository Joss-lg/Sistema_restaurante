<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CajaService
{
    /**
     * Calcula el desglose de una orden o grupo de órdenes de una mesa.
     */
    public function obtenerDesgloseMesa(Mesa $mesa): array
    {
        $ordenesActivas = $mesa->ordenesActivas()->get();
        
        $subtotal = $ordenesActivas->sum(function ($orden) {
            return $orden->detalles->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
        });

        $propina = $ordenesActivas->sum('propina');
        $iva = round($subtotal * 0.16, 2);
        $total = round($subtotal + $iva + $propina, 2);

        return [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'propina' => $propina,
            'total' => $total,
            'ordenes' => $ordenesActivas,
            // Agregamos estos datos para la vista de cobro
            'cuentasDivididas' => $mesa->cuenta_dividida ?? false,
            'totalCuentasDivision' => $mesa->numero_cuenta_division ?? 1
        ];
    }

    /**
     * Libera una mesa y limpia sus estados.
     */
    public function liberarMesa(Mesa $mesa): bool
    {
        return DB::transaction(function () use ($mesa) {
            $mesa->update([
                'estado' => Mesa::ESTADO_DISPONIBLE,
                'mesero_id' => null,
                'total_consumo' => 0,
                'updated_at' => Carbon::now(),
            ]);
            return true;
        });
    }
}