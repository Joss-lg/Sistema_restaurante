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
        try {
            $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'platillos' => 'required|array|min:1',
                'platillos.*.id' => 'required|integer|exists:productos,id',
                'platillos.*.cantidad' => 'required|integer|min:1',
                'platillos.*.precio' => 'required|numeric|min:0',
                'platillos.*.notas' => 'nullable|string|max:1000',
            ]);

            $mesa = Mesa::findOrFail($request->mesa_id);
            $usuario = auth()->user();

            $subtotal = 0;
            foreach ($request->platillos as $platillo) {
                $subtotal += floatval($platillo['precio']) * intval($platillo['cantidad']);
            }

            $totalNeto = round($subtotal, 2);
            $totalConIva = round($subtotal * 1.16, 2);

            $orden = \App\Models\Orden::create([
                'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id' => $mesa->id,
                'mesero_id' => $usuario->id,
                'estado' => 'pendiente',
                'total' => $totalNeto,
                'propina' => 0,
                'abierta_el' => now(),
            ]);

            foreach ($request->platillos as $platillo) {
                \App\Models\DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'producto_id' => $platillo['id'],
                    'cantidad' => intval($platillo['cantidad']),
                    'precio_unitario' => floatval($platillo['precio']),
                    'estado' => 'en cocina',
                    'notas' => $platillo['notas'] ?? null,
                ]);
            }

            $mesa->estado = 'ocupada';
            if (Schema::hasColumn('mesas', 'mesero_id') && is_null($mesa->mesero_id)) {
                $mesa->mesero_id = auth()->id();
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = $totalConIva;
            }
            $mesa->save();

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa marcada como ocupada',
                'orden_id' => $orden->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la orden. ' . $e->getMessage(),
            ], 500);
        }
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