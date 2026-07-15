<?php

namespace App\Http\Controllers;

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
            'iva' => $desglose['iva'],
            'propina' => $desglose['propina'],
            'totalPagar' => $desglose['total'],
            'cuentasDivididas' => $desglose['cuentasDivididas'],
            'totalCuentasDivision' => $desglose['totalCuentasDivision']
        ]);
    }

    public function procesarPago(Request $request): JsonResponse
    {
        // 1. Validar los datos estructurados que vienen del JS
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'pagos' => 'required|array|min:1',
            'pagos.*.metodo' => 'required|string|in:efectivo,tarjeta,transferencia',
            'pagos.*.monto' => 'required|numeric|min:0',
            'pagos.*.referencia' => 'nullable|string|max:255',
        ]);

        // 2. Obtener la sesión de caja abierta actual de manera dinámica
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return response()->json([
                'success' => false, 
                'message' => 'No hay ningún turno de caja abierto en este momento.'
            ], 422);
        }

        try {
            // 3. Usar transacciones para asegurar la consistencia total de los datos
            DB::transaction(function () use ($request, $cajaActiva) {
                $mesa = Mesa::findOrFail($request->mesa_id);
                
                // Obtenemos la orden activa asociada para la relación polimórfica
                $orden = $mesa->ordenesActivas()->first(); 

                // Recorremos la lista de pagos del payload del Front-end
                foreach ($request->pagos as $pago) {
                    $monto = floatval($pago['monto']);
                    
                    // Solo registramos si el monto ingresado para este método es mayor a 0
                    if ($monto > 0) {
                        FlujoCaja::create([
                            'caja_movimiento_id' => $cajaActiva->id, 
                            'tipo'               => 'ingreso',
                            'categoria'          => 'Ventas',
                            'concepto'           => "Pago Mesa #M" . $mesa->numero, // Usamos numero de mesa si es más legible
                            'monto'              => $monto,
                            'metodo_pago'        => strtolower($pago['metodo']), // Aseguramos minúsculas ('efectivo', 'tarjeta', 'transferencia')
                            'referencia'         => !empty($pago['referencia']) ? trim($pago['referencia']) : null,
                            'fecha'              => now(),
                            
                            // GUARDADO POLIMÓRFICO: Vinculamos el flujo directamente al modelo de la Orden
                            'flujoable_id'       => $orden ? $orden->id : null,
                            'flujoable_type'     => $orden ? get_class($orden) : null,
                        ]);
                    }
                }

                // 4. Delegamos la liberación lógica de las órdenes y de la mesa a tu Service
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