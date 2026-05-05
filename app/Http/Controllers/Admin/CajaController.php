<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Mesa; // Importamos el modelo de Mesa
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CajaController extends Controller
{
    public function index()
    {
        // Traemos TODAS las mesas de la base de datos
        $mesas = Mesa::orderBy('numero', 'asc')->get();

        // Calculamos las estadísticas reales
        $mesasActivas = $mesas->where('estado', 'ocupada')->count();
        
        // Calculamos el dinero flotante 
        // (OJO: Asegúrate de que tu tabla mesas tenga una columna 'total_consumo', 
        // o cambia este nombre por el que uses para guardar la cuenta de la mesa).
        $totalAbierto = $mesas->where('estado', 'ocupada')->sum('total_consumo');

        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto'));
    }

    public function cobrar($id)
    {
        $mesa = Mesa::findOrFail($id);

        $orden = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->first();

        $productos = collect();
        $meseroNombre = $mesa->mesero?->nombre ?? 'Sin mesero asignado';
        $subtotal = $mesa->total_consumo ?? 0;
        $propina = 0;
        $totalPagar = $subtotal;

        if ($orden) {
            $productos = DB::table('detalles_orden')
                ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                ->select(
                    'detalles_orden.id',
                    'productos.nombre as nombre',
                    'detalles_orden.cantidad',
                    'detalles_orden.precio_unitario',
                    'detalles_orden.notas'
                )
                ->where('detalles_orden.orden_id', $orden->id)
                ->get();

            $meseroNombre = DB::table('users')->where('id', $orden->mesero_id)->value('nombre') ?? $meseroNombre;
            $subtotal = floatval($orden->total);
            $propina = floatval($orden->propina ?? 0);
            $totalPagar = $subtotal + $propina;
        }

        return view('admin.caja.cobrar', [
            'mesa' => $mesa,
            'orden' => $orden,
            'productos' => $productos,
            'meseroNombre' => $meseroNombre,
            'subtotal' => $subtotal,
            'propina' => $propina,
            'totalPagar' => $totalPagar,
        ]);
    }

    public function pagar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
            'orden_id' => 'nullable|integer|exists:ordenes,id',
            'efectivo' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|max:50',
        ]);

        $mesa = Mesa::findOrFail($validated['mesa_id']);
        $orden = null;

        if (! empty($validated['orden_id'])) {
            $orden = DB::table('ordenes')->where('id', $validated['orden_id'])->first();
            if (! $orden || $orden->mesa_id != $validated['mesa_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no válida para esta mesa.',
                ], 422);
            }
        }

        $total = $orden ? floatval($orden->total) + floatval($orden->propina ?? 0) : floatval($mesa->total_consumo ?? 0);
        $efectivo = floatval($validated['efectivo']);

        if ($efectivo < $total) {
            return response()->json([
                'success' => false,
                'message' => 'El efectivo recibido es menor al total a pagar.',
            ], 422);
        }

        DB::transaction(function () use ($orden, $mesa, $validated, $total, $efectivo) {
            if ($orden) {
                DB::table('ordenes')
                    ->where('id', $orden->id)
                    ->update([
                        'estado' => 'pagada',
                        'metodo_pago' => $validated['metodo_pago'],
                        'cerrada_el' => Carbon::now(),
                    ]);
            }

            $mesa->estado = 'disponible';
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = 0;
            }
            $mesa->save();

            CajaMovimiento::create([
                'concepto' => 'Pago de mesa ' . $mesa->numero,
                'monto' => $total,
                'tipo' => 'Ingreso',
                'responsable' => auth()->user()->nombre ?? auth()->user()->email,
                'comentarios' => 'Pago con ' . $validated['metodo_pago'] . '. Cambio: ' . number_format($efectivo - $total, 2),
                'estado' => 'Completado',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
            'cambio' => number_format($efectivo - $total, 2),
        ]);
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
            ->get();

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