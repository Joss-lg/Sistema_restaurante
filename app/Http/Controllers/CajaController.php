<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\CajaMovimiento;
use App\Models\FlujoCaja; 
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function index()
    {
        // 1. Validamos si hay una sesión de caja activa
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        // Si no hay caja abierta, bloqueamos el panel y mostramos la vista de apertura
        if (!$cajaActiva) {
            return view('admin.caja.apertura');
        }

        $mesas = Mesa::orderBy('numero', 'asc')
            ->with(['ordenesActivas.detalles.producto'])
            ->get();

        // Limpieza de estados inconsistentes
        $mesas->each(function ($mesa) {
            $ordenesActivasCount = $mesa->ordenesActivas()->count();
            
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $ordenesActivasCount === 0) {
                $this->cajaService->liberarMesa($mesa);
                $mesa->estado = Mesa::ESTADO_DISPONIBLE;
            }
        });

        $mesasActivas = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->count();
        $totalAbierto = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->sum(fn($m) => floatval($m->total_consumo ?? 0));

        // Retornamos tu vista con tus variables originales + los datos de la sesión activa
        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto', 'cajaActiva'));
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'turno'         => 'required|in:Matutino,Vespertino',
        ]);

        $existeCaja = CajaMovimiento::where('estado', 'abierta')->exists();
        if ($existeCaja) {
            return redirect()->back()->with('error', 'Ya existe una sesión de caja abierta actualmente.');
        }

        CajaMovimiento::create([
            'user_id'       => Auth::id(),
            'turno'         => $request->turno,
            'monto_inicial' => $request->monto_inicial,
        ]);

        return redirect()->route('admin.caja.index')->with('success', '¡Caja abierta correctamente!');
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_final_real' => 'required|numeric|min:0',
            'comentarios'      => 'nullable|string|max:500'
        ]);

        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->firstOrFail();

        // Recalcular montos matemáticos basados en el flujo registrado para esta sesión
        $ventas = $cajaActiva->flujos()->porCategoria('Ventas')->sum('monto');
        $anticipos = $cajaActiva->flujos()->porCategoria('Anticipos')->sum('monto');
        $gastos = $cajaActiva->flujos()->egresos()->sum('monto');
        
        $montoEsperado = $cajaActiva->monto_inicial + $ventas + $anticipos - $gastos;
        $montoReal = $request->monto_final_real;
        $diferencia = $montoReal - $montoEsperado;

        $cajaActiva->update([
            'monto_final_esperado' => $montoEsperado,
            'monto_final_real'     => $montoReal,
            'diferencia'           => $diferencia,
            'estado'               => 'cerrada',
            'comentarios'          => $request->comentarios
        ]);

        return redirect()->route('admin.caja.index')->with('success', 'La caja ha sido cerrada y el corte se generó con éxito.');
    }

    public function cobrar($id)
    {
        // Bloqueo preventivo en cobros si no hay caja activa
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->exists();
        if (!$cajaActiva) {
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

        $orden = $ordenes->first();
        $orden->load('mesero');

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

    // ==========================================================================
    // MÉTODO OPTIMIZADO: FLUJO DE CAJA CON DESGLOSE POR MÉTODO
    // ==========================================================================
    public function flujoDeCaja()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return view('admin.caja.apertura');
        }

        // 1. Gran total de todas las ventas de la sesión
        $totalVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                ->where('tipo', 'ingreso')
                                ->where('categoria', 'Ventas')
                                ->sum('monto');

        // 2. DESGLOSE INDIVIDUAL POR MÉTODO DE PAGO (Aquí estaba el error)
        $ventasEfectivo = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                    ->where('tipo', 'ingreso')
                                    ->where('categoria', 'Ventas')
                                    ->where('metodo_pago', 'Efectivo')
                                    ->sum('monto');

        $ventasTarjeta = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                   ->where('tipo', 'ingreso')
                                   ->where('categoria', 'Ventas')
                                   ->where('metodo_pago', 'Tarjeta')
                                   ->sum('monto');

        $ventasTransferencia = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                         ->where('tipo', 'ingreso')
                                         ->where('categoria', 'Ventas')
                                         ->where('metodo_pago', 'Transferencia')
                                         ->sum('monto');

        // 3. Obtener todos los gastos/salidas (Egresos) de este turno
        $totalGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                ->where('tipo', 'egreso')
                                ->sum('monto');

        // 4. Calcular el Saldo Actual Estimado en Caja
        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        // 5. Historiales detallados para las tablas informativas
        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                    ->where('tipo', 'ingreso')
                                    ->where('categoria', 'Ventas')
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                    ->where('tipo', 'egreso')
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // Enviamos las nuevas variables desglosadas a la vista blade
        return view('admin.caja.flujo', compact(
            'cajaActiva',
            'totalVentas',
            'ventasEfectivo',
            'ventasTarjeta',
            'ventasTransferencia',
            'totalGastos',
            'saldoEstimado',
            'historicoVentas',
            'historicoGastos'
        ));
    }

    public function pagar(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $mesa = Mesa::where('id', $request->mesa_id)->lockForUpdate()->firstOrFail();
                return response()->json(['success' => true, 'message' => 'Pago registrado.']);
            });
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

    public function procesarPago(Request $request): JsonResponse
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'pagos'   => 'required|array|min:1',
            'pagos.*.metodo' => 'required|string',
            'pagos.*.monto'  => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $mesa = Mesa::findOrFail($request->mesa_id);
                $cajaActiva = CajaMovimiento::where('estado', 'abierta')->firstOrFail();
                
                foreach ($request->pagos as $pago) {
                    $montoIndividual = floatval($pago['monto']);

                    if ($montoIndividual <= 0) {
                        continue;
                    }

                    FlujoCaja::create([
                        'caja_movimiento_id' => $cajaActiva->id,
                        'tipo'               => 'ingreso',
                        'categoria'          => 'Ventas',
                        'concepto'           => 'Pago Mesa #' . $mesa->numero,
                        'monto'              => $montoIndividual,
                        'metodo_pago'        => ucfirst($pago['metodo']), // Guarda perfectamente 'Efectivo', 'Tarjeta', etc.
                        'fecha'              => \Carbon\Carbon::now(),
                    ]);
                }

                $mesa->ordenesActivas()->update(['estado' => 'pagada']);
                $this->cajaService->liberarMesa($mesa);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago realizado con éxito',
                    'redirect_url' => url('/caja')
                ]);
        });
        } catch (\Exception $e) {
            Log::error("Error procesando pago: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarReportePdf($id)
    {
        $cajaActiva = CajaMovimiento::with('user')->findOrFail($id);

        $totalVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                ->where('tipo', 'ingreso')
                                ->where('categoria', 'Ventas')
                                ->sum('monto');

        $totalGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                ->where('tipo', 'egreso')
                                ->sum('monto');

        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                    ->where('tipo', 'ingreso')
                                    ->where('categoria', 'Ventas')
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)
                                    ->where('tipo', 'egreso')
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        $pdf = Pdf::loadView('admin.caja.reporte_pdf', compact(
            'cajaActiva',
            'totalVentas',
            'totalGastos',
            'saldoEstimado',
            'historicoVentas',
            'historicoGastos'
        ));

        return $pdf->stream('reporte-caja-turno-' . $cajaActiva->id . '.pdf');
    }
}