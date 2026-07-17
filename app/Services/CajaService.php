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
        $ordenesActivas = $mesa->ordenesActivas()
            ->with([
                'detalles.producto',
                'detalles.promocionAplicada.promocion',
                'promocionesAplicadas.promocion',
                'promocionesAplicadas.detalleOrden.producto',
            ])
            ->get();

        $subtotalBruto = $ordenesActivas->sum(function ($orden) {
            return $orden->detalles->sum(fn($detalle) => $detalle->cantidad * $detalle->precio_unitario);
        });

        $descuentoPromociones = $ordenesActivas->sum(function ($orden) {
            return $orden->promocionesAplicadas->sum('monto_descuento');
        });

        // Lista de productos con su descuento, para mostrar en la vista
        $productosConDescuento = $ordenesActivas->flatMap(function ($orden) {
            return $orden->promocionesAplicadas->map(function ($op) {
                return [
                    'producto'        => $op->detalleOrden->producto->nombre ?? 'Producto',
                    'promocion'       => $op->promocion->nombre ?? 'Promoción',
                    'monto_descuento' => (float) $op->monto_descuento,
                ];
            });
        })->values();

        $subtotal = round($subtotalBruto - $descuentoPromociones, 2);
        $propina  = $ordenesActivas->sum('propina');
        $iva      = round($subtotal * 0.16, 2);
        $total    = round($subtotal + $iva + $propina, 2);

        return [
            'subtotal'              => $subtotal,
            'subtotalBruto'         => round($subtotalBruto, 2),
            'descuentoPromociones'  => round($descuentoPromociones, 2),
            'productosConDescuento' => $productosConDescuento,
            'iva'                   => $iva,
            'propina'               => round($propina, 2),
            'total'                 => $total,
            'ordenes'               => $ordenesActivas,
            'cuentasDivididas'      => $mesa->cuenta_dividida ?? false,
            'totalCuentasDivision'  => $mesa->numero_cuenta_division ?? 1,
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