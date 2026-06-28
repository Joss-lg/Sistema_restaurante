<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermisoController extends Controller
{
    /**
     * Guarda un nuevo permiso en el catálogo base.
     */
    public function store(Request $request)
    {
        // 1. Limpiamos espacios en blanco antes de validar la unicidad
        $request->merge([
            'nombre' => trim($request->nombre)
        ]);

        $request->validate([
            'nombre'      => 'required|string|max:50|unique:permisos,nombre',
            'descripcion' => 'nullable|string|max:255'
        ]);

        // 2. Creamos el permiso con su slug limpio
        Permiso::create([
            'nombre'      => $request->nombre,
            'slug'        => Str::slug($request->nombre),
            'descripcion' => trim($request->descripcion),
        ]);

        return back()->with('success', 'El permiso ha sido registrado en el sistema correctamente.');
    }

    /**
     * Sincroniza los permisos de un empleado específico desde un formulario directo.
     */
    public function asignarPermisos(Request $request, $id)
    {
        // 1. Buscamos al empleado por su ID
        $empleado = User::findOrFail($id);

        // Seguridad: Impedir que el usuario en sesión se altere sus propios permisos
        if ($empleado->id === auth()->id()) {
            return back()->with('error', 'No puedes modificar tus propios permisos directamente desde este apartado.');
        }

        // 2. Validamos que el array de permisos contenga únicamente IDs reales y existentes
        $request->validate([
            'permisos'   => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        // 3. Sincronizamos los permisos mapeados en la tabla pivote
        $permisosSeleccionados = $request->input('permisos', []);
        $empleado->permisos()->sync($permisosSeleccionados);

        return back()->with('success', "Los permisos del empleado han sido actualizados.");
    }
}