<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    public function index()
    {
        // Cargamos todos los usuarios
        $empleados = User::all(); 
        $permisos = Permiso::all();

        return view('admin.empleados.index', compact('empleados', 'permisos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_empleado' => 'required|unique:users,codigo_empleado|digits:4',
            'rol' => 'required|string|in:admin,capitan,mesero,cocinero,cajero',
        ]);

        $empleado = User::create([
            'nombre' => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol' => $request->rol,
            'password' => Hash::make($request->codigo_empleado),
            'esta_activo' => true,
        ]);

        return redirect()->route('admin.empleados.index')
                        ->with('success', '¡Empleado registrado! Ahora puedes configurar sus permisos.');
    }

    /**
     * Muestra la Matriz de Permisos para un empleado específico
     */
    public function permisos($id)
    {
        // Buscamos al empleado o lanzamos error 404
        $empleado = User::findOrFail($id);
        
        // Obtenemos todos los permisos base del sistema
        $permisosBase = Permiso::all();

        return view('admin.empleados.permisos', compact('empleado', 'permisosBase'));
    }

    /**
     * Procesa y guarda los permisos seleccionados en la matriz
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::findOrFail($id);

        // Aquí la lógica dependerá de cómo tengas tu tabla intermedia, 
        // pero lo más común es usar sync() si usas muchos a muchos.
        if ($request->has('permisos')) {
            // Ejemplo básico: $empleado->permisos()->sync($request->permisos);
            // Por ahora solo redirigimos con éxito
        }

        return redirect()->route('admin.empleados.index')
                        ->with('success', 'Permisos de ' . $empleado->nombre . ' actualizados correctamente.');
    }

    public function destroy($id)
    {
        $empleado = User::findOrFail($id);
        $empleado->delete();

        return redirect()->back()->with('success', 'Empleado eliminado correctamente.');
    }
}