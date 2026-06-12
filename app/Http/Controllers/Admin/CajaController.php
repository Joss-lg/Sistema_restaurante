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

        // Verificar y corregir estados inconsistentes
        $mesas->each(function ($mesa) {
            // Si la mesa está marcada como ocupada pero no tiene órdenes activas, cambiar a disponible
            if ($mesa->estado === 'ocupada' && $mesa->ordenesActivas->isEmpty()) {
                $mesa->update([
                    'estado' => 'disponible',
                ]);
                
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    $mesa->update(['mesero_id' => null]);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    $mesa->update(['total_consumo' => 0]);
                }
                
                Log::info('Estado de mesa corregido en caja.index', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'nuevo_estado' => 'disponible',
                ]);
            }
        });

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

        // Obtener TODAS las órdenes (pagadas y activas)
        $todasLasOrdenes = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        // Detectar si hay cuentas divididas (buscando en TODAS las órdenes, incluyendo pagadas)
        $ordenesDivididas = $todasLasOrdenes->filter(function ($ordenItem) {
            return isset($ordenItem->cuenta_dividida) && $ordenItem->cuenta_dividida;
        });
        $tienesCuentasDivididas = $ordenesDivididas->isNotEmpty();

        Log::info('DEBUG cobrar() - Análisis de mesa', [
            'mesa_id' => $mesa->id,
            'mesa_numero' => $mesa->numero,
            'tienesCuentasDivididas' => $tienesCuentasDivididas,
            'total_ordenes' => $todasLasOrdenes->count(),
            'ordenes_divididas' => $ordenesDivididas->count(),
            'estados_ordenes' => $todasLasOrdenes->pluck('estado')->toArray(),
        ]);

        // Si hay cuentas divididas, solo mostrar las ACTIVAS (no pagadas)
        if ($tienesCuentasDivididas) {
            $ordenes = $ordenesDivididas->filter(function ($ordenItem) {
                return in_array($ordenItem->estado, ['pendiente', 'en proceso', 'servida']);
            });
        } else {
            $ordenes = $todasLasOrdenes->filter(function ($ordenItem) {
                return in_array($ordenItem->estado, ['pendiente', 'en proceso', 'servida']);
            });
        }

        $cuentasDivididas = $tienesCuentasDivididas;
        
        // Obtener la primera orden dividida ACTIVA si existe
        $ordenDividida = $ordenes->first();
        
        // Obtener el total de cuentas a dividir (buscar en TODAS las ordenes divididas para encontrar el máximo)
        $totalCuentasDivision = null;
        if ($cuentasDivididas) {
            $maxNumeroCuenta = $ordenesDivididas->max(function ($orden) {
                return intval($orden->numero_cuenta_division ?? 0);
            });
            
            $totalCuentasDivision = $ordenDividida?->total_cuentas_division ?? 
                                    $maxNumeroCuenta ??
                                    $ordenesDivididas->count();
            
            Log::info('DEBUG cobrar() - Cuentas Divididas', [
                'mesa_id' => $mesa->id,
                'totalCuentasDivision_FINAL' => $totalCuentasDivision,
                'ordenes_activas_divididas' => $ordenes->count(),
                'max_numero_cuenta' => $maxNumeroCuenta,
            ]);
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
                // Establece el label para cuentas divididas
                $ordenLabel = 'Dividida en ' . $totalCuentasDivision . ' personas';
                
                // Si hay cuentas divididas, necesitamos sumar TODOS los productos
                // y dividir el total entre el número de personas
                
                // Obtener TODAS las órdenes (divididas + normales) de la mesa
                $todasLasOrdenesDelMesa = DB::table('ordenes')
                    ->where('mesa_id', $mesa->id)
                    ->whereNull('deleted_at')
                    ->get();
                
                // Obtener todos los detalles de TODAS las órdenes
                $ordenIds = $todasLasOrdenesDelMesa->pluck('id')->toArray();
                $todosLosDetalles = DB::table('detalles_orden')
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

                // Calcular el total completo
                $subtotalTotal = $todosLosDetalles->sum(function ($item) {
                    return floatval($item->cantidad) * floatval($item->precio_unitario);
                });
                $subtotalTotal = round($subtotalTotal, 2);
                $ivaTotal = round($subtotalTotal * 0.16, 2);
                
                // Obtener la propina de cualquier orden dividida (deberían ser todas iguales)
                $propinaTotal = floatval($ordenDividida->propina ?? 0);
                $totalOrdenCompleta = round($subtotalTotal + $ivaTotal + $propinaTotal, 2);

                // Crear una cuenta por cada persona con el total dividido
                for ($i = 1; $i <= $totalCuentasDivision; $i++) {
                    // Buscar la orden ACTIVA para esta persona (no incluir pagadas)
                    $ordenParaEstaPersona = $ordenes->firstWhere('numero_cuenta_division', $i);
                    
                    // Solo agregar a cuentasDividadasInfo si está activa
                    if ($ordenParaEstaPersona) {
                        $cuentasDividadasInfo[] = [
                            'numero_cuenta' => $i,
                            'orden_id' => $ordenParaEstaPersona->id,
                            'numero_orden' => $ordenParaEstaPersona->numero_orden ?? 'Persona ' . $i,
                            'productos' => $todosLosDetalles,
                            'subtotal' => round($subtotalTotal / $totalCuentasDivision, 2),
                            'iva' => round($ivaTotal / $totalCuentasDivision, 2),
                            'propina' => round($propinaTotal / $totalCuentasDivision, 2),
                            'total' => round($totalOrdenCompleta / $totalCuentasDivision, 2)
                        ];
                    }
                }

                $subtotal = $subtotalTotal;
                $iva = $ivaTotal;
                $propina = $propinaTotal;

                $totalPagar = round($subtotal + $iva + $propina, 2);

                // Ordenar las cuentas divididas por su número para una presentación consistente
                usort($cuentasDividadasInfo, function($a, $b) {
                    return intval($a['numero_cuenta'] ?? 0) <=> intval($b['numero_cuenta'] ?? 0);
                });
            } else {
                // Comportamiento original para cuentas no divididas
                $orden = $ordenes->first();
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

        // Log final para depuración
        Log::info('FINAL cuentasDividadasInfo', [
            'count' => count($cuentasDividadasInfo),
            'data' => $cuentasDividadasInfo,
            'cuentasDivididas' => $cuentasDivididas,
            'totalCuentasDivision' => $totalCuentasDivision,
            'totalPagar' => $totalPagar
        ]);

        // Si no hay órdenes activas, significa que todo ya fue pagado
        if ($ordenes->isEmpty()) {
            // Verificar si la mesa sigue en estado "ocupada" y liberarla si es necesario
            if ($mesa->estado === 'ocupada') {
                DB::table('mesas')->where('id', $mesa->id)->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
                
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['mesero_id' => null]);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                }
                
                Log::info('Mesa liberada en cobrar() - todas las órdenes pagadas', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                ]);
            }
            
            return redirect()->route('admin.caja.index')->with('success', 'Mesa ' . $mesa->numero . ' ya ha sido completamente pagada y liberada.');
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
                'iva' => 'nullable|numeric|min:0',
                'propina' => 'nullable|numeric|min:0',
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
            
            Log::info('Pago de orden específica (mesa dividida)', [
                'orden_id' => $orden->id,
                'mesa_id' => $mesa->id,
                'numero_orden' => $orden->numero_orden,
                'estado_orden_actual' => $orden->estado,
            ]);
        } else {
            Log::info('Pago de mesa completa (sin orden específica)', [
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
            ]);
        }

        $ordenesActivas = DB::table('ordenes')
            ->where('mesa_id', $validated['mesa_id'])
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->get();

        if ($ordenesActivas->isEmpty()) {
            Log::warning('No hay órdenes activas para cobrar', [
                'mesa_id' => $validated['mesa_id'],
                'orden_id' => $validated['orden_id'] ?? null,
                'todas_ordenes' => DB::table('ordenes')
                    ->where('mesa_id', $validated['mesa_id'])
                    ->whereNull('deleted_at')
                    ->select('id', 'estado', 'numero_orden')
                    ->get()
                    ->toArray(),
            ]);
            
            // Si no hay órdenes activas pero existen órdenes (no pagadas o pagadas), 
            // significa que ya todo fue pagado
            $todasLasOrdenes = DB::table('ordenes')
                ->where('mesa_id', $validated['mesa_id'])
                ->whereNull('deleted_at')
                ->count();
            
            if ($todasLasOrdenes > 0) {
                // Hay órdenes pero todas están pagadas, liberar la mesa
                Log::info('Todas las órdenes ya están pagadas, liberando mesa', [
                    'mesa_id' => $validated['mesa_id'],
                    'total_ordenes' => $todasLasOrdenes,
                ]);
                
                DB::table('mesas')->where('id', $validated['mesa_id'])->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
                
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    DB::table('mesas')->where('id', $validated['mesa_id'])->update(['mesero_id' => null]);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $validated['mesa_id'])->update(['total_consumo' => 0]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Mesa completamente pagada y liberada.',
                    'mesa_liberada' => true,
                    'estado_mesa_final' => 'disponible',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No hay órdenes activas para esta mesa.',
            ], 422);
        }

        // Si se proporcionó orden_id, calcular totales sólo para esa orden
        if (! empty($validated['orden_id']) && $orden) {
            $detalleTotal = DB::table('detalles_orden')
                ->where('orden_id', $orden->id)
                ->sum(DB::raw('cantidad * precio_unitario'));

            // Usar los valores proporcionados o calcular los defaults
            $ivaTotal = !is_null($validated['iva']) ? floatval($validated['iva']) : round(floatval($detalleTotal) * 0.16, 2);
            $propinaTotal = !is_null($validated['propina']) ? floatval($validated['propina']) : floatval($orden->propina);
            $total = round(floatval($detalleTotal) + $ivaTotal + $propinaTotal, 2);
        } else {
            $ordenIds = $ordenesActivas->pluck('id')->all();
            $detalleTotal = DB::table('detalles_orden')
                ->whereIn('orden_id', $ordenIds)
                ->sum(DB::raw('cantidad * precio_unitario'));

            // Usar los valores proporcionados o calcular los defaults
            $ivaTotal = !is_null($validated['iva']) ? floatval($validated['iva']) : round(floatval($detalleTotal) * 0.16, 2);
            $propinaTotal = !is_null($validated['propina']) ? floatval($validated['propina']) : floatval($ordenesActivas->sum('propina'));
            $total = round(floatval($detalleTotal) + $ivaTotal + $propinaTotal, 2);
        }

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

        DB::transaction(function () use ($mesa, $validated, $total, $efectivo, $cambio, $orden, $ivaTotal, $propinaTotal) {
            if (! empty($orden)) {
                // Verificar si esta orden es parte de cuentas divididas
                $esOrdenDividida = $orden->cuenta_dividida ?? false;
                
                // Actualizar SOLO la orden específica como pagada
                $updated = DB::table('ordenes')
                    ->where('id', $orden->id)
                    ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                    ->update([
                        'estado' => 'pagada',
                        'metodo_pago' => $validated['metodo_pago'],
                        'propina' => $propinaTotal,
                        'cerrada_el' => Carbon::now(),
                    ]);
                
                Log::info('Orden marcada como pagada', [
                    'orden_id' => $orden->id,
                    'mesa_id' => $mesa->id,
                    'updated_rows' => $updated,
                    'propina' => $propinaTotal,
                    'metodo_pago' => $validated['metodo_pago'],
                    'es_orden_dividida' => $esOrdenDividida,
                ]);
                
                // Si es una orden dividida, también marcar TODAS las otras órdenes divididas como pagadas
                // para evitar que la mesa quede "atrapada" con órdenes fantasma
                if ($esOrdenDividida) {
                    $actualizadasDivididas = DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->where('cuenta_dividida', true)
                        ->where('id', '!=', $orden->id)
                        ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                        ->whereNull('deleted_at')
                        ->update([
                            'estado' => 'pagada',
                            'metodo_pago' => $validated['metodo_pago'],
                            'propina' => 0,  // Las órdenes divididas "fantasma" no llevan propina
                            'cerrada_el' => Carbon::now(),
                        ]);
                    
                    Log::info('Órdenes divididas adicionales marcadas como pagadas', [
                        'mesa_id' => $mesa->id,
                        'orden_pagada_id' => $orden->id,
                        'ordenes_fantasma_actualizadas' => $actualizadasDivididas,
                    ]);
                }
            } else {
                // Marcar TODAS las órdenes activas como pagadas (cuando no se especifica orden_id)
                $updated = DB::table('ordenes')
                    ->where('mesa_id', $mesa->id)
                    ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                    ->whereNull('deleted_at')
                    ->update([
                        'estado' => 'pagada',
                        'metodo_pago' => $validated['metodo_pago'],
                        'propina' => $propinaTotal,
                        'cerrada_el' => Carbon::now(),
                    ]);
                
                Log::info('Todas las órdenes de la mesa marcadas como pagadas', [
                    'mesa_id' => $mesa->id,
                    'updated_rows' => $updated,
                    'metodo_pago' => $validated['metodo_pago'],
                ]);
            }

            // Verificar si quedan órdenes activas (pendiente, en proceso, servida)
            $ordenesActivas = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->count();

            // Debug: obtener TODAS las órdenes de la mesa para ver su estado real
            $todasLasOrdenesMesa = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereNull('deleted_at')
                ->select('id', 'estado', 'numero_orden', 'cuenta_dividida')
                ->get();

            Log::info('Verificación de órdenes activas después del pago', [
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
                'ordenes_activas_restantes' => $ordenesActivas,
                'estado_mesa_actual' => $mesa->estado,
                'todas_ordenes_mesa' => $todasLasOrdenesMesa->toArray(),
            ]);

            // Si NO quedan órdenes activas, liberar la mesa
            if ($ordenesActivas === 0) {
                // Usar actualización directa en BD para asegurar que se persista
                DB::table('mesas')->where('id', $mesa->id)->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
                
                // Si existen estas columnas, también actualizar
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['mesero_id' => null]);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                }
                
                // Recargar la mesa desde BD para confirmar
                $mesaRecargada = Mesa::find($mesa->id);
                
                Log::info('Mesa liberada correctamente', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'estado_nuevo' => 'disponible',
                    'estado_bd_despues' => $mesaRecargada?->estado,
                    'mesero_id_bd' => $mesaRecargada?->mesero_id,
                ]);
            } else {
                Log::info('Mesa permanece ocupada - aún hay órdenes activas', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'ordenes_activas_restantes' => $ordenesActivas,
                    'todas_ordenes' => DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->whereNull('deleted_at')
                        ->pluck('estado', 'id')
                        ->toArray(),
                ]);
            }

            // Registrar movimiento en caja
            if (Schema::hasTable('caja_movimientos')) {
                $movimientoData = [
                    'concepto' => 'Pago de mesa ' . $mesa->numero,
                    'monto' => $total,
                    'tipo' => 'Ingreso',
                    'responsable' => auth()->user()->nombre ?? auth()->user()->email,
                    'comentarios' => 'Pago con ' . $validated['metodo_pago'] . ($validated['referencia'] ? '. Ref: ' . $validated['referencia'] : '') . '. Cambio: ' . number_format($cambio, 2) . ' | IVA: $' . number_format($ivaTotal, 2) . ' | Propina: $' . number_format($propinaTotal, 2),
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

        // Recargar mesa y verificar estado final
        $mesaFinal = Mesa::find($mesa->id);

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
            'cambio' => number_format($cambio, 2),
            'comprobante_url' => $comprobanteUrl,
            'referencia' => $validated['referencia'] ?? null,
            'mesa_liberada' => $mesaFinal->estado === 'disponible',
            'estado_mesa_final' => $mesaFinal->estado,
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

    /**
     * Abre una mesa disponible desde el panel de caja
     */
    public function abrirMesa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
            'capacidad' => 'required|integer|min:1|max:20',
            'cuenta_dividida' => 'boolean',
            'total_cuentas_division' => 'nullable|integer|min:2|max:10',
        ]);

        $mesa = Mesa::findOrFail($validated['mesa_id']);

        // Verificar que la mesa esté disponible
        if ($mesa->estado !== 'disponible') {
            return response()->json([
                'success' => false,
                'message' => 'Esta mesa no está disponible para ocupar.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($mesa, $validated) {
                // Actualizar capacidad de la mesa si se proporciona
                if ($validated['capacidad']) {
                    $mesa->update(['capacidad' => $validated['capacidad']]);
                }

                // Cambiar estado a ocupada
                $mesa->update([
                    'estado' => 'ocupada',
                    'mesero_id' => auth()->id(),
                    'updated_at' => Carbon::now(),
                ]);

                // Si se especifica cuenta dividida, crear múltiples órdenes vacías
                if ($validated['cuenta_dividida'] && $validated['total_cuentas_division']) {
                    $totalCuentas = $validated['total_cuentas_division'];
                    
                    for ($i = 1; $i <= $totalCuentas; $i++) {
                        Orden::create([
                            'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                            'mesa_id' => $mesa->id,
                            'mesero_id' => auth()->id(),
                            'estado' => 'pendiente',
                            'total' => 0,
                            'propina' => 0,
                            'abierta_el' => now(),
                            'cuenta_dividida' => true,
                            'numero_cuenta_division' => $i,
                            'total_cuentas_division' => $totalCuentas,
                        ]);
                    }
                } else {
                    // Crear una orden inicial vacía
                    Orden::create([
                        'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                        'mesa_id' => $mesa->id,
                        'mesero_id' => auth()->id(),
                        'estado' => 'pendiente',
                        'total' => 0,
                        'propina' => 0,
                        'abierta_el' => now(),
                    ]);
                }

                Log::info('Mesa abierta desde caja', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'usuario_id' => auth()->id(),
                    'cuenta_dividida' => $validated['cuenta_dividida'] ?? false,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Mesa ' . $mesa->numero . ' abierta correctamente.',
                'mesa_id' => $mesa->id,
                'redirect' => route('admin.caja.index'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al abrir mesa desde caja', [
                'mesa_id' => $validated['mesa_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al abrir la mesa: ' . $e->getMessage(),
            ], 500);
        }
    }
}