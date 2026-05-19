<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::all();
        return view('admin.mesas.index', compact('mesas'));
    }

    public function getMesas(): JsonResponse
    {
        $mesas = Mesa::all();
        
        $mesasConEstado = $mesas->map(function ($mesa) {
            $ordenesActivas = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->count();
            
            $ocupada = $mesa->estado === 'ocupada' || $ordenesActivas > 0;

            return [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'seccion' => $mesa->seccion,
                'ordenes_activas' => $ordenesActivas,
                'ocupada' => $ocupada,
            ];
        });

        return response()->json($mesasConEstado);
    }

    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        $nuevoEstado = $request->input('estado');
        
        $mesa->update(['estado' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la mesa actualizado',
            'mesa' => $mesa
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero,'.$mesa->id,
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|string|in:libre,ocupada,reservada',
        ]);

        $mesa->update([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
            'estado' => $validated['estado'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:libre,ocupada,reservada',
            'posicion_x' => 'nullable|integer',
            'posicion_y' => 'nullable|integer',
        ]);

        $mesa = Mesa::create([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
            'estado' => $validated['estado'] ?? 'libre',
            'posicion_x' => $validated['posicion_x'] ?? null,
            'posicion_y' => $validated['posicion_y'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function updatePosicion(Request $request, $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'posicion_x' => 'required|integer',
            'posicion_y' => 'required|integer',
        ]);

        $mesa->update([
            'posicion_x' => $validated['posicion_x'],
            'posicion_y' => $validated['posicion_y'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Posición de la mesa guardada',
            'mesa' => $mesa
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);

        try {
            $mesa->delete();
            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la mesa. ' . $e->getMessage()
            ], 500);
        }
    }

    /* --- NUEVO MÉTODO PARA LA CAJA (OLLINTEM PRO) --- */
    public function cobrar($id)
    {
        $mesa = Mesa::findOrFail($id);

        // Buscamos la orden activa
        $orden = DB::table('ordenes')
            ->where('mesa_id', $id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->first();

        if (!$orden) {
            return redirect()->back()->with('error', 'No hay una orden activa para esta mesa.');
        }

        // Lógica de cuenta dividida (verifica que estos nombres existan en tu DB)
        $cuentasDivididas = isset($orden->cuenta_dividida) ? $orden->cuenta_dividida : false;
        $totalCuentasDivision = isset($orden->numero_cuenta_division) ? $orden->numero_cuenta_division : 1;

        // Totales base
        $subtotal = $orden->total ?? 0;
        $iva = $subtotal * 0.16;
        $propina = 0; 
        $totalPagar = $subtotal + $iva;

        // Preparamos la información detallada para cada cuenta si está dividida
        $cuentasDividadasInfo = [];
        if ($cuentasDivididas) {
            for ($i = 1; $i <= $totalCuentasDivision; $i++) {
                $cuentasDividadasInfo[] = [
                    'numero_cuenta' => $i,
                    'subtotal' => $subtotal / $totalCuentasDivision,
                    'iva' => $iva / $totalCuentasDivision,
                    'propina' => $propina / $totalCuentasDivision,
                    'total' => $totalPagar / $totalCuentasDivision,
                    'productos' => DB::table('detalle_ordenes')->where('orden_id', $orden->id)->get()
                ];
            }
        }

        // Productos para el listado lateral
        $productos = DB::table('detalle_ordenes')
            ->where('orden_id', $orden->id)
            ->get();

        return view('admin.caja.cobrar', compact(
            'mesa',
            'orden',
            'productos',
            'cuentasDivididas',
            'totalCuentasDivision',
            'cuentasDividadasInfo',
            'subtotal',
            'iva',
            'propina',
            'totalPagar'
        ));
    }
}