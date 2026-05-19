<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolController extends Controller
{
    /**
     * Muestra el panel del catálogo de roles (M) y carga la vista (V).
     */
    public function index()
    {
        // El Controlador le pide los datos al Modelo (M)
        $roles = Rol::withCount('usuarios')->get(); 
        
        // El Controlador envía los datos a la Vista (V)
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Procesa el formulario enviado por la Vista (V) y lo guarda usando el Modelo (M).
     */
    public function store(Request $request)
    {
        // Validamos la información entrante
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:500',
            'puede_acceder_pos' => 'required|boolean'
        ]);

        // El Controlador le ordena al Modelo (M) insertar el nuevo registro
        Rol::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre), // Convierte 'Seguridad Nocturna' en 'seguridad-nocturna'
            'descripcion' => $request->descripcion,
            'puede_acceder_pos' => $request->puede_acceder_pos,
        ]);

        // Redireccionamos de vuelta a la vista con un mensaje de éxito
        return redirect()->route('roles.index')->with('success', '¡Puesto registrado con éxito de manera dinámica!');
    }
}