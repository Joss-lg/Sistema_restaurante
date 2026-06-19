<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use App\Models\Producto; // Asumiendo que tu modelo se llama así
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    // 1. Cargar la vista principal con el listado y los productos para los modales
    public function index()
    {
        $promociones = Promocion::with('productos')->get();
        $productos = Producto::all(); // Necesario para llenar los checkboxes de tus modales
        
        return view('admin.promociones.index', compact('promociones', 'productos'));
    }

    // 2. Guardar la nueva promoción
    public function store(Request $request)
    {
        // Tu lógica de validación aquí...
        
        $promocion = Promocion::create($request->all());
        
        // Sincronizar los checkboxes de productos en la tabla pivote
        if ($request->has('productos')) {
            $promocion->productos()->sync($request->productos);
        }

        return redirect()->back()->with('success', 'Promoción creada correctamente.');
    }

    // 3. Responder con JSON al fetch() del modal de editar
    public function edit(Promocion $promocion)
    {
        return response()->json([
            'promocion' => $promocion,
            //  Por esto:
            'productos_vinculados' => $promocion->productos()->pluck('productos.id')->toArray()
        ]);
    }

    // 4. Actualizar la promoción existente
   public function update(Request $request, Promocion $promocion)
    {
        // 1. Tus validaciones normales aquí...
        // $request->validate([ ... ]);

        $datos = $request->all();

        /**
         * 🌟 CONTROL INTELIGENTE DEL BOOLEANO:
         * - Si viene del formulario tradicional y está apagado: $request->has('esta_activa') será FALSE -> guarda false.
         * - Si viene del switch AJAX y está apagado: llegará '0', por lo que validamos que no sea '0' -> guarda false.
         * - Si está encendido (en ambos casos): será TRUE y diferente de '0' -> guarda true.
         */
        $datos['esta_activa'] = $request->has('esta_activa') && $request->esta_activa != '0';

        // 2. Actualizar el modelo
        $promocion->update($datos);

        // 3. Sincronizar productos si la petición viene del formulario modal completo
        if ($request->has('productos')) {
            $promocion->productos()->sync($request->productos);
        }

        // 4. RESPUESTA DINÁMICA: Si es AJAX, responde con JSON; si es formulario normal, redirecciona.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado de la promoción actualizado correctamente.',
                'esta_activa' => $promocion->esta_activa
            ]);
        }

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción actualizada con éxito.');
    }
    // 5. Eliminar el registro
    public function destroy(Promocion $promocion)
    {
        // Desvincular productos en la tabla intermedia antes de borrar (opcional si usas OnDelete Cascade)
        $promocion->productos()->detach(); 
        
        $promocion->delete();

        return redirect()->back()->with('success', 'Promoción eliminada permanentemente.');
    }
}