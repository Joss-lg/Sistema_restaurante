<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Mesa;
use App\Models\Orden;
use App\Models\FlujoCaja;

class TicketService
{
    /**
     * Genera los datos del ticket final de caja para una MESA completa,
     * agregando TODAS sus órdenes activas (puede haber más de una si
     * hubo varias rondas de envío a cocina que crearon órdenes separadas).
     * Esta es la misma unidad de agregación que ya usa CajaService::obtenerDesgloseMesa(),
     * así el ticket impreso siempre coincide con lo que se cobró.
     */
    public function obtenerDatosTicketPorMesa(int $mesaId): array
    {
        $mesa = Mesa::findOrFail($mesaId);

        $ordenes = $mesa->ordenesActivas()
            ->with([
                'mesero',
                'detalles.producto',
                'detalles.promocionAplicada.promocion',
            ])
            ->get();

        if ($ordenes->isEmpty()) {
            // Fallback: la mesa ya fue liberada (caso normal justo después de
            // cobrar, ya que procesarPago() libera la mesa ANTES de que el
            // frontend pida el ticket). Buscamos TODAS las órdenes que se
            // cerraron juntas en el mismo pago -comparten el mismo
            // 'cerrada_el', porque CajaService::liberarMesa() las actualiza
            // a todas de una sola vez con el mismo timestamp- en vez de
            // tomar solo la última orden (que reintroducía el mismo bug de
            // productos/total faltantes justo en el momento del cobro).
            $ultimaCerrada = Orden::where('mesa_id', $mesa->id)
                ->where('estado', Orden::ESTADO_PAGADA)
                ->latest('cerrada_el')
                ->first();

            $ordenes = $ultimaCerrada
                ? Orden::where('mesa_id', $mesa->id)
                    ->where('estado', Orden::ESTADO_PAGADA)
                    ->where('cerrada_el', $ultimaCerrada->cerrada_el)
                    ->with(['mesero', 'detalles.producto', 'detalles.promocionAplicada.promocion'])
                    ->get()
                : collect();
        }

        // --- Items: se aplanan los detalles de TODAS las órdenes de la mesa ---
        $items = $ordenes->flatMap(function ($orden) {
            return $orden->detalles->map(function ($detalle) {
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
        });

        $ordenIds = $ordenes->pluck('id');

        // --- Pagos: se buscan por TODAS las órdenes de la mesa, no solo una ---
        $pagos = FlujoCaja::whereIn('flujoable_id', $ordenIds)
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
        $baseImponible  = $subtotalBruto - $descuentoTotal;

        // --- IVA: unificado con la MISMA fuente de verdad que usa CajaService
        // y ComandaController (Configuracion), en vez de session(), para que
        // el ticket de caja siempre coincida con el desglose que ya se cobró.
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();
        $iva = $ivaHabilitado ? round($baseImponible * ($ivaPorcentaje / 100), 2) : 0;

        // Propina: se suma la de TODAS las órdenes de la mesa. En la
        // práctica solo una tendrá valor > 0 (actualizarPropina() la
        // concentra en una sola orden y resetea las demás a 0), pero
        // sumar todas es seguro y no depende de ese detalle interno.
        $propina = $ordenes->sum(fn ($orden) => $orden->propina ?? 0);

        $totalCalculado = $baseImponible + $iva + $propina;

        $primeraOrden = $ordenes->first();
        $numeroFolio  = $ordenIds->isNotEmpty()
            ? $ordenIds->map(fn ($id) => str_pad($id, 4, '0', STR_PAD_LEFT))->implode('/')
            : '0000';

        return [
            'folio'          => 'M' . ($mesa->numero ?? '') . '-' . $numeroFolio,
            // AJUSTE: se quita la hora, solo queda la fecha.
            'fecha'          => optional($primeraOrden?->cerrada_el)->format('d/m/Y') ?? now()->format('d/m/Y'),
            'mesa'           => $mesa->numero ?? null,
            'mesero'         => $primeraOrden?->mesero->name ?? null,
            'items'          => $items->values(),
            'subtotal'       => $subtotalBruto,
            'descuentoTotal' => $descuentoTotal,
            'iva'            => $iva,
            'ivaPorcentaje'  => $ivaPorcentaje,
            'ivaHabilitado'  => $ivaHabilitado,
            'propina'        => $propina,
            'total'          => round($totalCalculado, 2),
            'pagos'          => $pagos,
            'negocio'        => ['nombre' => 'Agostadero'],
        ];
    }
}