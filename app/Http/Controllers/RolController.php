<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    /**
     * Muestra el panel del catálogo de roles y puestos con sus métricas.
     */
    public function index()
    {
        // Traemos los roles contando eficientemente la cantidad de usuarios vinculados (Evita N+1)
        $roles = Rol::withCount('usuarios')->orderBy('nombre', 'asc')->get(); 
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Registra un nuevo puesto/rol en el sistema de manera dinámica.
     */
    public function store(Request $request)
    {
        // 1. Limpieza preventiva de espacios para evitar falsos positivos en unique
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        // 2. Validación estricta del payload
        $request->validate([
            'nombre'            => 'required|string|max:255|unique:roles,nombre',
            'descripcion'       => 'nullable|string|max:500',
            'puede_acceder_pos' => 'boolean' // Cambiado a opcional/boolean para mayor flexibilidad de UI (Switches)
        ]);

        try {
            // 3. Persistencia mediante el Modelo
            Rol::create([
                'nombre'            => $request->nombre,
                'slug'              => Str::slug($request->nombre), 
                'descripcion'       => trim($request->descripcion),
                'puede_acceder_pos' => $request->boolean('puede_acceder_pos'), // Convierte on, 1 o true limpiamente
            ]);

            return redirect()->route('admin.roles.index')
                             ->with('success', '¡Puesto registrado con éxito de manera dinámica!');

        } catch (\Exception $e) {
            Log::error('Error en RolController@store: ' . $e->getMessage());
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Ocurrió un error inesperado al registrar el puesto.');
        }
    }

    /**
     * Actualiza las propiedades estructurales de un puesto existente.
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'            => 'required|string|max:255|unique:roles,nombre,' . $id,
            'descripcion'       => 'nullable|string|max:500',
            'puede_acceder_pos' => 'boolean'
        ]);

        try {
            $rol = Rol::findOrFail($id);

            $rol->update([
                'nombre'            => $request->nombre,
                'slug'              => Str::slug($request->nombre),
                'descripcion'       => trim($request->descripcion),
                'puede_acceder_pos' => $request->boolean('puede_acceder_pos'),
            ]);

            return redirect()->route('admin.roles.index')
                             ->with('success', 'Puesto actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en RolController@update: ' . $e->getMessage());
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Ocurrió un error al intentar modificar el puesto.');
        }
    }

    /**
     * Elimina un rol del sistema si no cuenta con dependencias activas de personal.
     */
    public function destroy($id)
    {
        $rol = Rol::withCount('usuarios')->findOrFail($id);

        // Candado relacional de seguridad: impide dejar huérfanos a los empleados activos
        if ($rol->usuarios_count > 0) {
            return redirect()->route('admin.roles.index')
                             ->with('error', "No se puede eliminar el puesto '{$rol->nombre}' porque tiene {$rol->usuarios_count} empleados asignados.");
        }

        try {
            $rol->delete();
            return redirect()->route('admin.roles.index')
                             ->with('success', 'Puesto removido del sistema correctamente.');
                             
        } catch (\Exception $e) {
            Log::error('Error en RolController@destroy: ' . $e->getMessage());
            return redirect()->route('admin.roles.index')
                             ->with('error', 'Ocurrió un error interno al procesar la baja del puesto.');
        }
    }
}