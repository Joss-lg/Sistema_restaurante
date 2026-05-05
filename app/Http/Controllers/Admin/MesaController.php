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
}
