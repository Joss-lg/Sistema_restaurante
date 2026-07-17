<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
class ProductoController extends Controller
{
    /**
     * Muestra el catálogo de Alimentos (Menú) y sus recetas.
     */
    public function index()
    {
        // 1. Traemos los platillos con su categoría y sus insumos acotando columnas (Evita N+1)
        $productos = Producto::with(['categoria:id,nombre', 'insumos:id,nombre,unidad_medida'])
                             ->orderBy('nombre')
                             ->get();

        // 2. Traemos datos optimizados para los selectores de los modales
        $categorias = Categoria::orderBy('nombre')->select(['id', 'nombre'])->get();
        
        // Solo insumos activos para la formulación de nuevas recetas
        $insumosDisponibles = Insumo::where('esta_activo', true)
                                    ->orderBy('nombre')
                                    ->select(['id', 'nombre', 'unidad_medida'])
                                    ->get();

        return view('admin.productos.index', compact('productos', 'categorias'), ['insumos' => $insumosDisponibles]);
    }

    /**
     * Registra un nuevo platillo y guarda su receta (ingredientes).
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'             => 'required|string|max:255',
            'categoria_id'       => 'required|exists:categorias,id',
            'precio'             => 'required|numeric|min:0',
            'se_vende_por_peso'  => 'sometimes|boolean',
            // Solo es obligatorio si el producto se vende por peso.
            'precio_por_100g'    => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            'insumos'            => 'nullable|array',
            'insumos.*'          => 'exists:insumos,id',
            'cantidades'         => 'nullable|array',
            'cantidades.*'       => 'required_with:insumos|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $sePorPeso = $request->boolean('se_vende_por_peso');

            // 1. Creamos el Platillo principal
            $producto = Producto::create([
                'nombre'             => $request->nombre,
                'categoria_id'       => $request->categoria_id,
                // Si se vende por peso, el precio fijo no aplica: se calcula
                // en el POS a partir de precio_por_100g y el gramaje elegido.
                'precio'             => $sePorPeso ? 0 : $request->precio,
                'se_vende_por_peso'  => $sePorPeso,
                'precio_por_100g'    => $sePorPeso ? $request->precio_por_100g : null,
                'esta_disponible'    => $request->boolean('esta_disponible'), // Evalúa "on", 1 o true
            ]);

            // 2. Mapeo seguro de la receta en base a correspondencia de llaves
            if ($request->filled('insumos') && $request->filled('cantidades')) {
                $receta = [];
                foreach ($request->insumos as $index => $insumoId) {
                    if (isset($request->cantidades[$index]) && (float)$request->cantidades[$index] > 0) {
                        $receta[$insumoId] = [
                            'cantidad_usada' => (float)$request->cantidades[$index]
                        ];
                    }
                }
                
                if (!empty($receta)) {
                    $producto->insumos()->sync($receta);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Producto guardado correctamente.'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al guardar el producto.'], 500);
        }
    }

    /**
     * Actualiza un platillo y modifica su receta estructural.
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'             => 'required|string|max:255',
            'categoria_id'       => 'required|exists:categorias,id',
            'precio'             => 'required|numeric|min:0',
            'se_vende_por_peso'  => 'sometimes|boolean',
            'precio_por_100g'    => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            'insumos'            => 'nullable|array',
            'insumos.*'          => 'exists:insumos,id',
            'cantidades'         => 'nullable|array',
            'cantidades.*'       => 'required_with:insumos|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            $sePorPeso = $request->boolean('se_vende_por_peso');

            // 1. Actualización del modelo principal
            $producto->update([
                'nombre'             => $request->nombre,
                'categoria_id'       => $request->categoria_id,
                'precio'             => $sePorPeso ? 0 : $request->precio,
                'se_vende_por_peso'  => $sePorPeso,
                'precio_por_100g'    => $sePorPeso ? $request->precio_por_100g : null,
                 'esta_disponible'    => $request->boolean('esta_disponible'),
            ]);

            // 2. Re-sincronización estructural de ingredientes
            $receta = [];
            if ($request->filled('insumos') && $request->filled('cantidades')) {
                foreach ($request->insumos as $index => $insumoId) {
                    if (isset($request->cantidades[$index]) && (float)$request->cantidades[$index] > 0) {
                        $receta[$insumoId] = [
                            'cantidad_usada' => (float)$request->cantidades[$index]
                        ];
                    }
                }
            }
            
            // Sync vacía o sobreescribe la tabla pivote de manera segura
            $producto->insumos()->sync($receta);

            DB::commit();
            return response()->json(['message' => 'Producto actualizado correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar el producto.'], 500);
        }
    }

    /**
     * Elimina un platillo del menú (Soporta Soft Delete nativo).
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $nombre = $producto->nombre;
        $producto->delete();

        return response()->json(['message' => "El producto ({$nombre}) fue eliminado correctamente."]);
    }

    /**
     * Alterna la disponibilidad instantánea del platillo (Switch de operaciones).
     */
    public function toggleDisponibilidad($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->esta_disponible = !$producto->esta_disponible;
        $producto->save();

        $estadoStr = $producto->esta_disponible ? 'habilitado' : 'deshabilitado';

        return response()->json([
            'message'         => "El producto ({$producto->nombre}) ha sido {$estadoStr}.",
            'esta_disponible' => $producto->esta_disponible,
        ]);
    }
    public function getProductos(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'insumos', 'modificadores'])
            ->get()
            ->groupBy('categoria.nombre');
        
        return response()->json($productos);
    }

    public function getEstadisticas(): JsonResponse
    {
        return response()->json([
            'total' => Producto::count(),
            'disponibles' => Producto::where('esta_disponible', true)->count(),
            'categorias' => Categoria::count(),
        ]);
    }
}