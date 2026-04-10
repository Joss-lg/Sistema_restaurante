<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermisoController extends Controller
{
    /**
     * Guarda un nuevo permiso en el catálogo.
     */
    public function store(Request $request)
    {
        // 1. Validamos que el nombre sea obligatorio y único
        $request->validate([
            'nombre' => 'required|unique:permisos,nombre|max:50',
            'descripcion' => 'nullable|max:255'
        ]);

        // 2. Creamos el permiso
        // El slug se genera automáticamente: "Ver Ventas" -> "ver-ventas"
        Permiso::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'descripcion' => $request->descripcion,
        ]);

        // 3. Redireccionamos con un mensaje de éxito
        return back()->with('success', 'El permiso ha sido registrado en el sistema.');
    }
}