<?php

namespace App\Http\Controllers;

use App\Models\AporteFondoPropina;
use App\Models\CajaMovimiento;
use App\Models\Configuracion;
use App\Models\FlujoCaja;
use App\Models\Orden;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Desglose de ventas por mesero y turno, y fondo de propinas de barra y cocina.
 *
 * No captura nada nuevo: todo el desglose se arma con datos que ya existen.
 * Cada cobro queda en flujo_caja con su metodo de pago y apuntando a la orden
 * (flujoable_id); la orden trae el mesero y la mesa; y el turno sale del
 * caja_movimiento al que pertenece el cobro.
 */
class MeserosFinanzasController extends Controller
{
    /**
     * Pantalla principal: un renglon por mesero y turno del dia elegido.
     */
    public function index(Request $request)
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->startOfDay()
            : Carbon::today();

        $filas = $this->desglosePorMeseroYTurno($fecha);

        // Porcentaje sugerido al aplicar el aporte. Es solo el valor por
        // defecto: en cada renglon se puede escribir otro.
        $porcentajeSugerido = (float) Configuracion::obtener('fondo_propina_porcentaje', 5);

        // Tarjeta del dinero por repartir: acumulado del DIA que se esta viendo.
        $fondoDelDia = (float) AporteFondoPropina::whereDate('fecha', $fecha->toDateString())->sum('monto');

        $ventaDelDia = $filas->sum('venta_total');
        $mesasDelDia = $filas->sum('mesas_atendidas');

