<?php

namespace App\Http\Controllers\Admin;

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
        // Traemos todas las categorías ordenadas por el campo 'orden_visualizacion' (Ej: 1, 2, 3)
        // y luego alfabéticamente por 'nombre'
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
        // 1. Validamos los datos estrictamente
        $request->validate([
            'nombre'              => 'required|string|max:255|unique:categorias,nombre',
            'color'               => 'nullable|string|max:20',
            'orden_visualizacion' => 'nullable|integer|min:0'
        ]);

        // 2. Creamos la categoría
        Categoria::create([
            'nombre'              => trim($request->nombre), // Quitamos espacios en blanco extra
            'slug'                => Str::slug($request->nombre), // Convierte "Platos Fuertes" en "platos-fuertes"
            'color'               => $request->color ?? '#3B82F6', // Azul por defecto si no escogen uno
            'orden_visualizacion' => $request->orden_visualizacion ?? 0 // Cero si no lo llenan
        ]);

        // 3. Regresamos a la pantalla con éxito
        return redirect()->route('admin.categorias.index')
                         ->with('success', '¡Categoría creada exitosamente!');
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            // unique excluye el ID actual para que deje guardar si no le cambian el nombre
            'nombre'              => 'required|string|max:255|unique:categorias,nombre,' . $id,
            'color'               => 'nullable|string|max:20',
            'orden_visualizacion' => 'nullable|integer|min:0'
        ]);

        $categoria->update([
            'nombre'              => trim($request->nombre),
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
        
        // Antes de borrar, podrías revisar si la categoría tiene productos asignados
        if ($categoria->productos()->count() > 0) {
            return redirect()->back()
                             ->with('error', 'No puedes eliminar esta categoría porque aún tiene platillos asignados.');
        }

        $categoria->delete(); // Gracias al modelo, esto hace un SoftDelete (no lo borra de la BD)

        return redirect()->route('admin.categorias.index')
                         ->with('success', 'Categoría eliminada del menú.');
    }
}