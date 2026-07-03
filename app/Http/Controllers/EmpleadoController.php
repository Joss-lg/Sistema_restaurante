<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if (!$request->has('ver_inactivos')) {
            $query->where('esta_activo', true);
        }

        // CORRECCIÓN 1: Quitamos 'permisos:id,nombre' porque la tabla permisos ya no tiene 'nombre'.
        // Solo traemos la relación completa.
        $empleados = $query->with(['permisos', 'rol:id,nombre'])->get();
        
        // CORRECCIÓN 2: Eliminamos la consulta $permisosBase porque los permisos 
        // ahora se calculan dinámicamente por módulo, no por catálogo de nombres.
        $roles = Rol::orderBy('nombre')->get(); 

        return view('admin.empleados.index', compact('empleados', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'rol_id'            => 'required|exists:roles,id',
            'puede_acceder_pos' => 'boolean',
            'codigo_empleado'   => 'required_if:puede_acceder_pos,1|nullable|digits:4|unique:users,codigo_empleado',
        ]);

        $esPOS = $request->boolean('puede_acceder_pos');

        User::create([
            'nombre'            => trim($request->nombre),
            'email'             => $request->email ?? strtolower(str_replace(' ', '', $request->nombre)) . '@empresa.com',
            'rol_id'            => $request->rol_id, // El rol sirve para agrupar/identificar el puesto, está perfecto.
            'puede_acceder_pos' => $esPOS,
            'codigo_empleado'   => $esPOS ? $request->codigo_empleado : null,
            'password'          => $esPOS ? Hash::make($request->codigo_empleado) : Hash::make('acceso_limitado'),
            'esta_activo'       => true,
        ]);

        return redirect()->route('admin.empleados.index')->with('success', 'Empleado registrado.');
    }

    public function update(Request $request, $id)
    {
        $empleado = User::findOrFail($id);

        $request->validate([
            'nombre'            => 'required|string|max:255',
            'rol_id'            => 'required|exists:roles,id',
            'puede_acceder_pos' => 'boolean',
            'codigo_empleado'   => 'required_if:puede_acceder_pos,1|nullable|digits:4|unique:users,codigo_empleado,' . $id,
        ]);

        $esPOS = $request->boolean('puede_acceder_pos');
        
        $data = [
            'nombre'            => trim($request->nombre),
            'rol_id'            => $request->rol_id,
            'puede_acceder_pos' => $esPOS,
            'codigo_empleado'   => $esPOS ? $request->codigo_empleado : null,
        ];

        if ($esPOS && $request->filled('codigo_empleado')) {
            $data['password'] = Hash::make($request->codigo_empleado);
        }

        $empleado->update($data);

        return redirect()->route('admin.empleados.index')->with('success', 'Datos actualizados.');
    }

    // CORRECCIÓN 3: Limpiamos la función que carga la vista
    public function permisos($id)
    {
        // Ya no buscamos $permisosBase. La vista que armamos ya tiene los módulos definidos.
        $empleado = User::with('permisos')->findOrFail($id);
        
        return view('admin.empleados.permisos', compact('empleado'));
    }

    // CORRECCIÓN 4: Integramos la lógica de updateOrCreate que te mostré antes
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::findOrFail($id);
        
        $request->validate([
            'permisos' => 'required|array',
        ]);

        // Recorremos los checkboxes enviados desde la vista
        foreach ($request->permisos as $moduloId => $acciones) {
            Permiso::updateOrCreate(
                [
                    'user_id'   => $empleado->id,
                    'modulo_id' => $moduloId
                ],
                [
                    'mostrar'   => isset($acciones['mostrar']) ? 1 : 0,
                    'crear'     => isset($acciones['crear']) ? 1 : 0,
                    'editar'    => isset($acciones['editar']) ? 1 : 0,
                    'eliminar'  => isset($acciones['eliminar']) ? 1 : 0,
                    'gestionar' => isset($acciones['gestionar']) ? 1 : 0,
                ]
            );
        }

        return redirect()->route('admin.empleados.index')->with('success', "Permisos de {$empleado->nombre} actualizados correctamente.");
    }

    public function destroy($id)
    {
        $empleado = User::findOrFail($id);
        if ($empleado->id === auth()->id()) return redirect()->back()->with('error', 'No puedes eliminarte a ti mismo.');
        $empleado->update(['esta_activo' => false]);
        return redirect()->back()->with('success', 'Empleado dado de baja.');
    }

    public function reactivar($id)
    {
        User::findOrFail($id)->update(['esta_activo' => true]);
        return redirect()->back()->with('success', "Empleado reactivado.");
    }
}

