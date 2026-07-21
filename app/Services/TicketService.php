<?php

namespace App\Services;

use App\Models\Orden;
use App\Models\FlujoCaja;

class TicketService
{
    public function obtenerDatosTicket(int $ordenId): array
    {
        $orden = Orden::with([
            'mesa',
            'mesero',
            'detalles.producto',
            'detalles.promocionAplicada.promocion', // <-- nuevo
        ])->findOrFail($ordenId);

        $items = $orden->detalles->map(function ($detalle) {
            $descuento = $detalle->promocionAplicada?->monto_descuento ?? 0;

            return [
                'cantidad'        => $detalle->cantidad,
                'nombre'          => $detalle->producto->nombre ?? 'Producto',
                'subtotal'        => $detalle->subtotal, // precio lleno, sin descuento
                'descuento'       => $descuento,
                'promocion_nombre'=> $detalle->promocionAplicada?->promocion?->nombre,
            ];
        });

        $pagos = FlujoCaja::where('flujoable_id', $orden->id)
            ->where('flujoable_type', Orden::class)
            ->where('categoria', 'Ventas')
            ->get()
            ->map(fn ($flujo) => [
                'metodo'     => ucfirst($flujo->metodo_pago),
                'monto'      => $flujo->monto,
                'referencia' => $flujo->referencia,
            ]);

        $subtotalBruto  = $items->sum('subtotal');
        $descuentoTotal = $items->sum('descuento');

        return [
            'folio'          => 'M' . ($orden->mesa->numero ?? '') . '-' . str_pad($orden->id, 4, '0', STR_PAD_LEFT),
            'fecha'          => optional($orden->cerrada_el)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'mesa'           => $orden->mesa->numero ?? null,
            'mesero'         => $orden->mesero->name ?? null,
            'items'          => $items,
            'subtotal'       => $subtotalBruto,
            'descuentoTotal' => $descuentoTotal, // <-- nuevo
            'propina'        => $orden->propina ?? 0,
            'total'          => $orden->total_con_impuestos,
            'pagos'          => $pagos,
            'negocio'        => ['nombre' => config('app.name', 'Mi Negocio')],
        ];
    }
}