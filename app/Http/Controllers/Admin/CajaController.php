<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Mesa; // Importamos el modelo de Mesa
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CajaController extends Controller
{
    public function index()
    {
        // Traemos TODAS las mesas con sus órdenes activas cargadas para optimizar
        $mesas = Mesa::orderBy('numero', 'asc')
            ->with(['ordenesActivas' => function ($query) {
                $query->with(['detalles.producto']);
            }])
            ->get();

        // Calculamos las estadísticas reales
        $mesasActivas = $mesas->where('estado', 'ocupada')->count();
        
        // Calculamos el dinero flotante (total de órdenes activas no pagadas)
        $totalAbierto = $mesas->where('estado', 'ocupada')
            ->sum(function ($mesa) {
                return $mesa->total_consumo;
            });

        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto'));
    }

    public function cobrar($id)
    {
        $mesa = Mesa::findOrFail($id);

        $ordenes = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        // Verificar si hay cuentas divididas
        $ordenesDivididas = $ordenes->filter(function ($ordenItem) {
            return isset($ordenItem->cuenta_dividida) && $ordenItem->cuenta_dividida;
        });
        $cuentasDivididas = $ordenesDivididas->isNotEmpty();
        $ordenDividida = $ordenesDivididas->first();
        $totalCuentasDivision = $ordenDividida->total_cuentas_division ?? null;
        if ($cuentasDivididas && empty($totalCuentasDivision)) {
            $totalCuentasDivision = $ordenesDivididas->count();
        }

        $productos = collect();
        $meseroNombre = $mesa->mesero?->nombre ?? 'Sin mesero asignado';
        $subtotal = 0;
        $iva = 0;
        $propina = 0;
        $totalPagar = 0;
        $orden = null;
        $ordenLabel = 'Orden #N/A';
        $ordenId = null;
        $cuentasDividadasInfo = [];

        if ($ordenes->isNotEmpty()) {
            if ($cuentasDivididas) {
                // Si hay cuentas divididas, procesarlas por separado
                $ordenLabel = 'Cuenta dividida en ' . $totalCuentasDivision . ' cuentas';
                
                foreach ($ordenes as $ordenItem) {
                    $ordenObj = \App\Models\Orden::find($ordenItem->id);
                    $detalles = DB::table('detalles_orden')
                        ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                        ->select(
                            'detalles_orden.id',
                            'productos.nombre as nombre',
                            'detalles_orden.cantidad',
                            'detalles_orden.precio_unitario',
                            'detalles_orden.notas'
                        )
                        ->where('detalles_orden.orden_id', $ordenItem->id)
                        ->get();

                    $subtotalCuenta = $detalles->sum(function ($item) {
                        return floatval($item->cantidad) * floatval($item->precio_unitario);
                    });
                    $subtotalCuenta = round($subtotalCuenta, 2);
                    $ivaCuenta = round($subtotalCuenta * 0.16, 2);
                    $propinaCuenta = floatval($ordenItem->propina);

                    $cuentasDividadasInfo[] = [
                        'numero_cuenta' => $ordenItem->numero_cuenta_division,
                        'orden_id' => $ordenItem->id,
                        'numero_orden' => $ordenItem->numero_orden,
                        'productos' => $detalles,
                        'subtotal' => $subtotalCuenta,
                        'iva' => $ivaCuenta,
                        'propina' => $propinaCuenta,
                        'total' => round($subtotalCuenta + $ivaCuenta + $propinaCuenta, 2)
                    ];

                    $subtotal += $subtotalCuenta;
                    $iva += $ivaCuenta;
                    $propina += $propinaCuenta;
                }

                $totalPagar = round($subtotal + $iva + $propina, 2);
            } else {
                // Comportamiento original para cuentas no divididas
                $orden = (object) $ordenes[0];
                $ordenId = $orden->id;
                $ordenLabel = $ordenes->count() === 1
                    ? 'Orden #' . $orden->numero_orden
                    : $ordenes->count() . ' órdenes activas';

                $ordenIds = $ordenes->pluck('id')->all();

                $productos = DB::table('detalles_orden')
                    ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                    ->select(
                        'detalles_orden.id',
                        'productos.nombre as nombre',
                        'detalles_orden.cantidad',
                        'detalles_orden.precio_unitario',
                        'detalles_orden.notas'
                    )
                    ->whereIn('detalles_orden.orden_id', $ordenIds)
                    ->get();

                $meseroNombre = DB::table('users')->where('id', $orden->mesero_id)->value('nombre') ?? $meseroNombre;
                $subtotal = $productos->sum(function ($item) {
                    return floatval($item->cantidad) * floatval($item->precio_unitario);
                });
                $subtotal = round($subtotal, 2);
                $propina = floatval($ordenes->sum('propina'));
                $iva = round($subtotal * 0.16, 2);
                $totalPagar = round($subtotal + $iva + $propina, 2);
            }
        }

        return view('admin.caja.cobrar', [
            'mesa' => $mesa,
            'orden' => $orden,
            'productos' => $productos,
            'meseroNombre' => $meseroNombre,
            'subtotal' => $subtotal,
            'iva' => $iva ?? 0,
            'propina' => $propina,
            'totalPagar' => $totalPagar,
            'ordenLabel' => $ordenLabel,
            'ordenId' => $ordenId,
            'cuentasDivididas' => $cuentasDivididas,
            'cuentasDividadasInfo' => $cuentasDividadasInfo,
            'totalCuentasDivision' => $totalCuentasDivision,
            'personasPorCuenta' => $cuentasDivididas && $totalCuentasDivision ? ceil($mesa->capacidad / max(1, $totalCuentasDivision)) : null,
        ]);
    }

    public function pagar(Request $request): JsonResponse
    {
            $validated = $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'orden_id' => 'nullable|integer|exists:ordenes,id',
                'efectivo' => 'required|numeric|min:0',
                'metodo_pago' => 'required|string|in:Efectivo,Transferencia,Tarjeta',
                'referencia' => 'nullable|string|max:191',
        ]);

        if (in_array($validated['metodo_pago'], ['Transferencia', 'Tarjeta']) && empty($validated['referencia'])) {
            $validated['referencia'] = $this->generarReferenciaAutomatica($validated['metodo_pago']);
        }

        $mesa = Mesa::findOrFail($validated['mesa_id']);
        $orden = null;
        if (! empty($validated['orden_id'])) {
            $orden = Orden::find($validated['orden_id']);
            if (! $orden || $orden->mesa_id != $validated['mesa_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no válida para esta mesa.',
                ], 422);
            }
        }

        $ordenesActivas = DB::table('ordenes')
            ->where('mesa_id', $validated['mesa_id'])
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->get();

        if ($ordenesActivas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay órdenes activas para esta mesa.',
            ], 422);
        }

        $ordenIds = $ordenesActivas->pluck('id')->all();
        $detalleTotal = DB::table('detalles_orden')
            ->whereIn('orden_id', $ordenIds)
            ->sum(DB::raw('cantidad * precio_unitario'));

        $propinaTotal = floatval($ordenesActivas->sum('propina'));
        $total = round(floatval($detalleTotal) * 1.16 + $propinaTotal, 2);

        $efectivo = floatval($validated['efectivo']);
        $cambio = 0;

        if ($validated['metodo_pago'] === 'Efectivo') {
            if ($efectivo < $total) {
                return response()->json([
                    'success' => false,
                    'message' => 'El efectivo recibido es menor al total a pagar.',
                ], 422);
            }
            $cambio = round($efectivo - $total, 2);
        } else {
            if ($efectivo < $total) {
                $efectivo = $total;
            }
            $cambio = 0;
        }

        $comprobanteUrl = null;

        DB::transaction(function () use ($mesa, $validated, $total, $efectivo, $cambio) {
            DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->update([
                    'estado' => 'pagada',
                    'metodo_pago' => $validated['metodo_pago'],
                    'cerrada_el' => Carbon::now(),
                ]);

            $mesa->estado = 'disponible';
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesa->mesero_id = null;
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = 0;
            }
            $mesa->save();

            if (Schema::hasTable('caja_movimientos')) {
                $movimientoData = [
                    'concepto' => 'Pago de mesa ' . $mesa->numero,
                    'monto' => $total,
                    'tipo' => 'Ingreso',
                    'responsable' => auth()->user()->nombre ?? auth()->user()->email,
                    'comentarios' => 'Pago con ' . $validated['metodo_pago'] . ($validated['referencia'] ? '. Ref: ' . $validated['referencia'] : '') . '. Cambio: ' . number_format($cambio, 2),
                    'estado' => 'Completado',
                ];

                if (Schema::hasColumn('caja_movimientos', 'metodo_pago')) {
                    $movimientoData['metodo_pago'] = $validated['metodo_pago'];
                }
                if (Schema::hasColumn('caja_movimientos', 'referencia')) {
                    $movimientoData['referencia'] = $validated['referencia'] ?? null;
                }

                CajaMovimiento::create($movimientoData);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
            'cambio' => number_format($cambio, 2),
            'comprobante_url' => $comprobanteUrl,
            'referencia' => $validated['referencia'] ?? null,
        ]);

    }

    private function generarReferenciaAutomatica(string $metodo): string
    {
        $prefijo = $metodo === 'Transferencia' ? 'TRF' : 'TAR';
        return sprintf('%s-%s-%s', $prefijo, now()->format('YmdHis'), rand(100, 999));
    }

    public function getEstadisticas(): JsonResponse
    {
        $hoy = Carbon::today();

        $ingresosHoy = CajaMovimiento::where('tipo', 'Ingreso')
            ->whereDate('created_at', $hoy)
            ->sum('monto');

        $egresosHoy = CajaMovimiento::where('tipo', 'Egreso')
            ->whereDate('created_at', $hoy)
            ->sum('monto');

        $totalEnCaja = CajaMovimiento::selectRaw(
                "SUM(CASE WHEN tipo = 'Ingreso' THEN monto WHEN tipo = 'Egreso' THEN -monto ELSE 0 END) as total"
            )
            ->value('total') ?? 0;

        $cierresPendientes = CajaMovimiento::where('tipo', 'Cierre')
            ->where('estado', 'Pendiente')
            ->count();

        return response()->json([
            'total_en_caja' => $totalEnCaja,
            'ingresos_hoy' => $ingresosHoy,
            'egresos_hoy' => $egresosHoy,
            'cierres_pendientes' => $cierresPendientes,
        ]);
    }

    public function getMovimientos(): JsonResponse
    {
        $movimientos = CajaMovimiento::orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->map(function (CajaMovimiento $movimiento) {
                $comprobanteUrl = null;
                if ($movimiento->comprobante && Storage::disk('public')->exists($movimiento->comprobante)) {
                    $comprobanteUrl = Storage::disk('public')->url($movimiento->comprobante);
                }

                return array_merge($movimiento->toArray(), [
                    'comprobante_url' => $comprobanteUrl,
                ]);
            });

        return response()->json($movimientos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:Ingreso,Egreso,Cierre',
            'responsable' => 'required|string|max:255',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        $movimiento = CajaMovimiento::create([
            'concepto' => $validated['concepto'],
            'monto' => abs($validated['monto']),
            'tipo' => $validated['tipo'],
            'responsable' => $validated['responsable'],
            'comentarios' => $validated['comentarios'] ?? null,
            'estado' => $validated['tipo'] === 'Cierre' ? 'Pendiente' : 'Completado',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Movimiento registrado correctamente',
            'movimiento' => $movimiento,
        ], 201);
    }
}