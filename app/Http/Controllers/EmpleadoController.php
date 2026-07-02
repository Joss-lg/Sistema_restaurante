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

        $empleados = $query->with(['permisos:id,nombre', 'rol:id,nombre'])->get();
        $permisos = Permiso::select(['id', 'nombre', 'slug'])->get();
        
        // CORRECCIÓN: Eliminamos el where('puede_acceder_pos', true)
        // porque esa columna ya no existe en la tabla roles.
        $roles = Rol::orderBy('nombre')->get(); 

        return view('admin.empleados.index', compact('empleados', 'permisos', 'roles'));
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

    public function permisos($id)
    {
        $empleado = User::with(['permisos', 'rol'])->findOrFail($id);
        $permisosBase = Permiso::orderBy('nombre')->get();
        return view('admin.empleados.permisos', compact('empleado', 'permisosBase'));
    }

    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::findOrFail($id);
        $request->validate(['permisos' => 'nullable|array', 'permisos.*' => 'exists:permisos,id']);
        $empleado->permisos()->sync($request->input('permisos', []));
        return redirect()->route('admin.empleados.index')->with('success', "Permisos actualizados.");
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