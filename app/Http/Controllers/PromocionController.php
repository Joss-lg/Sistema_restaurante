<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromocionController extends Controller
{
    /**
     * API: Obtener promociones activas para el Punto de Venta.
     */
    public function getPromocionesActivas(): JsonResponse
    {
        try {
            $promociones = Promocion::where('esta_activa', true)
                // Seleccionamos las columnas reales de la base de datos
                ->select(['id', 'nombre', 'descripcion', 'tipo_promocion', 'valor_descuento', 'dias_semana'])
                ->get()
                ->map(function ($promo) {
                    return [
                        'id'              => $promo->id,
                        'nombre'          => $promo->nombre,
                        'descripcion'     => $promo->descripcion,
                        'tipo_promocion'  => $promo->tipo_promocion, // Sincronizado       
                        'valor_descuento' => $promo->valor_descuento, // Sincronizado     
                        'dias_semana'     => $promo->dias_semana,
                    ];
                });

            return response()->json([
                'success'     => true,
                'promociones' => $promociones,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener promociones activas en PromocionController', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar promociones',
            ], 500);
        }
    }
    /**
     * Cargar el listado de promociones y los productos disponibles.
     */
    public function index()
    {
        $promociones = Promocion::with('productos:id,nombre,precio')->get();

        // FIX: faltaba traer se_vende_por_peso y precio_por_100g. Sin esas
        // columnas, la vista (modal-crear/modal-editar) no podía distinguir
        // los productos por peso y siempre mostraba $0.00 (el campo `precio`
        // real de esos productos, que a propósito se guarda en 0 porque su
        // precio vive en precio_por_100g).
        $productos = Producto::where('esta_disponible', true)
                             ->orderBy('nombre')
                             ->select(['id', 'nombre', 'precio', 'se_vende_por_peso', 'precio_por_100g'])
                             ->get();
        
        return view('admin.promociones.index', compact('promociones', 'productos'));
    }

    /**
     * Responder con JSON al fetch() del modal de edición.
     */
    public function edit(Promocion $promocion)
    {
        return response()->json([
            'success'              => true,
            'promocion'            => $promocion,
            'productos_vinculados' => $promocion->productos()->pluck('productos.id')->toArray()
        ]);
    }

    /**
     * Actualizar los parámetros de la promoción o conmutar su estado por AJAX.
     */
   public function update(Request $request, Promocion $promocion)
{
    // Si es un cambio rápido de switch desde el index (Toggle Status)
    if ($request->has('toggle_status')) {
        $promocion->update([
            'esta_activa' => $request->boolean('esta_activa')
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Estado actualizado correctamente.',
            'esta_activa' => $promocion->esta_activa
        ]);
    }

    $request->merge([
        'nombre' => trim($request->nombre)
    ]);

    // VALIDACIÓN: Ajustada a los nombres correctos de tus columnas
    $request->validate([
        'nombre'          => 'required|string|max:255',
        'descripcion'     => 'nullable|string|max:500',
        'tipo_promocion'  => 'required|in:descuento_fijo,porcentaje,dos_por_uno,combo',
        'valor_descuento' => 'required|numeric|min:0',
        'fecha_inicio'    => 'required|date',
        'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
        'dias_semana'     => 'nullable|array',
        'productos'       => 'nullable|array',
        'productos.*'     => 'exists:productos,id',
    ]);

    try {
        DB::transaction(function () use ($request, $promocion) {
            $promocion->update([
                'nombre'          => $request->nombre,
                'descripcion'     => $request->descripcion,
                'tipo_promocion'  => $request->tipo_promocion, // Corregido
                'valor_descuento' => $request->valor_descuento, // Corregido
                'fecha_inicio'    => $request->fecha_inicio,
                'fecha_fin'       => $request->fecha_fin,
               'dias_semana' => $request->input('dias_semana', []),
                'esta_activa'     => $request->boolean('esta_activa'),
            ]);

            $promocion->productos()->sync($request->input('productos', []));
        });

        return response()->json([
            'success' => true,
            'message' => 'Promoción actualizada con éxito.'
        ]);

    } catch (\Exception $e) {
        Log::error('Error en PromocionController@update: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Registrar una nueva promoción.
     */
 public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'tipo_promocion' => 'required|in:descuento_fijo,porcentaje,dos_por_uno,combo',
        'valor_descuento' => 'required|numeric|min:0',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'dias_semana' => 'nullable|array',
        'productos' => 'nullable|array',
    ]);

    try {

        DB::beginTransaction();

        $promocion = Promocion::create([

            'nombre' => $request->nombre,

            'descripcion' => $request->descripcion,

            'tipo_promocion' => $request->tipo_promocion,

            'valor_descuento' => $request->valor_descuento,

            'fecha_inicio' => $request->fecha_inicio,

            'fecha_fin' => $request->fecha_fin,

            // IMPORTANTE: SIN json_encode()
            'dias_semana' => $request->dias_semana,

            'esta_activa' => $request->boolean('esta_activa', true),

        ]);

        if ($request->filled('productos')) {

            $promocion->productos()->sync($request->productos);

        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Promoción creada correctamente.'
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ],500);

    }

}


    /**
     * Eliminar el registro de manera atómica.
     */
    public function destroy(Promocion $promocion)
    {
        try {
            DB::transaction(function () use ($promocion) {
                $promocion->productos()->detach(); 
                $promocion->delete();
            });

            return redirect()->back()->with('success', 'Promoción eliminada permanentemente del sistema.');

        } catch (\Exception $e) {
            Log::error('Error en PromocionController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo eliminar la promoción seleccionada.');
        }
    }
} // <-- Este cierre asegura que la clase PromocionController finalice correctamente