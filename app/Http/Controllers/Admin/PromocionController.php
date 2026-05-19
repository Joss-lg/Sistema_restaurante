<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use App\Models\Producto;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        // Solo si tiene permiso de ver
        if (!auth()->user()->tienePermiso('promociones', 'ver')) {
            abort(403);
        }

        $promociones = Promocion::with('productos')->get();
        $productos = Producto::all();
        return view('admin.promociones.index', compact('promociones', 'productos'));
    }

    public function store(Request $request)
    {
        // 1. Verificación de permisos (Matriz de seguridad)
        if (!auth()->user()->tienePermiso('promociones', 'crear')) {
            return back()->with('error', 'No autorizado');
        }

        // 2. Validación robusta
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_promocion' => 'required|in:porcentaje,2x1,fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'dias_semana' => 'nullable|array',
            'productos' => 'required|array' // Asegúrate de que el select de productos envíe este array
        ]);

        // 3. Preparación de datos
        $data = $request->except(['productos', 'dias_semana', 'esta_activa']);
    
        // Convertimos los días (ej: [1,2,4]) a JSON para la base de datos
        $data['dias_semana'] = $request->has('dias_semana') 
            ? json_encode($request->dias_semana) 
            : json_encode([]);

        // Asignamos el estado enviado desde el formulario
        $data['esta_activa'] = (bool) $request->esta_activa;

        // 4. Persistencia en Base de Datos
        $promocion = Promocion::create($data);

        // 5. Relación Muchos a Muchos (Tabla promocion_productos)
        if ($request->has('productos') && !empty($request->productos)) {
            $promocion->productos()->attach($request->productos);
        }

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción "' . $promocion->nombre . '" creada con éxito');
    }

    public function create()
    {
        if (!auth()->user()->tienePermiso('promociones', 'crear')) {
            abort(403);
        }

        $productos = Producto::all();
        return view('admin.promociones.create', compact('productos'));
    }

    public function edit(Promocion $promocion)
    {
        if (!auth()->user()->tienePermiso('promociones', 'editar')) {
            abort(403);
        }

        $productos = Producto::all();
        return view('admin.promociones.edit', compact('promocion', 'productos'));
    }

    public function update(Request $request, Promocion $promocion)
    {
        if (!auth()->user()->tienePermiso('promociones', 'editar')) {
            return back()->with('error', 'No autorizado');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'valor_descuento' => 'required|numeric',
            'productos' => 'required|array'
        ]);

        $promocion->update($request->except('productos'));

        $promocion->productos()->sync($request->productos);

        return redirect()->route('admin.promociones.index')->with('success', 'Promoción actualizada con éxito');
    }

    public function destroy(Promocion $promocion)
    {
        if (!auth()->user()->tienePermiso('promociones', 'eliminar')) {
            return back()->with('error', 'No autorizado');
        }

        $promocion->delete();

        return redirect()->route('admin.promociones.index')->with('success', 'Promoción eliminada con éxito');
    }
}