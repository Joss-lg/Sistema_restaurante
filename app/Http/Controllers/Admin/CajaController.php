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
        // IMPORTANTE: Obtener SIEMPRE datos frescos de BD sin caché
        // No usamos cache para asegurar que vemos el estado actual de las mesas
        
        // Mostrar TODAS las mesas (sin filtro de mesero_id)
        $mesas = Mesa::orderBy('numero', 'asc')
            ->with(['ordenesActivas' => function ($query) {
                $query->with(['detalles.producto']);
            }])
            ->get();

        // Verificar y corregir estados inconsistentes
        $mesas->each(function ($mesa) {
            // Obtener órdenes ACTIVAS reales desde BD (sin usar relación cacheada)
            $ordenesActivasReales = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->count();

            // Si la mesa está marcada como ocupada pero NO tiene órdenes activas, libérarla
            if ($mesa->estado === 'ocupada' && $ordenesActivasReales === 0) {
                DB::table('mesas')->where('id', $mesa->id)->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
                
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                }
                
                Log::info('Estado de mesa corregido en caja.index', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'nuevo_estado' => 'disponible',
                    'ordenes_activas_restantes' => $ordenesActivasReales,
                ]);
                
                // Actualizar la mesa en memoria para que refleje el cambio
                $mesa->estado = 'disponible';
                $mesa->total_consumo = 0;
            }
        });

        // Re-obtener las mesas desde BD para asegurar que tenemos datos frescos
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

        // Obtener TODAS las órdenes (pagadas y activas)
        $todasLasOrdenes = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        // PRIMERO: Obtener TODAS las órdenes activas (sin filtrar por cuenta_dividida)
        $ordenes = $todasLasOrdenes->filter(function ($ordenItem) {
            return in_array($ordenItem->estado, ['pendiente', 'en proceso', 'servida']);
        });

        // SEGUNDO: ✅ ARREGLO - Buscar órdenes divididas en TODAS las órdenes, NO solo activas
        // Esto permite detectar cuentas divididas incluso cuando algunas órdenes ya están pagadas
        $ordenesDivididas = $todasLasOrdenes->filter(function ($ordenItem) {
            return isset($ordenItem->cuenta_dividida) && $ordenItem->cuenta_dividida;
        });
        $cuentasDivididas = $ordenesDivididas->isNotEmpty();

        Log::info('DEBUG cobrar() - Análisis de mesa', [
            'mesa_id' => $mesa->id,
            'mesa_numero' => $mesa->numero,
            'cuentasDivididas' => $cuentasDivididas,
            'total_ordenes' => $todasLasOrdenes->count(),
            'ordenes_activas' => $ordenes->count(),
            'ordenes_divididas_activas' => $ordenesDivididas->count(),
            'estados_ordenes' => $todasLasOrdenes->pluck('estado')->toArray(),
        ]);

        $ordenDividida = $ordenes->first();
        
        // ✅ IMPORTANTE: Obtener TODAS las órdenes divididas ANTES de usarlas
        // Se necesita para poder determinar cuáles aún faltan por cobrar
        $todasLasOrdenesDivididas = collect();
        if ($cuentasDivididas) {
            $todasLasOrdenesDivididas = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->where('cuenta_dividida', true)
                ->whereNull('deleted_at')
                ->get();
        }
        
        // Obtener el total de cuentas a dividir
        $totalCuentasDivision = null;
        if ($cuentasDivididas) {
            $maxNumeroCuenta = $ordenes->max(function ($orden) {
                return intval($orden->numero_cuenta_division ?? 0);
            });
            
            $totalCuentasDivision = $ordenDividida?->total_cuentas_division ?? 
                                    $maxNumeroCuenta ??
                                    $ordenesDivididas->count();
            
            Log::info('DEBUG cobrar() - Cuentas Divididas', [
                'mesa_id' => $mesa->id,
                'totalCuentasDivision_FINAL' => $totalCuentasDivision,
                'ordenes_activas' => $ordenes->count(),
                'max_numero_cuenta' => $maxNumeroCuenta,
                'todas_ordenes_divididas' => $todasLasOrdenesDivididas->count(),
            ]);
        }

        // Inicializar variables
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
        $productos = collect();  // Inicializar como colección vacía

        if ($ordenes->isNotEmpty()) {
            if ($cuentasDivididas) {
                // Establece el label para cuentas divididas
                $ordenLabel = 'Dividida en ' . $totalCuentasDivision . ' personas';
                
                // ✅ CRÍTICO: Para cuentas divididas, necesitamos el total COMPLETO de la mesa
                // incluyendo órdenes ya pagadas, para poder dividir correctamente
                
                // ✅ ARREGLO: Obtener detalles solo de la PRIMERA orden dividida
                // Las demás órdenes NO tienen detalles, pero su total se calcula dividiendo
                $primerOrdenDividida = DB::table('ordenes')
                    ->where('mesa_id', $mesa->id)
                    ->where('cuenta_dividida', true)
                    ->orderBy('numero_cuenta_division', 'asc')
                    ->first();
                
                $detallesPorPersona = collect();
                if ($primerOrdenDividida) {
                    // Obtener detalles SOLO de la primera orden
                    $detallesPorPersona = DB::table('detalles_orden')
                        ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                        ->select(
                            'detalles_orden.id',
                            'productos.nombre as nombre',
                            'detalles_orden.cantidad',
                            'detalles_orden.precio_unitario',
                            'detalles_orden.notas'
                        )
                        ->where('detalles_orden.orden_id', $primerOrdenDividida->id)
                        ->get();
                }
                
                // Asignar a $productos para que la validación funcione
                $productos = $detallesPorPersona;

                // Calcular el total completo (dividido entre personas después)
                $subtotalTotal = $detallesPorPersona->sum(function ($item) {
                    return floatval($item->cantidad) * floatval($item->precio_unitario);
                });
                $subtotalTotal = round($subtotalTotal, 2);
                
                // ✅ IMPORTANTE: El IVA se calcula sobre el subtotal COMPLETO de la mesa
                $ivaTotal = round($subtotalTotal * 0.16, 2);
                
                // Obtener la propina de cualquier orden dividida (deberían ser todas iguales)
                $propinaTotal = floatval($ordenDividida->propina ?? 0);
                
                // El total de la mesa COMPLETA (incluyendo ya pagado)
                $totalMesaCompleta = round($subtotalTotal + $ivaTotal + $propinaTotal, 2);

                Log::info('Cálculo de totales para cuentas divididas', [
                    'mesa_id' => $mesa->id,
                    'subtotalTotal' => $subtotalTotal,
                    'ivaTotal' => $ivaTotal,
                    'propinaTotal' => $propinaTotal,
                    'totalMesaCompleta' => $totalMesaCompleta,
                    'totalCuentasDivision' => $totalCuentasDivision,
                    'precio_por_persona' => round($totalMesaCompleta / $totalCuentasDivision, 2),
                ]);

                // Crear una cuenta por cada persona con el total dividido
                for ($i = 1; $i <= $totalCuentasDivision; $i++) {
                    // ✅ CRÍTICO: Buscar la orden CORRECTA para esta persona en TODAS las órdenes divididas
                    // No solo en las activas, porque la persona 1 podría ya estar pagada
                    $ordenParaEstaPersona = $todasLasOrdenesDivididas->firstWhere('numero_cuenta_division', $i);
                    
                    Log::info('Búsqueda de orden para persona', [
                        'persona' => $i,
                        'orden_encontrada' => $ordenParaEstaPersona?->id ?? null,
                        'orden_estado' => $ordenParaEstaPersona?->estado ?? null,
                        'numero_cuenta_division' => $ordenParaEstaPersona?->numero_cuenta_division ?? null,
                    ]);
                    
                    // SIEMPRE agregar la cuenta si encontramos la orden
                    if ($ordenParaEstaPersona) {
                        $cuentasDividadasInfo[] = [
                            'numero_cuenta' => $i,
                            'orden_id' => $ordenParaEstaPersona->id,
                            'numero_orden' => $ordenParaEstaPersona->numero_orden ?? 'Persona ' . $i,
                            'productos' => collect($detallesPorPersona),
                            'subtotal' => round($subtotalTotal / $totalCuentasDivision, 2),
                            'iva' => round($ivaTotal / $totalCuentasDivision, 2),
                            'propina' => round($propinaTotal / $totalCuentasDivision, 2),
                            'total' => round($totalMesaCompleta / $totalCuentasDivision, 2),
                            'estado_orden' => $ordenParaEstaPersona->estado,
                        ];
                    }
                }

                $subtotal = $subtotalTotal;
                $iva = $ivaTotal;
                $propina = $propinaTotal;
                // ✅ IMPORTANTE: El totalPagar debe ser para UNA persona, no toda la mesa
                $totalPagar = round($totalMesaCompleta / $totalCuentasDivision, 2);
                
                Log::info('✅ ASIGNACIÓN DE totalPagar PARA CUENTAS DIVIDIDAS', [
                    'totalMesaCompleta' => $totalMesaCompleta,
                    'totalCuentasDivision' => $totalCuentasDivision,
                    'totalPagar_ASIGNADO' => $totalPagar,
                ]);

        // Ordenar las cuentas divididas por su número
                usort($cuentasDividadasInfo, function($a, $b) {
                    return intval($a['numero_cuenta'] ?? 0) <=> intval($b['numero_cuenta'] ?? 0);
                });
                
                // ✅ IMPORTANTE: Asignar ordenId a la PRIMERA persona que NO está pagada
                $ordenId = null;
                foreach ($cuentasDividadasInfo as $cuenta) {
                    if ($cuenta['estado_orden'] !== 'pagada') {
                        $ordenId = $cuenta['orden_id'];
                        Log::info('Asignando ordenId a la primera persona sin pagar', [
                            'numero_cuenta' => $cuenta['numero_cuenta'],
                            'orden_id' => $ordenId,
                            'estado_orden' => $cuenta['estado_orden'],
                        ]);
                        break;
                    }
                }
            } else {
                // Comportamiento original para cuentas no divididas
                $orden = $ordenes->first();
                $ordenId = $orden->id;
                $ordenLabel = $ordenes->count() === 1
                    ? 'Orden #' . $orden->numero_orden
                    : $ordenes->count() . ' órdenes activas';

                // Obtener detalles SOLO de las órdenes ACTIVAS (no pagadas)
                $ordenIds = $ordenes->pluck('id')->toArray();

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
            'totalPagar' => $totalPagar,
            'productos_cargados' => $productos->count(),
        ]);

        // Si no hay órdenes activas, significa que todo ya fue pagado
        if ($ordenes->isEmpty()) {
            Log::warning('Mesa sin órdenes activas', [
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
                'total_ordenes_todas' => $todasLasOrdenes->count(),
                'detalles_ordenes' => $todasLasOrdenes->pluck('estado')->toArray(),
            ]);
            
            // Liberar la mesa si está ocupada
            if ($mesa->estado === 'ocupada') {
                DB::table('mesas')->where('id', $mesa->id)->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
                
                // Nota: NO borramos mesero_id para que el mesero siga viendo la mesa cuando se reabre
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                }
            }
            
            // **CAMBIO IMPORTANTE**: En lugar de redirigir, mostrar error con más información
            if ($todasLasOrdenes->isEmpty()) {
                // No hay órdenes en absoluto
                return redirect()->route('admin.caja.index')
                    ->with('error', 'Mesa #' . $mesa->numero . ' no tiene órdenes. El mesero debe enviar los pedidos primero desde la comanda.');
            } else {
                // Hay órdenes pero todas están pagadas
                return redirect()->route('admin.caja.index')
                    ->with('success', 'Mesa #' . $mesa->numero . ' ya ha sido completamente pagada y liberada.');
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
                'metodo_pago' => 'required|string|in:Efectivo,Transferencia,Tarjeta,Tarjeta Débito',
                'referencia' => 'nullable|string|max:191',
                'iva' => 'nullable|numeric|min:0',
                'propina' => 'nullable|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'promocion_id' => 'nullable|integer',
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
            
            // ✅ VALIDACIÓN CRÍTICA: Verificar si la orden ya está pagada
            if ($orden->estado === 'pagada') {
                Log::warning('Intento de cobro duplicado - orden ya pagada', [
                    'orden_id' => $orden->id,
                    'mesa_id' => $mesa->id,
                    'numero_orden' => $orden->numero_orden,
                    'estado_orden' => $orden->estado,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya fue pagada. No se puede procesar nuevamente.',
                    'mesa_liberada' => false,
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

        // Obtener TODAS las órdenes para verificar el estado general
        $todasLasOrdenes = DB::table('ordenes')
            ->where('mesa_id', $validated['mesa_id'])
            ->whereNull('deleted_at')
            ->get();

        Log::info('DEBUG pagar() - Estado de órdenes', [
            'mesa_id' => $validated['mesa_id'],
            'ordenes_activas' => $ordenesActivas->count(),
            'todas_ordenes' => $todasLasOrdenes->count(),
            'orden_id_solicitada' => $validated['orden_id'] ?? null,
            'estados_todas' => $todasLasOrdenes->pluck('estado')->toArray(),
        ]);

        // Si no hay órdenes activas Y no se especificó una orden_id
        if ($ordenesActivas->isEmpty() && empty($validated['orden_id'])) {
            // Verificar si ALL órdenes están pagadas (significa final del pago)
            $ordenesPagadas = $todasLasOrdenes->filter(function($o) { return $o->estado === 'pagada'; })->count();
            
            if ($todasLasOrdenes->count() > 0 && $ordenesPagadas === $todasLasOrdenes->count()) {
                // Todas están pagadas, liberar la mesa
                Log::info('Todas las órdenes ya están pagadas, liberando mesa', [
                    'mesa_id' => $validated['mesa_id'],
                    'total_ordenes' => $todasLasOrdenes->count(),
                ]);
                
                DB::table('mesas')->where('id', $validated['mesa_id'])->update([
                    'estado' => 'disponible',
                    'updated_at' => now(),
                ]);
                
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $validated['mesa_id'])->update(['total_consumo' => 0]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Mesa completamente pagada y liberada.',
                    'mesa_liberada' => true,
                    'estado_mesa_final' => 'disponible',
                ]);
            } else {
                // No hay órdenes para cobrar
                return response()->json([
                    'success' => false,
                    'message' => 'No hay órdenes activas para esta mesa.',
                ], 422);
            }
        }

        // Si se proporcionó orden_id, calcular totales sólo para esa orden
        if (! empty($validated['orden_id']) && $orden) {
            $detalleTotal = DB::table('detalles_orden')
                ->where('orden_id', $orden->id)
                ->sum(DB::raw('cantidad * precio_unitario'));

            // ✅ NUEVO: Si la orden es parte de una cuenta dividida, dividir el total
            $esCuentaDividida = $orden->cuenta_dividida ?? false;
            $totalCuentasDivision = $orden->total_cuentas_division ?? 1;
            
            if ($esCuentaDividida && $totalCuentasDivision > 1) {
                Log::info('Orden dividida detectada - dividiendo total', [
                    'orden_id' => $orden->id,
                    'detalleTotal_antes' => $detalleTotal,
                    'total_cuentas_division' => $totalCuentasDivision,
                ]);
                $detalleTotal = round($detalleTotal / $totalCuentasDivision, 2);
                
                Log::info('Orden dividida - total dividido', [
                    'orden_id' => $orden->id,
                    'detalleTotal_despues' => $detalleTotal,
                ]);
            }

            // Usar los valores proporcionados o calcular los defaults
            $ivaTotal = !is_null($validated['iva']) ? floatval($validated['iva']) : round(floatval($detalleTotal) * 0.16, 2);
            
            // ✅ NUEVO: Dividir propina también si es cuenta dividida
            $propinaDeLaOrden = floatval($orden->propina ?? 0);
            if ($esCuentaDividida && $totalCuentasDivision > 1) {
                $propinaDeLaOrden = round($propinaDeLaOrden / $totalCuentasDivision, 2);
            }
            
            $propinaTotal = !is_null($validated['propina']) ? floatval($validated['propina']) : $propinaDeLaOrden;
            
            // 🔴 IMPORTANTE: Incluir el descuento de la promoción
            $descuento = floatval($validated['descuento'] ?? 0);
            $total = round(floatval($detalleTotal) + $ivaTotal + $propinaTotal - $descuento, 2);
            
            Log::info('Cálculo de total con promoción (orden específica)', [
                'detalleTotal' => $detalleTotal,
                'iva' => $ivaTotal,
                'propina' => $propinaTotal,
                'descuento' => $descuento,
                'total_final' => $total,
                'es_cuenta_dividida' => $esCuentaDividida,
                'total_cuentas' => $totalCuentasDivision,
            ]);
        } else {
            $ordenIds = $ordenesActivas->pluck('id')->all();
            $detalleTotal = DB::table('detalles_orden')
                ->whereIn('orden_id', $ordenIds)
                ->sum(DB::raw('cantidad * precio_unitario'));

            // Usar los valores proporcionados o calcular los defaults
            $ivaTotal = !is_null($validated['iva']) ? floatval($validated['iva']) : round(floatval($detalleTotal) * 0.16, 2);
            $propinaTotal = !is_null($validated['propina']) ? floatval($validated['propina']) : floatval($ordenesActivas->sum('propina'));
            
            // 🔴 IMPORTANTE: Incluir el descuento de la promoción
            $descuento = floatval($validated['descuento'] ?? 0);
            $total = round(floatval($detalleTotal) + $ivaTotal + $propinaTotal - $descuento, 2);
            
            Log::info('Cálculo de total con promoción (mesa completa)', [
                'detalleTotal' => $detalleTotal,
                'iva' => $ivaTotal,
                'propina' => $propinaTotal,
                'descuento' => $descuento,
                'total_final' => $total,
            ]);
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

        try {
            DB::transaction(function () use ($mesa, $validated, $total, $efectivo, $cambio, $orden, $ivaTotal, $propinaTotal) {
                if (! empty($orden)) {
                    // Verificar si esta orden es parte de cuentas divididas
                    $esOrdenDividida = $orden->cuenta_dividida ?? false;
                    
                    // ✅ VERIFICACIÓN DOBLE: Confirmar que la orden NO está ya pagada antes de actualizar
                    $ordenEstadoActual = DB::table('ordenes')->where('id', $orden->id)->value('estado');
                    if ($ordenEstadoActual === 'pagada') {
                        throw new \Exception('Esta orden ya fue pagada. No se puede procesar duplicadamente.');
                    }
                    
                    // Actualizar SOLO la orden específica como pagada
                    $updated = DB::table('ordenes')
                        ->where('id', $orden->id)
                        ->where('estado', '!=', 'pagada')  // ✅ No actualizar si ya está pagada
                        ->whereNull('deleted_at')
                        ->update([
                            'estado' => 'pagada',
                            'metodo_pago' => $validated['metodo_pago'],
                            'propina' => $propinaTotal,
                            'cerrada_el' => now(),
                        ]);
                    
                    Log::info('Orden marcada como pagada', [
                        'orden_id' => $orden->id,
                        'mesa_id' => $mesa->id,
                        'updated_rows' => $updated,
                        'propina' => $propinaTotal,
                        'metodo_pago' => $validated['metodo_pago'],
                        'es_orden_dividida' => $esOrdenDividida,
                    ]);
                    
                    // ✅ Si updated_rows es 0, significa que la orden ya estaba pagada
                    if ($updated === 0) {
                        Log::warning('Orden ya estaba pagada - intento de cobro duplicado', [
                            'orden_id' => $orden->id,
                            'mesa_id' => $mesa->id,
                        ]);
                        throw new \Exception('Esta orden ya fue pagada. No se puede procesar nuevamente.');
                    }
                    
                    // ✅ ELIMINADO: No marcar automáticamente todas las órdenes como pagadas
                    // Solo se marca la orden actual. Las demás órdenes se marcarán cuando se paguen.
                } else {
                    // Marcar TODAS las órdenes activas como pagadas (cuando no se especifica orden_id)
                    // PRIMERO: Obtener TODAS las órdenes para marcarlas
                    $todasLasOrdenesAMarcar = DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                        ->whereNull('deleted_at')
                        ->get();
                    
                    // SEGUNDO: Marcarlas como pagadas
                    $updated = DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                        ->whereNull('deleted_at')
                        ->update([
                            'estado' => 'pagada',
                            'metodo_pago' => $validated['metodo_pago'],
                            'propina' => $propinaTotal,
                            'cerrada_el' => now(),
                        ]);
                    
                    // TERCERO: Verificar que se actualizaron correctamente
                    $ordenesNoActualizadas = DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                        ->whereNull('deleted_at')
                        ->get();
                    
                    Log::info('Todas las órdenes de la mesa marcadas como pagadas', [
                        'mesa_id' => $mesa->id,
                        'ordenes_a_marcar' => $todasLasOrdenesAMarcar->count(),
                        'updated_rows' => $updated,
                        'ordenes_no_actualizadas' => $ordenesNoActualizadas->count(),
                        'metodo_pago' => $validated['metodo_pago'],
                    ]);
                }

                // ===============================================
                // VERIFICACIÓN CRÍTICA: ¿HAY ÓRDENES ACTIVAS RESTANTES?
                // ===============================================
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

                $estadosPorTipo = [
                    'pagada' => $todasLasOrdenesMesa->where('estado', 'pagada')->count(),
                    'pendiente' => $todasLasOrdenesMesa->where('estado', 'pendiente')->count(),
                    'en proceso' => $todasLasOrdenesMesa->where('estado', 'en proceso')->count(),
                    'servida' => $todasLasOrdenesMesa->where('estado', 'servida')->count(),
                ];

                Log::info('Verificación de órdenes después del pago', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'ordenes_activas_restantes' => $ordenesActivas,
                    'total_ordenes' => $todasLasOrdenesMesa->count(),
                    'estado_por_tipo' => $estadosPorTipo,
                    'todas_ordenes_mesa' => $todasLasOrdenesMesa->toArray(),
                ]);

                // ===============================================
                // SI NO HAY ÓRDENES ACTIVAS → LIBERAR LA MESA
                // ===============================================
                if ($ordenesActivas === 0) {
                    // ACTUALIZAR: estado = disponible, mesero_id = null, total_consumo = 0
                    DB::table('mesas')->where('id', $mesa->id)->update([
                        'estado' => 'disponible',
                        'updated_at' => now(),
                    ]);
                    
                    if (Schema::hasColumn('mesas', 'mesero_id')) {
                        DB::table('mesas')->where('id', $mesa->id)->update(['mesero_id' => null]);
                    }
                    if (Schema::hasColumn('mesas', 'total_consumo')) {
                        DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                    }
                    
                    // Recargar la mesa desde BD para confirmar que está liberada
                    $mesaRecargada = Mesa::find($mesa->id);
                    
                    Log::info('✅ MESA LIBERADA COMPLETAMENTE', [
                        'mesa_id' => $mesa->id,
                        'mesa_numero' => $mesa->numero,
                        'estado_nuevo' => 'disponible',
                        'estado_bd_after' => $mesaRecargada?->estado,
                        'mesero_id_bd' => $mesaRecargada?->mesero_id,
                        'total_consumo_bd' => $mesaRecargada?->total_consumo,
                        'total_ordenes_pagadas' => $estadosPorTipo['pagada'],
                    ]);
                } else {
                    Log::info('Mesa permanece ocupada - aún hay órdenes activas', [
                        'mesa_id' => $mesa->id,
                        'mesa_numero' => $mesa->numero,
                        'ordenes_activas_restantes' => $ordenesActivas,
                        'estado_por_tipo' => $estadosPorTipo,
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
        } catch (\Exception $e) {
            Log::error('Error en transacción de pago', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'mesa_id' => $mesa->id ?? null,
                'orden_id' => $orden->id ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'mesa_liberada' => false,
            ], 422);
        }

        // ===============================================
        // RESPUESTA FINAL: Confirmar estado de la mesa
        // ===============================================
        $mesaFinal = Mesa::find($mesa->id);
        
        // ⚠️ VERIFICACIÓN CRÍTICA: Confirmar que la mesa está realmente disponible y sin órdenes activas
        $ordenesActivasFinales = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->count();

        Log::info('Respuesta final del método pagar()', [
            'mesa_id' => $mesa->id,
            'mesa_numero' => $mesaFinal->numero,
            'mesa_liberada' => $mesaFinal->estado === 'disponible',
            'estado_final_bd' => $mesaFinal->estado,
            'mesero_id_final' => $mesaFinal->mesero_id,
            'total_consumo_final' => $mesaFinal->total_consumo ?? 0,
            'ordenes_activas_finales' => $ordenesActivasFinales,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente. Mesa ' . ($mesaFinal->estado === 'disponible' ? '✅ LIBERADA.' : 'aún tiene órdenes.'),
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

    /**
     * Obtener promociones activas para mostrar en caja
     */
    public function getPromocionesActivas(): JsonResponse
    {
        try {
            $promociones = DB::table('promociones')
                ->where('esta_activa', true)
                ->select('id', 'nombre', 'descripcion', 'tipo_promocion', 'valor_descuento')
                ->get();

            return response()->json([
                'success' => true,
                'promociones' => $promociones,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener promociones activas', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar promociones',
            ], 500);
        }
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

    public function destroy($id): JsonResponse
    {
        try {
            $mesa = Mesa::findOrFail($id);

            // Validar que sea admin
            if (!auth()->user()->tienePermiso('mesas.eliminar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar mesas.',
                ], 403);
            }

            // Validar que la mesa esté disponible
            if ($mesa->estado !== 'disponible') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar mesas en estado LIBRE.',
                ], 422);
            }

            // Soft delete (no borra registros, solo marca como eliminado)
            $mesa->delete();

            Log::info('Mesa eliminada (soft delete)', [
                'mesa_id' => $id,
                'mesa_numero' => $mesa->numero,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente. Historial de órdenes preservado.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar mesa', [
                'mesa_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la mesa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liberar una mesa después del cobro
     */
    public function liberarMesa(Request $request)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
        ]);

        try {
            $mesa = Mesa::findOrFail($validated['mesa_id']);

            DB::transaction(function () use ($mesa) {
                // Actualizar mesa a disponible PERO MANTENER EL MESERO ASIGNADO
                $mesa->update([
                    'estado' => 'disponible',
                    // NO limpiar mesero_id - la mesa sigue siendo del mesero
                    'updated_at' => Carbon::now(),
                ]);

                Log::info('Mesa liberada manualmente', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'mesero_asignado' => $mesa->mesero_id,
                    'usuario_id' => auth()->id(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Mesa liberada correctamente.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al liberar mesa', [
                'mesa_id' => $validated['mesa_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al liberar la mesa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estado actual de una mesa
     */
    public function getEstadoMesa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
        ]);

        try {
            $mesa = Mesa::findOrFail($validated['mesa_id']);
            
            $ordenesActivas = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->count();

            return response()->json([
                'success' => true,
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
                'estado' => $mesa->estado,
                'ordenes_activas' => $ordenesActivas,
                'está_disponible' => $mesa->estado === 'disponible' && $ordenesActivas === 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estado de mesa: ' . $e->getMessage(),
            ], 500);
        }
    }
}