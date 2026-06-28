<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    /**
     * Muestra la lista de empleados activos.
     */
    public function index(Request $request)
{
    // Si la URL tiene ?ver_inactivos=1, traemos a todos. Si no, solo los activos.
    $query = User::query();

    if (!$request->has('ver_inactivos')) {
        $query->where('esta_activo', true);
    }

    $empleados = $query->with(['permisos:id,nombre', 'rol:id,nombre'])
                       ->get();
    
    $permisos = Permiso::select(['id', 'nombre', 'slug'])->get();
    $roles = Rol::where('puede_acceder_pos', true)->orderBy('nombre')->get();

    return view('admin.empleados.index', compact('empleados', 'permisos', 'roles'));
}

    /**
     * Registra un nuevo empleado en el sistema.
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => trim($request->nombre),
            'codigo_empleado' => trim($request->codigo_empleado)
        ]);

        $request->validate([
            'nombre'          => 'required|string|max:255',
            'codigo_empleado' => 'required|digits:4|unique:users,codigo_empleado',
            'rol_id'          => 'required|exists:roles,id',
        ]);

        User::create([
            'nombre'          => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol_id'          => $request->rol_id,
            'password'        => Hash::make($request->codigo_empleado),
            'esta_activo'     => true,
        ]);

        return redirect()->route('admin.empleados.index')
                         ->with('success', '¡Empleado registrado! Ahora puedes configurar sus permisos.');
    }

    /**
     * Muestra la Matriz de Permisos para un empleado específico.
     */
    public function permisos($id)
    {
        $empleado = User::with(['permisos', 'rol'])->findOrFail($id);
        $permisosBase = Permiso::orderBy('nombre')->get();

        return view('admin.empleados.permisos', compact('empleado', 'permisosBase'));
    }

    /**
     * Procesa y guarda los permisos seleccionados en la matriz (Checkboxes).
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::with('rol')->findOrFail($id);

        if ($empleado->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes modificar tus propios permisos por seguridad corporativa.');
        }

        $request->validate([
            'permisos'   => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $permisosSeleccionados = $request->input('permisos', []);
        $empleado->permisos()->sync($permisosSeleccionados);

        return redirect()->route('admin.empleados.index')
                         ->with('success', "Los permisos de {$empleado->nombre} se han actualizado correctamente.");
    }

    /**
     * Da de baja un empleado (Borrado Lógico).
     */
    public function destroy($id)
    {
        $empleado = User::with('rol')->findOrFail($id);
        
        if ($empleado->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta de usuario.');
        }

        if ($empleado->rol && $empleado->rol->slug === 'admin') {
            return redirect()->back()->with('error', 'No se puede eliminar a un administrador del sistema.');
        }

        $empleado->update(['esta_activo' => false]);

        return redirect()->back()->with('success', 'Empleado dado de baja correctamente.');
    }

    /**
     * Actualiza los datos base del empleado.
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nombre' => trim($request->nombre),
            'codigo_empleado' => trim($request->codigo_empleado)
        ]);

        $request->validate([
            'nombre'          => 'required|string|max:255',
            'codigo_empleado' => 'required|digits:4|unique:users,codigo_empleado,' . $id,
            'rol_id'          => 'required|exists:roles,id',
        ]);

        $empleado = User::findOrFail($id);
        
        $data = [
            'nombre'          => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'rol_id'          => $request->rol_id,
        ];

        if ($empleado->codigo_empleado !== $request->codigo_empleado) {
            $data['password'] = Hash::make($request->codigo_empleado);
        }

        $empleado->update($data);

        return redirect()->route('admin.empleados.index')
                         ->with('success', "¡Datos de {$empleado->nombre} actualizados correctamente!");
    }

    /**
 * Reactiva un empleado dado de baja.
 */
public function reactivar($id)
{
    $empleado = User::findOrFail($id);
    
    $empleado->update(['esta_activo' => true]);

    return redirect()->back()->with('success', "¡El empleado {$empleado->nombre} ha sido reactivado correctamente!");
}

}