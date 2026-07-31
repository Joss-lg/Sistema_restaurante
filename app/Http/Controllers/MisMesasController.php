<?php

namespace App\Http\Controllers;

use App\Models\FlujoCaja;
use App\Models\Orden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * "Mis mesas": lo que el mesero atendio HOY.
 *
 * Se separa en tres grupos porque son tres preguntas distintas que se hace
 * durante el turno:
 *   - Atendiendo: que tengo abierto ahora mismo (lo que falta por cobrar)
 *   - Cobradas:   que ya se pago (para cuadrar propinas al final)
 *   - Canceladas: que se cerro sin cobrar (para saber por que)
 *
 * SIEMPRE se filtra por el mesero en sesion. Un mesero no debe poder ver las
 * cuentas de otro desde aqui: para eso esta el desglose de Finanzas, que si
 * pide permisos.
 */
class MisMesasController extends Controller
{
    /**
     * Resumen del dia del mesero en sesion.
     */
    public function index(Request $request): JsonResponse
    {
        $meseroId = auth()->id();
        $hoy = now()->toDateString();

        // Se usa la fecha de APERTURA y no la de cobro: para el mesero, una
        // mesa que abrio en la noche sigue siendo "suya" aunque se cobre
        // pasada la medianoche.
        $ordenes = Orden::with(['mesa'])
            ->where('mesero_id', $meseroId)
            ->whereDate('abierta_el', $hoy)
            ->orderByDesc('abierta_el')
            ->get();

        $atendiendo = collect();
        $cobradas   = collect();
        $canceladas = collect();

        foreach ($ordenes as $orden) {
            $fila = [
                'orden_id'  => $orden->id,
                'numero'    => $orden->numero_orden,
                'mesa'      => optional($orden->mesa)->numero ?? 'Sin mesa',
                'personas'  => $orden->personas,
                'abierta'   => optional($orden->abierta_el)->format('H:i'),
                'cerrada'   => optional($orden->cerrada_el)->format('H:i'),
                'estado'    => $orden->estado,
                'total'     => 0.0,
            ];

            if ($orden->estado === Orden::ESTADO_CANCELADA) {
                $fila['total']  = (float) ($orden->monto_cancelado ?? 0);
                $fila['motivo'] = $orden->cancelada_motivo;
                $canceladas->push($fila);
                continue;
            }

            if ($orden->estado === Orden::ESTADO_PAGADA) {
                // Lo realmente cobrado sale de flujo_caja, no del campo total
                // de la orden: ahi ya viene con IVA, propina y descuentos, y
                // repartido por metodo de pago.
                $cobros = FlujoCaja::where('flujoable_id', $orden->id)
                    ->where('flujoable_type', Orden::class)
                    ->where('tipo', 'ingreso')
                    ->where('categoria', 'Ventas')
                    ->get();

                $fila['total']    = round((float) $cobros->sum('monto'), 2);
                $fila['efectivo'] = round((float) $cobros->filter(fn($c) => strtolower($c->metodo_pago) === 'efectivo')->sum('monto'), 2);
                $fila['tarjeta']  = round((float) $cobros->filter(fn($c) => strtolower($c->metodo_pago) === 'tarjeta')->sum('monto'), 2);
                $fila['transferencia'] = round((float) $cobros->filter(fn($c) => strtolower($c->metodo_pago) === 'transferencia')->sum('monto'), 2);

                $cobradas->push($fila);
                continue;
            }

            // Abierta: aun no hay cobro, asi que el monto se calcula del
            // consumo capturado hasta el momento.
            $fila['total'] = round((float) $orden->detalles()
                ->where('estado', '<>', 'cancelado')
                ->sum(DB::raw('cantidad * precio_unitario')), 2);

            $atendiendo->push($fila);
        }

        return response()->json([
            'success' => true,
            'fecha'   => now()->format('d/m/Y'),
            'grupos'  => [
                'atendiendo' => $atendiendo->values(),
                'cobradas'   => $cobradas->values(),
                'canceladas' => $canceladas->values(),
            ],
            'totales' => [
                'mesas_atendidas' => $ordenes->count(),
                'abiertas'        => $atendiendo->count(),
                'cobradas'        => $cobradas->count(),
                'vendido'         => round((float) $cobradas->sum('total'), 2),
                'por_cobrar'      => round((float) $atendiendo->sum('total'), 2),
            ],
        ]);
    }

    /**
     * Consumo de una de SUS mesas.
     */
    public function detalle($ordenId): JsonResponse
    {
        $orden = Orden::with(['mesa', 'detalles.producto'])->findOrFail($ordenId);

        // Candado: aunque la ruta ya exige sesion, se verifica que la orden
        // sea del mesero. Sin esto, cambiar el id en la URL dejaria ver el
        // consumo de la mesa de un companero.
        if ((int) $orden->mesero_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cuenta no es tuya.',
            ], 403);
        }

        $productos = $orden->detalles->map(function ($d) {
            $cancelado = strtolower($d->estado ?? '') === 'cancelado';

            return [
                'producto'  => optional($d->producto)->nombre ?? 'Producto eliminado',
                'cantidad'  => (float) $d->cantidad,
                'precio'    => round((float) $d->precio_unitario, 2),
                'importe'   => $cancelado ? 0 : round($d->cantidad * $d->precio_unitario, 2),
                'cancelado' => $cancelado,
                'notas'     => $d->notas,
            ];
        });

        return response()->json([
            'success'   => true,
            'mesa'      => optional($orden->mesa)->numero ?? 'Sin mesa',
            'numero'    => $orden->numero_orden,
            'estado'    => $orden->estado,
            'personas'  => $orden->personas,
            'abierta'   => optional($orden->abierta_el)->format('d/m/Y H:i'),
            'cerrada'   => optional($orden->cerrada_el)->format('H:i'),
            'productos' => $productos->values(),
            'consumo'   => round($productos->sum('importe'), 2),
            'propina'   => round((float) ($orden->propina ?? 0), 2),
        ]);
    }
}