        return view('admin.finanzas.meseros', compact(
            'fecha', 'filas', 'porcentajeSugerido', 'fondoDelDia', 'ventaDelDia', 'mesasDelDia'
        ));
    }

    /**
     * Detalle de las mesas que atendio un mesero en un turno concreto.
     */
    public function detalle(Request $request): JsonResponse
    {
        $request->validate([
            'caja_movimiento_id' => 'required|integer|exists:caja_movimientos,id',
            'mesero_id'          => 'required|integer|exists:users,id',
        ]);

        $mesas = DB::table('flujo_caja as f')
            ->join('ordenes as o', 'o.id', '=', 'f.flujoable_id')
            ->join('mesas as m', 'm.id', '=', 'o.mesa_id')
            ->where('f.caja_movimiento_id', $request->caja_movimiento_id)
            ->where('o.mesero_id', $request->mesero_id)
            ->where('f.tipo', 'ingreso')
            ->where('f.categoria', 'Ventas')
            ->whereNull('f.deleted_at')
            ->groupBy('o.id', 'm.numero', 'o.numero_orden', 'o.personas', 'o.cerrada_el')
            ->orderBy('o.cerrada_el')
            ->get([
                'o.id as orden_id',
                'o.numero_orden',
                'm.numero as mesa',
                'o.personas',
                'o.cerrada_el',
                DB::raw('SUM(f.monto) as total'),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'efectivo' THEN f.monto ELSE 0 END) as efectivo"),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'tarjeta' THEN f.monto ELSE 0 END) as tarjeta"),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'transferencia' THEN f.monto ELSE 0 END) as transferencia"),
            ]);

        $mesero = User::find($request->mesero_id);
        $turno  = CajaMovimiento::find($request->caja_movimiento_id);

        // --- CONSUMO DE CADA MESA ---
        // Se traen los productos de todas las ordenes del listado en UNA sola
        // consulta y luego se reparten en memoria. Hacer una consulta por mesa
        // seria el problema N+1: con 20 mesas serian 21 viajes a la base.
        //
        // Los cancelados se incluyen pero marcados, para que se vea que se
        // pidieron y se dieron de baja; no suman al importe.
        $productos = DB::table('detalles_orden as d')
            ->leftJoin('productos as p', 'p.id', '=', 'd.producto_id')
            ->whereIn('d.orden_id', $mesas->pluck('orden_id'))
            ->orderBy('d.orden_id')
            ->orderBy('d.id')
            ->get([
                'd.orden_id',
                'd.cantidad',
                'd.gramaje',
                'd.precio_unitario',
                'd.estado',
                'd.notas',
                'p.nombre as producto',
            ])
            ->map(function ($d) {
                $cancelado = strtolower($d->estado ?? '') === 'cancelado';

                return [
                    'orden_id'        => $d->orden_id,
                    'producto'        => $d->producto ?? 'Producto eliminado',
                    'cantidad'        => (float) $d->cantidad,
                    'gramaje'         => $d->gramaje,
                    'precio_unitario' => round((float) $d->precio_unitario, 2),
                    'importe'         => $cancelado ? 0 : round($d->cantidad * $d->precio_unitario, 2),
                    'cancelado'       => $cancelado,
                    'notas'           => $d->notas,
                ];
            })
            ->groupBy('orden_id');

        $mesas = $mesas->map(function ($m) use ($productos) {
            $lista = $productos->get($m->orden_id, collect())->values();

            $m->productos      = $lista;
            $m->consumo        = round($lista->sum('importe'), 2);
            $m->piezas         = $lista->where('cancelado', false)->sum('cantidad');
            $m->hay_cancelados = $lista->where('cancelado', true)->count() > 0;

            return $m;
        });

        return response()->json([
            'success' => true,
            'mesero'  => $mesero->nombre ?? $mesero->name ?? 'Sin nombre',
            'turno'   => $turno->turno ?? 'Sin turno',
            'fecha'   => optional($turno->created_at)->format('d/m/Y'),
            'mesas'   => $mesas,
            'totales' => [
                'mesas'         => $mesas->count(),
                'total'         => round($mesas->sum('total'), 2),
                'efectivo'      => round($mesas->sum('efectivo'), 2),
                'tarjeta'       => round($mesas->sum('tarjeta'), 2),
                'transferencia' => round($mesas->sum('transferencia'), 2),
            ],
        ]);
    }

    /**
     * Registra (o corrige) el aporte de un mesero al fondo de barra y cocina.
     *
     * El aporte es un porcentaje de la VENTA TOTAL del mesero en ese turno.
     * La venta base NO se toma del formulario: se recalcula aqui desde
     * flujo_caja. Si se aceptara la que manda el navegador, cualquiera
     * podria inflarla o reducirla y el fondo quedaria mal.
     *
     * Enviar 0 elimina el aporte.
     */
    public function aplicarAporte(Request $request): JsonResponse
    {
        $request->validate([
            'caja_movimiento_id' => 'required|integer|exists:caja_movimientos,id',
            'mesero_id'          => 'required|integer|exists:users,id',
            'porcentaje'         => 'required|numeric|min:0|max:100',
        ], [
            'porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',
            'porcentaje.min' => 'El porcentaje no puede ser negativo.',
        ]);

        $turno = CajaMovimiento::findOrFail($request->caja_movimiento_id);
        $porcentaje = round((float) $request->porcentaje, 2);

        $ventaBase = (float) FlujoCaja::where('caja_movimiento_id', $turno->id)
            ->where('tipo', 'ingreso')
            ->where('categoria', 'Ventas')
            ->whereIn('flujoable_id', function ($q) use ($request) {
                $q->select('id')->from('ordenes')->where('mesero_id', $request->mesero_id);
            })
            ->sum('monto');

        if ($ventaBase <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este mesero no tiene ventas registradas en ese turno.',
            ], 422);
        }

        $fecha = optional($turno->created_at)->toDateString() ?? now()->toDateString();

        if ($porcentaje == 0) {
            AporteFondoPropina::where('caja_movimiento_id', $turno->id)
                ->where('mesero_id', $request->mesero_id)
                ->delete();

            $mensaje = 'Aporte eliminado.';
            $monto = 0;
        } else {
            $monto = round($ventaBase * ($porcentaje / 100), 2);

            AporteFondoPropina::updateOrCreate(
                ['caja_movimiento_id' => $turno->id, 'mesero_id' => $request->mesero_id],
                [
                    'fecha'          => $fecha,
                    'venta_base'     => $ventaBase,
                    'porcentaje'     => $porcentaje,
                    'monto'          => $monto,
                    'registrado_por' => auth()->id(),
                ]
            );

            $mensaje = 'Aporte de $' . number_format($monto, 2) . ' registrado.';
        }

        $fondoDelDia = (float) AporteFondoPropina::whereDate('fecha', $fecha)->sum('monto');

        return response()->json([
            'success'     => true,
            'message'     => $mensaje,
            'monto'       => $monto,
            'venta_base'  => round($ventaBase, 2),
            'fondo_dia'   => $fondoDelDia,
        ]);
    }

    /**
     * Arma el desglose: un renglon por combinacion de mesero y turno.
     *
     * Se agrupa por caja_movimiento (que ES el turno) y no por la fecha de la
     * orden, para que una cuenta abierta en el matutino y cobrada ya entrado
     * el vespertino quede en el turno donde realmente entro el dinero.
     */
    private function desglosePorMeseroYTurno(Carbon $fecha)
    {
        $filas = DB::table('flujo_caja as f')
            ->join('ordenes as o', 'o.id', '=', 'f.flujoable_id')
            ->join('caja_movimientos as c', 'c.id', '=', 'f.caja_movimiento_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.mesero_id')
            ->whereDate('c.created_at', $fecha->toDateString())
            ->where('f.tipo', 'ingreso')
            ->where('f.categoria', 'Ventas')
            ->whereNull('f.deleted_at')
            ->whereNotNull('o.mesero_id')
            ->groupBy('c.id', 'c.turno', 'o.mesero_id', 'u.nombre')
            ->orderBy('c.turno')
            ->orderByDesc(DB::raw('SUM(f.monto)'))
            ->get([
                'c.id as caja_movimiento_id',
                'c.turno',
                'o.mesero_id',
                'u.nombre as mesero',
                DB::raw('COUNT(DISTINCT o.mesa_id) as mesas_atendidas'),
                DB::raw('SUM(f.monto) as venta_total'),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'efectivo' THEN f.monto ELSE 0 END) as efectivo"),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'tarjeta' THEN f.monto ELSE 0 END) as tarjeta"),
                DB::raw("SUM(CASE WHEN LOWER(f.metodo_pago) = 'transferencia' THEN f.monto ELSE 0 END) as transferencia"),
            ]);

        // Se pegan los aportes ya registrados para poder mostrarlos en su
        // renglon y dejar el campo con el porcentaje que se uso.
        $aportes = AporteFondoPropina::whereIn('caja_movimiento_id', $filas->pluck('caja_movimiento_id')->unique())
            ->get()
            ->keyBy(fn($a) => $a->caja_movimiento_id . '-' . $a->mesero_id);

        return $filas->map(function ($fila) use ($aportes) {
            $aporte = $aportes->get($fila->caja_movimiento_id . '-' . $fila->mesero_id);

            $fila->mesero            = $fila->mesero ?? 'Sin asignar';
            $fila->venta_total       = round((float) $fila->venta_total, 2);
            $fila->efectivo          = round((float) $fila->efectivo, 2);
            $fila->tarjeta           = round((float) $fila->tarjeta, 2);
            $fila->transferencia     = round((float) $fila->transferencia, 2);
            $fila->aporte_monto      = $aporte ? (float) $aporte->monto : 0.0;
            $fila->aporte_porcentaje = $aporte ? (float) $aporte->porcentaje : null;

            return $fila;
        });
    }
}