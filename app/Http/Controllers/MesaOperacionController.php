<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
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
            'totalCuentasDivision' => $desglose['totalCuentasDivision']
        ]);
    }

    public function procesarPago(Request $request): JsonResponse
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
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
            DB::transaction(function () use ($request, $cajaActiva) {
                $mesa = Mesa::findOrFail($request->mesa_id);
                
                $orden = $mesa->ordenesActivas()->first(); 

                $propinaOrden = $orden ? floatval($orden->propina) : 0;

                $sumaTotal = collect($request->pagos)->sum(fn($p) => floatval($p['monto']));
                $sumaRastreable = collect($request->pagos)
                    ->whereIn('metodo', ['tarjeta', 'transferencia'])
                    ->sum(fn($p) => floatval($p['monto']));

                $propinaRastreableTotal = ($sumaTotal > 0)
                    ? round($propinaOrden * ($sumaRastreable / $sumaTotal), 2)
                    : 0;

                foreach ($request->pagos as $pago) {
                    $monto = floatval($pago['monto']);
                    $metodo = strtolower($pago['metodo']);
                    
                    if ($monto > 0) {
                        FlujoCaja::create([
                            'caja_movimiento_id' => $cajaActiva->id, 
                            'tipo'               => 'ingreso',
                            'categoria'          => 'Ventas',
                            'concepto'           => "Pago Mesa #M" . $mesa->numero,
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

                $this->cajaService->liberarMesa($mesa);
            });

            return response()->json([
                'success' => true, 
                'message' => 'El pago se procesó y registró correctamente.',
                'redirect_url' => route('admin.caja.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando pago de la mesa #' . $request->mesa_id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => 'Hubo un problema al procesar la venta en el servidor.'
            ], 500);
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

        $orden->load(['detalles', 'promocionesAplicadas']);

        $subtotalBruto = $orden->detalles->sum(fn($d) => $d->cantidad * $d->precio_unitario);
        $descuentoPromociones = $orden->promocionesAplicadas->sum('monto_descuento');
        $subtotal = max(0, round($subtotalBruto - $descuentoPromociones, 2));

        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();
        $iva = $ivaHabilitado ? round($subtotal * ($ivaPorcentaje / 100), 2) : 0;

        $base = $subtotal + $iva;

        if ($request->tipo === 'porcentaje') {
            if ($request->valor > 100) {
                return response()->json(['success' => false, 'message' => 'El porcentaje no puede ser mayor a 100.'], 422);
            }
            $propina = round($base * ($request->valor / 100), 2);
        } else {
            $propina = round($request->valor, 2);
        }

        $orden->update(['propina' => $propina]);

        return response()->json([
            'success' => true,
            'propina' => $propina,
            'total'   => round($base + $propina, 2),
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