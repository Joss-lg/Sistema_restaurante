<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Muestra el catálogo de Alimentos (Menú) y sus recetas.
     */
    public function index()
    {
        // 1. Traemos los platillos con su categoría y su receta (insumos)
        $productos = Producto::with(['categoria', 'insumos'])
                            ->orderBy('nombre')
                            ->get();

        // 2. Traemos datos para los modales de "Crear Platillo"
        $categorias = Categoria::orderBy('nombre')->get();
        
        // Solo traemos insumos activos para armar nuevas recetas
        $insumosDisponibles = Insumo::where('esta_activo', true)
                                  ->orderBy('nombre')
                                  ->get();

        return view('admin.alimentos.index', compact('productos', 'categorias', 'insumosDisponibles'));
    }

    /**
     * Registra un nuevo platillo y guarda su receta (ingredientes).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'tiempo_preparacion' => 'required|integer|min:0',
            // Validación de la receta (Arreglo de insumos y cantidades)
            'insumos' => 'nullable|array',
            'insumos.*' => 'exists:insumos,id',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            // 1. Creamos el Platillo principal
            $producto = Producto::create([
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
                'precio' => $request->precio,
                'tiempo_preparacion' => $request->tiempo_preparacion,
                'esta_disponible' => $request->has('esta_disponible'), // Checkbox del formulario
            ]);

            // 2. Si enviaron ingredientes, armamos la Receta en la tabla pivote
            if ($request->has('insumos') && $request->has('cantidades')) {
                $receta = [];
                
                // Unimos el ID del insumo con la cantidad que gasta
                foreach ($request->insumos as $index => $insumo_id) {
                    if (!empty($request->cantidades[$index])) {
                        $receta[$insumo_id] = ['cantidad_usada' => $request->cantidades[$index]];
                    }
                }
                
                // sync() inserta todos los ingredientes de un golpe en la tabla 'recetas'
                $producto->insumos()->sync($receta);
            }

            DB::commit();
            return redirect()->route('admin.productos.index')->with('success', 'Platillo y receta guardados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el platillo: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un platillo y modifica su receta.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'tiempo_preparacion' => 'required|integer|min:0',
            'insumos' => 'nullable|array',
            'cantidades' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizamos datos básicos
            $producto->update([
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
                'precio' => $request->precio,
                'tiempo_preparacion' => $request->tiempo_preparacion,
                'esta_disponible' => $request->has('esta_disponible'),
            ]);

            // 2. Actualizamos la Receta (sync borra los ingredientes viejos y pone los nuevos)
            $receta = [];
            if ($request->has('insumos') && $request->has('cantidades')) {
                foreach ($request->insumos as $index => $insumo_id) {
                    if (!empty($request->cantidades[$index])) {
                        $receta[$insumo_id] = ['cantidad_usada' => $request->cantidades[$index]];
                    }
                }
            }
            $producto->insumos()->sync($receta);

            DB::commit();
            return redirect()->route('admin.productos.index')->with('success', 'Platillo actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar el platillo.');
        }
    }

    /**
     * Elimina un platillo del menú (Soft Delete).
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete(); // Como usaste SoftDeletes en tu migración, no se borra de verdad
        
        return redirect()->route('admin.productos.index')->with('success', 'El platillo fue eliminado del menú.');
    }

    /**
     * Método rápido para que el cajero apague un platillo si se acabó en la cocina
     */
    public function toggleDisponibilidad($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->esta_disponible = !$producto->esta_disponible;
        $producto->save();

        return redirect()->back()->with('success', 'Disponibilidad actualizada.');
    }
}