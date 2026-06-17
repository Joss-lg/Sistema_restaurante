<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AlimentoController extends Controller
{
    /**
     * Mostrar la vista principal con todos los productos
     */
    public function index()
    {
        $categorias = Categoria::all();
        $insumosDisponibles = Insumo::where('esta_activo', true)->orderBy('nombre')->get();

        return view('admin.alimentos.index', compact('categorias', 'insumosDisponibles'));
    }

    /**
     * Obtener todos los productos en formato JSON
     */
    public function getProductos(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'insumos'])
            ->get()
            ->groupBy('categoria.nombre');
        
        return response()->json($productos);
    }

    /**
     * Obtener estadísticas de productos
     */
    public function getEstadisticas(): JsonResponse
    {
        $totalProductos = Producto::count();
        $productosDisponibles = Producto::where('esta_disponible', true)->count();
        $totalCategorias = Categoria::count();

        return response()->json([
            'total' => $totalProductos,
            'disponibles' => $productosDisponibles,
            'categorias' => $totalCategorias
        ]);
    }

    /**
     * Guardar un nuevo producto
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'categoria_nombre' => 'required|string|max:255',
            'tiempo_preparacion' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
            'insumos' => 'nullable|array',
            'insumos.*' => 'nullable|exists:insumos,id',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'nullable|numeric|min:0.001',
        ]);

        $categoriaId = $validated['categoria_id'] ?? null;
        if (!$categoriaId) {
            $categoriaId = Categoria::firstOrCreate(
                ['nombre' => $validated['categoria_nombre']],
                ['slug' => Str::slug($validated['categoria_nombre'])]
            )->id;
        }

        $producto = Producto::create([
            'nombre' => $validated['nombre'],
            'precio' => $validated['precio'],
            'categoria_id' => $categoriaId,
            'tiempo_preparacion' => $validated['tiempo_preparacion'] ?? 15,
            'descripcion' => $validated['descripcion'] ?? null,
            'esta_disponible' => true,
        ]);

        if (!empty($validated['insumos']) && !empty($validated['cantidades'])) {
            $receta = [];
            foreach ($validated['insumos'] as $index => $insumoId) {
                $cantidad = $validated['cantidades'][$index] ?? null;
                if ($insumoId && $cantidad && floatval($cantidad) > 0) {
                    $receta[$insumoId] = ['cantidad_usada' => floatval($cantidad)];
                }
            }
            if (!empty($receta)) {
                $producto->insumos()->sync($receta);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'producto' => $producto->load('categoria')
        ], 201);
    }

    /**
     * Actualizar un producto
     */
    public function update(Request $request, $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'categoria_nombre' => 'required|string|max:255',
            'tiempo_preparacion' => 'nullable|integer|min:1',
            'esta_disponible' => 'nullable|boolean',
            'descripcion' => 'nullable|string',
            'insumos' => 'nullable|array',
            'insumos.*' => 'nullable|exists:insumos,id',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'nullable|numeric|min:0.001',
        ]);

        $categoriaId = $validated['categoria_id'] ?? null;
        if (!$categoriaId) {
            $categoriaId = Categoria::firstOrCreate(
                ['nombre' => $validated['categoria_nombre']],
                ['slug' => Str::slug($validated['categoria_nombre'])]
            )->id;
        }

        $validated['categoria_id'] = $categoriaId;
        unset($validated['categoria_nombre']);

        $producto->update([
            'nombre' => $validated['nombre'],
            'precio' => $validated['precio'],
            'categoria_id' => $validated['categoria_id'],
            'tiempo_preparacion' => $validated['tiempo_preparacion'] ?? $producto->tiempo_preparacion,
            'descripcion' => $validated['descripcion'] ?? $producto->descripcion,
            'esta_disponible' => $validated['esta_disponible'] ?? $producto->esta_disponible,
        ]);

        $receta = [];
        if (!empty($validated['insumos']) && !empty($validated['cantidades'])) {
            foreach ($validated['insumos'] as $index => $insumoId) {
                $cantidad = $validated['cantidades'][$index] ?? null;
                if ($insumoId && $cantidad && floatval($cantidad) > 0) {
                    $receta[$insumoId] = ['cantidad_usada' => floatval($cantidad)];
                }
            }
        }
        $producto->insumos()->sync($receta);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto->load('categoria')
        ]);
    }

    /**
     * Eliminar un producto
     */
    public function destroy($id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    /**
     * Cambiar disponibilidad de un producto
     */
    public function toggleDisponibilidad($id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['esta_disponible' => !$producto->esta_disponible]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'disponible' => $producto->esta_disponible
        ]);
    }
}