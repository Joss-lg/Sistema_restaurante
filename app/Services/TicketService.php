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
            'detalles.promocionAplicada.promocion',
        ])->findOrFail($ordenId);

        $items = $orden->detalles->map(function ($detalle) {
            $descuento = $detalle->promocionAplicada?->monto_descuento ?? 0;
            $subtotalLinea = $detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario);

            return [
                'cantidad'         => $detalle->cantidad,
                'nombre'           => $detalle->producto->nombre ?? 'Producto sin registro',
                'subtotal'         => $subtotalLinea,
                'descuento'        => $descuento,
                'promocion_nombre' => $detalle->promocionAplicada?->promocion?->nombre,
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
        
        // Base sobre la cual calcular impuestos (Subtotal - Descuentos)
        $baseImponible = $subtotalBruto - $descuentoTotal;
        
        // Calcula el IVA de forma consistente (ajusta el porcentaje si manejas otro, ej: 0.16)
        $iva = $baseImponible * 0.16; 
        $propina = $orden->propina ?? 0;

        // Total calculado matemáticamente para evitar discrepancias con la BD
        $totalCalculado = $baseImponible + $iva + $propina;

        return [
            'folio'          => 'M' . ($orden->mesa->numero ?? '') . '-' . str_pad($orden->id, 4, '0', STR_PAD_LEFT),
            'fecha'          => optional($orden->cerrada_el)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'mesa'           => $orden->mesa->numero ?? null,
            'mesero'         => $orden->mesero->name ?? null,
            'items'          => $items,
            'subtotal'       => $subtotalBruto,
            'descuentoTotal' => $descuentoTotal,
            'iva'            => $iva, // <-- Asegúrate de pasarlo si lo usas en la vista del ticket
            'propina'        => $propina,
            'total'          => $totalCalculado, // Usamos el total unificado
            'pagos'          => $pagos,
            'negocio'        => ['nombre' => config('app.name', 'Mi Negocio')],
        ];
    }
}