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
        // CAMBIO: Quitamos el where('rol', '!=', 'admin') para que TODOS sean visibles
        $empleados = User::all(); 
        
        $permisos = Permiso::all();

        return view('admin.empleados.index', compact('empleados', 'permisos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_empleado' => 'required|unique:users,codigo_empleado|digits:4',
            // CAMBIO: Usamos 'admin' en lugar de 'administrador' para que coincida con el Modelo
            'rol' => 'required|string|in:admin,capitan,mesero,cocinero,cajero',
        ]);

        $empleado = User::create([
            'nombre' => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol' => $request->rol,
            'password' => Hash::make($request->codigo_empleado),
            'esta_activo' => true, // Aseguramos que se cree activo
        ]);

        return redirect()->route('admin.empleados.index')
                        ->with('success', '¡Empleado registrado! Ahora puedes asignar sus permisos en la tabla.');
    }

    public function destroy($id)
    {
        $empleado = User::findOrFail($id);
        $empleado->delete();

        return redirect()->back()->with('success', 'Empleado eliminado correctamente.');
    }
}