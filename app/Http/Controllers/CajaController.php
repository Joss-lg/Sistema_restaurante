<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\CajaMovimiento;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto'));
    }

    public function cobrar($id)
    {
        $mesa = Mesa::findOrFail($id);
        
        // Usamos el servicio para obtener el desglose limpio
        $desglose = $this->cajaService->obtenerDesgloseMesa($mesa);
        $ordenes = $desglose['ordenes'];

        if ($ordenes->isEmpty()) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA) {
                $this->cajaService->liberarMesa($mesa);
            }
            return redirect()->route('admin.caja.index')->with('error', 'La mesa no tiene órdenes activas.');
        }

        // Aquí mantienes tu lógica de cuentas divididas o normal
        // Ya tienes disponibles las variables en $desglose (subtotal, iva, propina, total)
        return view('admin.caja.cobrar', [
            'mesa' => $mesa,
            'ordenes' => $ordenes,
            'subtotal' => $desglose['subtotal'],
            'iva' => $desglose['iva'],
            'propina' => $desglose['propina'],
            'totalPagar' => $desglose['total']
        ]);
    }

    public function pagar(Request $request): JsonResponse
    {
        // ... (Tu validación y lógica de transacción)
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

    // ... (Métodos adicionales como getEstadisticas, getMovimientos, store que ya tenías)
}