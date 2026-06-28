<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    /**
     * Muestra el panel principal con todas las categorías activas.
     */
    public function index()
    {
        // Traemos todas las categorías ordenadas por el campo 'orden_visualizacion' y luego por 'nombre'
        $categorias = Categoria::orderBy('orden_visualizacion')
                               ->orderBy('nombre')
                               ->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Guarda una nueva categoría desde el modal.
     */
    public function store(Request $request)
    {
        // 1. Validamos los datos estrictamente (Se limpia el input antes de validar la unicidad)
        $request->merge(['nombre' => trim($request->nombre)]);

        $request->validate([
            'nombre'              => 'required|string|max:255|unique:categorias,nombre',
            'color'               => 'nullable|string|max:20',
            'orden_visualizacion' => 'nullable|integer|min:0'
        ]);

        // 2. Creamos la categoría
        Categoria::create([
            'nombre'              => $request->nombre,
            'slug'                => Str::slug($request->nombre),
            'color'               => $request->color ?? '#3B82F6', // Azul por defecto
            'orden_visualizacion' => $request->orden_visualizacion ?? 0
        ]);

        return redirect()->route('admin.categorias.index')
                         ->with('success', '¡Categoría creada exitosamente!');
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        // Limpiamos espacios antes de la validación
        $request->merge(['nombre' => trim($request->nombre)]);

        $request->validate([
            'nombre'              => 'required|string|max:255|unique:categorias,nombre,' . $id,
            'color'               => 'nullable|string|max:20',
            'orden_visualizacion' => 'nullable|integer|min:0'
        ]);

        $categoria->update([
            'nombre'              => $request->nombre,
            'slug'                => Str::slug($request->nombre),
            'color'               => $request->color ?? $categoria->color,
            'orden_visualizacion' => $request->orden_visualizacion ?? 0
        ]);

        return redirect()->route('admin.categorias.index')
                         ->with('success', '¡La categoría fue actualizada!');
    }

    /**
     * Borrado lógico (SoftDelete) de una categoría.
     */
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // Verificación de seguridad usando la relación (se asume que existe el método 'productos' en el modelo)
        if ($categoria->productos()->exists()) {
            return redirect()->back()
                             ->with('error', 'No puedes eliminar esta categoría porque aún tiene platillos asignados.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
                         ->with('success', 'Categoría eliminada del menú.');
    }
}