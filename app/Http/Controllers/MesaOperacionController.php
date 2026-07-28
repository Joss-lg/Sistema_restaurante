<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\CuentaDivision;
use App\Models\DetalleOrden;
use App\Models\Mesa;
use App\Models\CajaMovimiento;
use App\Models\FlujoCaja;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MesaOperacionController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function cobrar($id)
    {
        if (!CajaMovimiento::where('estado', 'abierta')->exists()) {
            return redirect()->route('admin.caja.index')->with('error', 'Debes abrir una caja antes de procesar cobros.');
        }

        $mesa = Mesa::findOrFail($id);
        $desglose = $this->cajaService->obtenerDesgloseMesa($mesa);
        $ordenes = $desglose['ordenes'];

        if ($ordenes->isEmpty()) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA) {
                $this->cajaService->liberarMesa($mesa);
            }
            return redirect()->route('admin.caja.index')->with('error', 'La mesa no tiene órdenes activas.');
        }

        $orden = $ordenes->first()->load('mesero');

        return view('admin.cobrar.index', [
            'mesa' => $mesa,
            'ordenes' => $ordenes,
            'orden' => $orden, 
            'subtotal' => $desglose['subtotal'],
            'subtotalBruto' => $desglose['subtotalBruto'],
            'descuentoPromociones' => $desglose['descuentoPromociones'],
            'productosConDescuento' => $desglose['productosConDescuento'],
            'iva' => $desglose['iva'],
            'ivaHabilitado' => $desglose['ivaHabilitado'],
            'ivaPorcentaje' => $desglose['ivaPorcentaje'],
            'propina' => $desglose['propina'],
            'totalPagar' => $desglose['total'],
            'cuentasDivididas' => $desglose['cuentasDivididas'],
            'totalCuentasDivision' => $desglose['totalCuentasDivision'],
            'division' => $desglose['division'],
            // --- NUEVO: comisión de plataforma de delivery ---
            'esDelivery' => $desglose['esDelivery'],
            'plataformaNombre' => $desglose['plataformaNombre'],
            'comisionPorcentaje' => $desglose['comisionPorcentaje'],
            'comisionMonto' => $desglose['comisionMonto'],
            'comisionIvaPorcentaje' => $desglose['comisionIvaPorcentaje'],
            'comisionIvaMonto' => $desglose['comisionIvaMonto'],
            'comisionTotal' => $desglose['comisionTotal'],
        ]);
    }

    public function procesarPago(Request $request): JsonResponse
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            // NUEVO: si la mesa está dividida, indica QUÉ parte se está cobrando.
            // Si se omite y la mesa no tiene división activa, se cobra completa (comportamiento original).
            'cuenta_division_id' => 'nullable|integer|exists:cuentas_division,id',
            'pagos' => 'required|array|min:1',
            'pagos.*.metodo' => 'required|string|in:efectivo,tarjeta,transferencia',
            'pagos.*.monto' => 'required|numeric|min:0',
            'pagos.*.referencia' => 'nullable|string|max:255',
        ]);

        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return response()->json([
                'success' => false, 
                'message' => 'No hay ningún turno de caja abierto en este momento.'
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request, $cajaActiva) {
                $mesa = Mesa::findOrFail($request->mesa_id);

                // Si viene cuenta_division_id, estamos cobrando SOLO esa parte.
                $cuentaDivision = null;
                if ($request->filled('cuenta_division_id')) {
                    $cuentaDivision = CuentaDivision::where('id', $request->cuenta_division_id)
                        ->where('mesa_id', $mesa->id)
                        ->firstOrFail();

                    if ($cuentaDivision->estado === 'pagada') {
                        throw new \Exception('Esta parte de la cuenta ya fue pagada.');
                    }
                } elseif ($mesa->cuentasDivisionPendientes()->exists()) {
                    // La mesa tiene una división activa pero se intentó cobrar
                    // todo de golpe sin indicar a quién: lo bloqueamos para no
                    // duplicar el cobro de las partes ya pagadas.
                    throw new \Exception('Esta mesa tiene la cuenta dividida. Selecciona a la persona que vas a cobrar.');
                }

                // AJUSTE: se toman TODAS las órdenes activas de la mesa, no
                // solo la primera. La propina puede vivir en cualquiera de
                // ellas (normalmente se concentra en la primera vía
                // actualizarPropina), así que sumamos por seguridad.
                $ordenesActivas = $mesa->ordenesActivas()->get();
                $orden = $ordenesActivas->first();

                // La propina "base" a prorratear es la de la parte que se está
                // cobrando (si hay división) o la de toda la mesa (pago único).
                $propinaBase = $cuentaDivision
                    ? (float) $cuentaDivision->propina
                    : $ordenesActivas->sum(fn ($o) => floatval($o->propina));

                $sumaTotal = collect($request->pagos)->sum(fn($p) => floatval($p['monto']));
                $sumaRastreable = collect($request->pagos)
                    ->whereIn('metodo', ['tarjeta', 'transferencia'])
                    ->sum(fn($p) => floatval($p['monto']));

                $propinaRastreableTotal = ($sumaTotal > 0)
                    ? round($propinaBase * ($sumaRastreable / $sumaTotal), 2)
                    : 0;

                $etiquetaPersona = $cuentaDivision
                    ? " (Persona {$cuentaDivision->numero_cuenta}/{$cuentaDivision->total_partes})"
                    : '';

                foreach ($request->pagos as $pago) {
                    $monto = floatval($pago['monto']);
                    $metodo = strtolower($pago['metodo']);
                    
                    if ($monto > 0) {
                        FlujoCaja::create([
                            'caja_movimiento_id' => $cajaActiva->id, 
                            'tipo'               => 'ingreso',
                            'categoria'          => 'Ventas',
                            'concepto'           => "Pago Mesa #M" . $mesa->numero . $etiquetaPersona,
                            'monto'              => $monto,
                            'metodo_pago'        => $metodo,
                            'referencia'         => !empty($pago['referencia']) ? trim($pago['referencia']) : null,
                            'fecha'              => now(),
                            
                            'flujoable_id'       => $orden ? $orden->id : null,
                            'flujoable_type'     => $orden ? get_class($orden) : null,
                        ]);

                        if ($orden && $orden->mesero_id && $propinaRastreableTotal > 0 && in_array($metodo, ['tarjeta', 'transferencia'])) {
                            $montoPropina = round($propinaRastreableTotal * ($monto / $sumaRastreable), 2);

                            if ($montoPropina > 0) {
                                \App\Models\PropinaMesero::create([
                                    'caja_movimiento_id' => $cajaActiva->id,
                                    'orden_id'           => $orden->id,
                                    'mesa_id'            => $mesa->id,
                                    'mesero_id'          => $orden->mesero_id,
                                    'metodo_pago'        => $metodo,
                                    'monto'              => $montoPropina,
                                ]);
                            }
                        }
                    }
                }

                if ($cuentaDivision) {
                    $cuentaDivision->update([
                        'estado'             => 'pagada',
                        'pagada_el'          => now(),
                        'caja_movimiento_id' => $cajaActiva->id,
                    ]);

                    // Solo se libera la mesa cuando TODAS las partes están pagadas.
                    if (!$mesa->cuentasDivisionPendientes()->exists()) {
                        $this->cajaService->liberarMesa($mesa);
                        return ['mesaLiberada' => true];
                    }

                    return ['mesaLiberada' => false];
                }

                $this->cajaService->liberarMesa($mesa);
                return ['mesaLiberada' => true];
            });

            return response()->json([
                'success'       => true,
                'mesa_liberada' => $resultado['mesaLiberada'],
                'message'       => $resultado['mesaLiberada']
                    ? 'El pago se procesó y registró correctamente.'
                    : 'Pago registrado. Aún quedan personas pendientes de pagar en esta mesa.',
                'redirect_url'  => $resultado['mesaLiberada'] ? route('admin.caja.index') : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando pago de la mesa #' . $request->mesa_id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage() ?: 'Hubo un problema al procesar la venta en el servidor.'
            ], 422);
        }
    }

    // ==========================================================================
    // DIVISIÓN DE CUENTA
    // ==========================================================================

    /**
     * Inicia la división de la cuenta de una mesa: 'equitativa' (partes
     * iguales) o 'por_producto' (cada quien paga lo que consumió).
     */
    public function iniciarDivision(Request $request): JsonResponse
    {
        $request->validate([
            'mesa_id'  => 'required|exists:mesas,id',
            'tipo'     => 'required|in:equitativa,por_producto',
            'personas' => 'required|integer|min:2|max:20',
        ]);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $division = $this->cajaService->iniciarDivision($mesa, $request->tipo, (int) $request->personas);

            return response()->json(['success' => true, 'division' => $division]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Asigna N unidades de un producto de la comanda a una persona de la
     * división 'por_producto'. Permite partir un mismo renglón (ej. "3
     * pizzas") entre varias personas. Lo puede hacer el mesero al enviar
     * la comanda o el cajero al momento de cobrar.
     */
    public function asignarProductoDivision(Request $request): JsonResponse
    {
        $request->validate([
            'mesa_id'       => 'required|exists:mesas,id',
            'detalle_id'    => 'required|exists:detalles_orden,id',
            'numero_cuenta' => 'required|integer|min:1',
            'cantidad'      => 'required|integer|min:0',
        ]);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $detalle = DetalleOrden::findOrFail($request->detalle_id);

            $division = $this->cajaService->asignarProductoAPersona(
                $mesa,
                $detalle,
                (int) $request->numero_cuenta,
                (int) $request->cantidad
            );

            return response()->json(['success' => true, 'division' => $division]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancela la división activa de una mesa (vuelve a cobro normal).
     */
    public function cancelarDivision(Request $request): JsonResponse
    {
        $request->validate(['mesa_id' => 'required|exists:mesas,id']);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $this->cajaService->cancelarDivision($mesa);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function liberarMesa(Request $request): JsonResponse
    {
        $validated = $request->validate(['mesa_id' => 'required|exists:mesas,id']);
        $mesa = Mesa::findOrFail($validated['mesa_id']);
        $this->cajaService->liberarMesa($mesa);
        
        return response()->json(['success' => true, 'message' => 'Mesa liberada correctamente.']);
    }

    public function getEstadoMesa(Request $request): JsonResponse
    {
        $validated = $request->validate(['mesa_id' => 'required|exists:mesas,id']);
        $mesa = Mesa::findOrFail($validated['mesa_id']);
        
        return response()->json([
            'success' => true,
            'estado' => $mesa->estado,
            'ordenes_activas' => $mesa->ordenesActivas()->count(),
            'esta_disponible' => $mesa->estado === Mesa::ESTADO_DISPONIBLE && $mesa->ordenesActivas()->count() === 0,
        ]);
    }

    public function actualizarPropina(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tipo'  => 'required|in:porcentaje,manual',
            'valor' => 'required|numeric|min:0',
        ]);

        $orden = \App\Models\Orden::findOrFail($id);
        $mesa = Mesa::findOrFail($orden->mesa_id);

        // AJUSTE: la propina debe calcularse sobre el total de TODA la mesa
        // (todas sus órdenes activas), no solo sobre los detalles de esta
        // orden individual. Usamos CajaService, la misma fuente de verdad
        // que ya usa el modal de cobro y el ticket final, para que el %
        // de propina siempre se aplique sobre la base correcta.
        $desglose = $this->cajaService->obtenerDesgloseMesa($mesa);
        $subtotal = $desglose['subtotal'];
        $iva = $desglose['iva'];
        $base = $subtotal + $iva;

        if ($request->tipo === 'porcentaje') {
            if ($request->valor > 100) {
                return response()->json(['success' => false, 'message' => 'El porcentaje no puede ser mayor a 100.'], 422);
            }
            $propina = round($base * ($request->valor / 100), 2);
        } else {
            $propina = round($request->valor, 2);
        }

        DB::transaction(function () use ($mesa, $orden, $propina) {
            // AJUSTE: la propina completa se concentra en ESTA orden, y se
            // resetea a 0 en las demás órdenes activas de la mesa. Así,
            // CajaService::obtenerDesgloseMesa (que suma la propina de TODAS
            // las órdenes activas) nunca la cuenta duplicada ni la pierde.
            $mesa->ordenesActivas()->where('id', '!=', $orden->id)->update(['propina' => 0]);
            $orden->update(['propina' => $propina]);

            // NUEVO: la propina se puede cambiar en cualquier momento,
            // incluso con la mesa ya dividida. Si hay una división activa,
            // repartimos la propina nueva entre las partes que aún no se
            // han pagado (las ya pagadas se quedan como quedaron cobradas).
            $this->cajaService->recalcularDivisionTrasPropina($mesa);
        });

        return response()->json([
            'success'  => true,
            'propina'  => $propina,
            'total'    => round($base + $propina, 2),
            // NUEVO: para que el frontend actualice los montos por persona
            // sin recargar la página cuando la mesa está dividida.
            'division' => $this->cajaService->obtenerEstadoDivision($mesa),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $mesa = Mesa::findOrFail($id);
            if ($mesa->estado !== Mesa::ESTADO_DISPONIBLE) {
                return response()->json(['success' => false, 'message' => 'Solo se pueden eliminar mesas disponibles.'], 422);
            }
            $mesa->delete();
            return response()->json(['success' => true, 'message' => 'Mesa eliminada.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}