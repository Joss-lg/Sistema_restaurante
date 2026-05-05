<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Mesa;
use App\Models\Categoria;
use App\Models\Producto;

class ComandaController extends Controller
{
    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();

        $rol = strtolower(trim($usuario->rol));

        if ($rol === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
            if ($mesa->mesero_id !== $usuario->id) {
                abort(403, 'No tienes permiso para ver esta mesa.');
            }
        }

        if (in_array($rol, ['capitan', 'capitán'], true) && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }

        $categorias = Categoria::all();
        $productos = Producto::with(['categoria', 'modificadores'])->get();

        return view('mesero.comanda', compact('mesa', 'categorias', 'productos'));
    }

    public function enviar(Request $request)
    {
        // 1. Buscamos la mesa usando el ID que nos mandó la pantalla
        $mesa = Mesa::find($request->mesa_id);
        
        if ($mesa) {
            // 2. ¡EL CAMBIO MÁGICO! Pasamos la mesa a ocupada
            $mesa->estado = 'ocupada';
            
            // Opcional: si en un futuro agregas total_consumo a tu tabla mesas
            // $mesa->total_consumo = $request->total;
            
            $mesa->save();
        }

        // Aquí más adelante crearás el registro en tu tabla "ordenes" y "detalles_orden"
        // Orden::create([ ... ]);

        return response()->json([
            'success' => true, 
            'message' => 'Orden enviada y mesa marcada como ocupada'
        ]);
    }

    // ==========================================
    // NUEVA FUNCIÓN PARA GUARDAR LA MESA
    // ==========================================
    public function storeMesa(Request $request)
    {
        // 1. Validamos que los datos vengan correctamente desde los inputs
        $request->validate([
            'numero' => 'required|string|max:20',
            'capacidad' => 'required|integer|min:1'
        ]);

        // 2. Buscamos la mesa por número
        $mesa = Mesa::where('numero', $request->numero)->first();

        if ($mesa && $mesa->estado === 'ocupada') {
            return response()->json([
                'success' => false,
                'message' => 'La mesa ya está ocupada. Elige otra mesa o cierra la orden antes de abrir una nueva.'
            ], 409);
        }

        // 3. Creamos o actualizamos la mesa para abrirla
        $mesa = $mesa ?? new Mesa(['numero' => $request->numero]);
        $mesa->capacidad = $request->capacidad;
        $mesa->estado = 'ocupada';
        if (strtolower(trim(auth()->user()->rol)) === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
            $mesa->mesero_id = auth()->id();
        }
        $mesa->save();

        return response()->json([
            'success' => true,
            'message' => 'Mesa abierta correctamente',
            'mesa' => $mesa
        ]);
    }
}