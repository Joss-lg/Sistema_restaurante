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
        // Optimizamos cargando 'detalles' desde el inicio para evitar el problema N+1
        $ordenesActivas = $mesa->ordenesActivas()->with('detalles')->get();
        
        $subtotal = $ordenesActivas->sum(function ($orden) {
            return $orden->detalles->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
        });

        $propina = $ordenesActivas->sum('propina');
        
        // NOTA: Cambia esto si tus precios ya incluyen IVA en la base de datos.
        // Si ya incluyen IVA: $iva = round($subtotal - ($subtotal / 1.16), 2);
        $iva = round($subtotal * 0.16, 2); 
        $total = round($subtotal + $iva + $propina, 2);

        return [
            'subtotal'             => round($subtotal, 2),
            'iva'                  => $iva,
            'propina'              => round($propina, 2),
            'total'                => $total,
            'ordenes'              => $ordenesActivas,
            'cuentasDivididas'     => $mesa->cuenta_dividida ?? false,
            'totalCuentasDivision' => $mesa->numero_cuenta_division ?? 1
        ];
    }

    /**
     * Libera una mesa y limpia sus estados.
     */
    public function liberarMesa(Mesa $mesa): bool
    {
        return DB::transaction(function () use ($mesa) {
            // Pasamos a pagadas todas las órdenes que estaban en proceso en la mesa
            $mesa->ordenes()
                ->whereIn('estado', Orden::getEstadosActivos())
                ->update([
                    'estado'     => Orden::ESTADO_PAGADA,
                    'cerrada_el' => Carbon::now(),
                ]);

            // Reseteamos por completo la mesa para dejarla lista para nuevos clientes
            $mesa->update([
                'estado'        => Mesa::ESTADO_DISPONIBLE,
                'mesero_id'     => null,
                'total_consumo' => 0,
                'updated_at'    => Carbon::now(),
            ]);

            return true;
        });
    }
}