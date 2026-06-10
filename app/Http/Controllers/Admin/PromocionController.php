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
            // Obtenemos solo los IDs de los productos ya vinculados a esta promo
            'productos_vinculados' => $promocion->productos()->pluck('id')->toArray()
        ]);
    }

    // 4. Actualizar la promoción existente
    public function update(Request $request, Promocion $promocion)
    {
        // Tu lógica de validación aquí...

        // Si el switch iOS no viene en el request, significa que lo apagaron (falso)
        $data = $request->all();
        $data['esta_activa'] = $request->has('esta_activa') ? 1 : 0;

        $promocion->update($data);

        // Actualizar la tabla pivote de productos
        $promocion->productos()->sync($request->input('productos', []));

        return redirect()->back()->with('success', 'Promoción actualizada con éxito.');
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