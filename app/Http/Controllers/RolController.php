<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')->orderBy('nombre', 'asc')->get(); 
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:500',
        ]);

        try {
            Rol::create([
                'nombre'      => trim($request->nombre),
                'descripcion' => $request->descripcion,
            ]);

            return redirect()->route('admin.roles.index')
                             ->with('success', 'Rol registrado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al guardar rol: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al registrar el rol.');
        }
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:255|unique:roles,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        try {
            $rol->update([
                'nombre'      => trim($request->nombre),
                'descripcion' => $request->descripcion,
            ]);

            return redirect()->route('admin.roles.index')
                             ->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el rol.');
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