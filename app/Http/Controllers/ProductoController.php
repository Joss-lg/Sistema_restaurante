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
        $productos = Producto::with(['categoria:id,nombre', 'insumos:id,nombre,unidad_medida'])
                             ->select([
                                 'id', 'categoria_id', 'nombre', 'descripcion', 'precio',
                                 'se_vende_por_peso', 'precio_por_100g', 'esta_disponible',
                                 'created_at', 'updated_at', 'deleted_at',
                             ])
                             ->selectRaw('imagen IS NOT NULL as tiene_imagen')
                             ->orderBy('nombre')
                             ->get();

        $categorias = Categoria::orderBy('nombre')->select(['id', 'nombre'])->get();

        $insumosDisponibles = Insumo::where('esta_activo', true)
                                    ->orderBy('nombre')
                                    ->select(['id', 'nombre', 'unidad_medida', 'stock_actual'])
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
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'categoria_id'      => 'required|exists:categorias,id',
            'precio'            => 'required|numeric|min:0',
            'se_vende_por_peso' => 'sometimes|boolean',
            'precio_por_100g'   => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            'insumos'           => 'nullable|array',
            'insumos.*'         => 'exists:insumos,id',
            'cantidades'        => 'nullable|array',
            'cantidades.*'      => 'required_with:insumos|numeric|min:0.001',
            'imagen'            => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $sePorPeso = $request->boolean('se_vende_por_peso');

            $producto = new Producto([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => $sePorPeso ? 0 : $request->precio,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? $request->precio_por_100g : null,
                'esta_disponible'   => $request->boolean('esta_disponible'),
            ]);

            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $producto->imagen = file_get_contents($file->getRealPath());
                $producto->imagen_mime_type = $file->getMimeType();
            }

            $producto->save();

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
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'categoria_id'      => 'required|exists:categorias,id',
            'precio'            => 'required|numeric|min:0',
            'se_vende_por_peso' => 'sometimes|boolean',
            'precio_por_100g'   => 'nullable|required_if:se_vende_por_peso,1|numeric|min:0',
            'insumos'           => 'nullable|array',
            'insumos.*'         => 'exists:insumos,id',
            'cantidades'        => 'nullable|array',
            'cantidades.*'      => 'required_with:insumos|numeric|min:0.001',
            'imagen'            => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'quitar_imagen'     => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            $sePorPeso = $request->boolean('se_vende_por_peso');

            $producto->update([
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'categoria_id'      => $request->categoria_id,
                'precio'            => $sePorPeso ? 0 : $request->precio,
                'se_vende_por_peso' => $sePorPeso,
                'precio_por_100g'   => $sePorPeso ? $request->precio_por_100g : null,
                'esta_disponible'   => $request->boolean('esta_disponible'),
            ]);

            if ($request->boolean('quitar_imagen')) {
                $producto->imagen = null;
                $producto->imagen_mime_type = null;
                $producto->save();
            } elseif ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $producto->imagen = file_get_contents($file->getRealPath());
                $producto->imagen_mime_type = $file->getMimeType();
                $producto->save();
            }

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

    /**
     * Devuelve los productos agrupados por categoría, para renderizar las tarjetas.
     */
    public function getProductos(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'insumos', 'modificadores'])
            ->select([
                'id', 'categoria_id', 'nombre', 'descripcion', 'precio',
                'se_vende_por_peso', 'precio_por_100g', 'esta_disponible',
                'updated_at', // 👈 AGREGADO: necesario para el cache-busting de imagen_url
            ])
            ->selectRaw('imagen IS NOT NULL as tiene_imagen')
            ->get()
            ->groupBy(function ($producto) {
                return $producto->categoria->nombre ?? 'Sin Categoría';
            });

        return response()->json($productos);
    }

    public function getEstadisticas(): JsonResponse
    {
        return response()->json([
            'total'       => Producto::count(),
            'disponibles' => Producto::where('esta_disponible', true)->count(),
            'categorias'  => Categoria::count(),
        ]);
    }

    /**
     * Sirve el binario de la imagen del producto con su content-type correcto.
     * Ahora que la URL incluye ?v={timestamp}, cada versión de la imagen tiene
     * su propia URL única, por lo que es seguro cachear agresivamente.
     */
    public function imagen($id)
    {
        $producto = Producto::select('id', 'imagen', 'imagen_mime_type')->findOrFail($id);

        abort_if(!$producto->imagen, 404);

        return response($producto->imagen, 200)
            ->header('Content-Type', $producto->imagen_mime_type)
            ->header('Cache-Control', 'public, max-age=31536000, immutable');
    }
}