<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\CajaMovimiento;
use App\Models\FlujoCaja; // Añadido para los registros individuales de venta
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

       return view('admin.cobrar.index', [
            'mesa' => $mesa,
            'ordenes' => $ordenes,
            'subtotal' => $desglose['subtotal'],
            'iva' => $desglose['iva'],
            'propina' => $desglose['propina'],
            'totalPagar' => $desglose['total'],
            'cuentasDivididas' => $desglose['cuentasDivididas'],
            'totalCuentasDivision' => $desglose['totalCuentasDivision']
        ]);
    }

    public function pagar(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $mesa = Mesa::where('id', $request->mesa_id)->lockForUpdate()->firstOrFail();
                // ... aplicar pago
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
            'mesa_id'      => 'required|exists:mesas,id',
            'monto_pagado' => 'required|numeric|min:0',
            'metodo_pago'  => 'required|string',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $mesa = Mesa::findOrFail($request->mesa_id);
                
                // Buscamos la sesión activa obligatoria para incrustar el cobro
                $cajaActiva = CajaMovimiento::where('estado', 'abierta')->firstOrFail();
                
                // 1. Cambiado quirúrgicamente: Ahora impacta en la tabla de detalle 'flujo_caja'
                FlujoCaja::create([
                    'caja_movimiento_id' => $cajaActiva->id, // Amarrado al turno actual
                    'tipo'               => 'ingreso',
                    'categoria'          => 'Ventas',
                    'concepto'           => 'Pago Mesa #' . $mesa->numero,
                    'monto'              => $request->monto_pagado,
                    'metodo_pago'        => $request->metodo_pago,
                    'fecha'              => Carbon::now(),
                ]);

                // 2. Liberar mesa (usando tu servicio existente)
                $this->cajaService->liberarMesa($mesa);

                return response()->json([
                    'success' => true, 
                    'message' => 'Pago procesado y mesa liberada con éxito.'
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
}