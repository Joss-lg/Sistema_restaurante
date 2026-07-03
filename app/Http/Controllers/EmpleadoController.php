<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permiso;
use App\Models\Modulo;
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

        $empleados = $query->with(['permisos', 'rol:id,nombre'])->get();
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
            'rol_id'            => $request->rol_id,
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
        
        // 🌟 LÓGICA DE BLINDAJE: Verificamos si te estás editando a ti mismo
        $esMismoUsuario = ($empleado->id === auth()->id());

        $reglas = [
            'nombre'            => 'required|string|max:255',
            'puede_acceder_pos' => 'boolean',
            'codigo_empleado'   => 'required_if:puede_acceder_pos,1|nullable|digits:4|unique:users,codigo_empleado,' . $id,
        ];

        // Solo exigimos el rol_id si NO eres tú mismo (porque si eres tú, el campo se envía vacío o bloqueado)
        if (!$esMismoUsuario) {
            $reglas['rol_id'] = 'required|exists:roles,id';
        }

        $request->validate($reglas);

        // Si eres tú mismo, forzamos tus valores actuales de la Base de Datos
        // Si es otro empleado, tomamos lo que seleccionaste en el formulario
        $esPOS = $esMismoUsuario ? $empleado->puede_acceder_pos : $request->boolean('puede_acceder_pos');
        $rolId = $esMismoUsuario ? $empleado->rol_id : $request->rol_id;
        
        $data = [
            'nombre'            => trim($request->nombre),
            'rol_id'            => $rolId,
            'puede_acceder_pos' => $esPOS,
            'codigo_empleado'   => $esPOS ? $request->codigo_empleado : null,
        ];

        if ($esPOS && $request->filled('codigo_empleado')) {
            $data['password'] = Hash::make($request->codigo_empleado);
        }

        $empleado->update($data);

        return redirect()->route('admin.empleados.index')->with('success', 'Datos actualizados.');
    }

    public function permisos($id)
    {
        $empleado = User::with('permisos')->findOrFail($id);
        return view('admin.empleados.permisos', compact('empleado'));
    }

    public function actualizarPermisos(Request $request, $id)
{
    $empleado = User::findOrFail($id);
    // Traemos todos los IDs de módulos existentes para asegurar que cubrimos el 1 al 10
    $todosLosModulos = \App\Models\Modulo::pluck('id'); 
    $permisosEnviados = $request->input('permisos', []);

    foreach ($todosLosModulos as $moduloId) {
        $acciones = $permisosEnviados[$moduloId] ?? [];

        // Buscamos primero el registro existente
        $permiso = Permiso::where('user_id', $empleado->id)
                          ->where('modulo_id', $moduloId)
                          ->first();

        $data = [
            'mostrar'   => isset($acciones['mostrar']) ? 1 : 0,
            'crear'     => isset($acciones['crear']) ? 1 : 0,
            'editar'    => isset($acciones['editar']) ? 1 : 0,
            'eliminar'  => isset($acciones['eliminar']) ? 1 : 0,
            'gestionar' => isset($acciones['gestionar']) ? 1 : 0,
        ];

        if ($permiso) {
            $permiso->update($data);
        } else {
            Permiso::create(array_merge([
                'user_id' => $empleado->id, 
                'modulo_id' => $moduloId
            ], $data));
        }
    }

    return redirect()->route('admin.empleados.index')
                     ->with('success', "Permisos de {$empleado->nombre} actualizados correctamente.");
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