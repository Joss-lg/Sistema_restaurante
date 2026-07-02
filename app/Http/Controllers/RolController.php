<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    public function index()
    {
        // Traemos los roles con el conteo de usuarios asignados
        $roles = Rol::withCount('usuarios')->orderBy('nombre', 'asc')->get(); 
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permisos = Permiso::all(); 
        return view('admin.roles.create', compact('permisos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:500',
            'permisos'    => 'nullable|array', 
            'permisos.*'  => 'exists:permisos,id'
        ]);

        try {
            $rol = Rol::create([
                'nombre'      => trim($request->nombre),
                'slug'        => Str::slug($request->nombre),
                'descripcion' => trim($request->descripcion),
            ]);

            // Sincronización limpia de permisos
            if ($request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            }

            return redirect()->route('admin.roles.index')
                             ->with('success', 'Rol creado y permisos asignados.');

        } catch (\Exception $e) {
            Log::error('Error al guardar rol: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al registrar.');
        }
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:255|unique:roles,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id'
        ]);

        try {
            $rol->update([
                'nombre'      => trim($request->nombre),
                'slug'        => Str::slug($request->nombre),
                'descripcion' => trim($request->descripcion),
            ]);

            // Usamos sync() para actualizar la tabla pivote de forma segura
            $rol->permisos()->sync($request->input('permisos', []));

            return redirect()->route('admin.roles.index')
                             ->with('success', 'Rol y permisos actualizados.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar.');
        }
    }

    public function destroy($id)
    {
        $rol = Rol::withCount('usuarios')->findOrFail($id);

        if ($rol->usuarios_count > 0) {
            return redirect()->route('admin.roles.index')
                             ->with('error', "No se puede eliminar: tiene empleados asignados.");
        }

        $rol->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}