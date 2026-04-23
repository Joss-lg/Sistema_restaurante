<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    /**
     * Muestra la lista de empleados y permisos base.
     */
    public function index()
    {
        // Cargamos los empleados con sus permisos para evitar consultas extra (Eager Loading)
        $empleados = User::with('permisos')->get(); 
        $permisos = Permiso::all();

        return view('admin.empleados.index', compact('empleados', 'permisos'));
    }

    /**
     * Registra un nuevo empleado en el sistema.
     */
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
     * Muestra la Matriz de Permisos para un empleado específico.
     */
    public function permisos($id)
    {
        // Buscamos al empleado con sus permisos actuales cargados
        $empleado = User::with('permisos')->findOrFail($id);
        
        // Obtenemos todos los permisos del sistema para mostrarlos en la matriz
        $permisosBase = Permiso::all();

        return view('admin.empleados.permisos', compact('empleado', 'permisosBase'));
    }

    /**
     * Procesa y guarda los permisos seleccionados en la matriz (Checkboxes).
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::findOrFail($id);

        // Seguridad: Evitar que el admin se bloquee a sí mismo
        if ($empleado->rol === 'admin' && $id == auth()->id()) {
            return redirect()->back()->with('error', 'No puedes modificar tus propios permisos de administrador.');
        }

        // Sincronización Many-to-Many
        // Si no se selecciona ningún checkbox, se envía un array vacío para quitar todo
        $permisosSeleccionados = $request->input('permisos', []);
        
        // El método sync() actualiza la tabla 'permiso_user' automáticamente
        $empleado->permisos()->sync($permisosSeleccionados);

        return redirect()->route('admin.empleados.index')
                        ->with('success', 'Los permisos de ' . $empleado->nombre . ' se han actualizado correctamente.');
    }

    public function destroy($id)
    {
        $empleado = User::findOrFail($id);
        
        // Seguridad: Evitar eliminar al admin principal
        if ($empleado->rol === 'admin') {
            return redirect()->back()->with('error', 'No se puede eliminar a un administrador del sistema.');
        }

        $empleado->delete();

        return redirect()->back()->with('success', 'Empleado eliminado correctamente.');
    }

        public function update(Request $request, $id)
    {
        // 1. Validar los datos (el PIN debe ser único excepto para el mismo usuario)
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_empleado' => 'required|digits:4|unique:users,codigo_empleado,' . $id,
            'rol' => 'required|string',
        ]);

        // 2. Buscar al empleado y actualizar
        $empleado = User::findOrFail($id);
        
        $empleado->update([
            'nombre' => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol' => $request->rol,
            // Opcional: Si quieres que el password se actualice si cambian el PIN
            'password' => Hash::make($request->codigo_empleado),
        ]);

        return redirect()->route('admin.empleados.index')
                        ->with('success', '¡Datos de ' . $empleado->nombre . ' actualizados correctamente!');
    }
}