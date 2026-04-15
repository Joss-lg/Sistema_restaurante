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
       
        $empleados = User::where('rol', '!=', 'admin')->get();
        
        
        $permisos = Permiso::all();

        return view('admin.empleados.index', compact('empleados', 'permisos'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_empleado' => 'required|unique:users,codigo_empleado|digits:4', // PIN de 4 dígitos único
            'rol' => 'required|string|in:administrador,capitan,mesero,cocinero,cajero',
          
        ]);

       
        $empleado = User::create([
            'nombre' => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol' => $request->rol,
            'password' => Hash::make($request->codigo_empleado), 
        ]);
        

        
        return redirect()->route('admin.empleados.index')
                        ->with('success', '¡Empleado registrado! Ahora puedes asignar sus permisos en la tabla.');
    }

    public function destroy($id)
    {
        $empleado = \App\Models\User::findOrFail($id);
        $empleado->delete();

        // Redirigimos con un mensaje de éxito (opcional)
        return redirect()->back()->with('success', 'Empleado eliminado correctamente.');
    }


}