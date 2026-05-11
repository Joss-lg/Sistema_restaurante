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
        
        // Enriquecer datos de mesas con información de órdenes
        $mesasConEstado = $mesas->map(function ($mesa) {
            // Contar órdenes activas en esta mesa
            // Estados que consideramos como "activos": pendiente, en_proceso, servida
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
        ]);

        $mesa->update([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
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
}
