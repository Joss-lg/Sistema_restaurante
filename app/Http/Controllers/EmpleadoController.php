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
    /**
     * ID del administrador principal, intocable para cualquier otro usuario.
     */
    private const ADMIN_ID = 1;

    /**
     * Verifica si el empleado objetivo es el admin principal.
     * Si lo es, bloquea la acción con un mensaje claro (redirige al listado de empleados).
     * Devuelve null si se puede continuar, o un redirect si se debe bloquear.
     */
    private function bloquearSiEsAdmin(User $empleado)
    {
        if ($empleado->id === self::ADMIN_ID) {
            return redirect()->route('admin.empleados.index')
                ->with('error', 'El administrador principal no puede ser modificado, eliminado ni gestionado.');
        }

        return null;
    }

    /**
     * Muestra la lista de empleados.
     */
    public function index(Request $request)
    {
        $query = User::withTrashed();

        if (!$request->has('ver_inactivos')) {
            $query->where('esta_activo', true)->whereNull('deleted_at');
        }

        $empleados = $query->with(['permisos', 'rol:id,nombre'])->get();
        $roles = Rol::orderBy('nombre')->get();

        return view('admin.empleados.index', compact('empleados', 'roles'));
    }

    /**
     * Almacena un nuevo empleado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'rol_id'            => 'required|exists:roles,id',
            'puede_acceder_pos' => 'boolean',
            'codigo_empleado'   => 'required_if:puede_acceder_pos,1|nullable|digits:4|unique:users,codigo_empleado',
        ]);

        $esPOS = $request->boolean('puede_acceder_pos');

        $correoGenerado = strtolower(str_replace(' ', '', $request->nombre)) . rand(100, 999) . '@empresa.com';

        User::create([
            'nombre'            => trim($request->nombre),
            'email'             => $request->email ?? $correoGenerado,
            'rol_id'            => $request->rol_id,
            'puede_acceder_pos' => $esPOS,
            'codigo_empleado'   => $esPOS ? $request->codigo_empleado : null,
            'password'          => $esPOS ? Hash::make($request->codigo_empleado) : Hash::make('acceso_limitado'),
            'esta_activo'       => true,
        ]);

        return redirect()->route('admin.empleados.index')->with('success', 'Empleado registrado exitosamente.');
    }

    /**
     * Actualiza los datos de un empleado existente.
     */
    public function update(Request $request, $id)
    {
        $empleado = User::withTrashed()->findOrFail($id);

        // El admin principal es intocable, incluso si el usuario tiene permiso de "editar".
        if ($bloqueo = $this->bloquearSiEsAdmin($empleado)) {
            return $bloqueo;
        }

        $esMismoUsuario = ($empleado->id === auth()->id());

        $reglas = [
            'nombre'            => 'required|string|max:255',
            'puede_acceder_pos' => 'boolean',
            'codigo_empleado'   => 'required_if:puede_acceder_pos,1|nullable|digits:4|unique:users,codigo_empleado,' . $id,
        ];

        if (!$esMismoUsuario) {
            $reglas['rol_id'] = 'required|exists:roles,id';
        }

        $request->validate($reglas);

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

    /**
     * Muestra el formulario para configurar los permisos del empleado por módulo.
     */
    public function permisos($id)
    {
        $empleado = User::withTrashed()->with('permisos')->findOrFail($id);

        // El admin principal no necesita (ni permite) gestión de permisos por nadie más.
        if ($bloqueo = $this->bloquearSiEsAdmin($empleado)) {
            return $bloqueo;
        }

        $modulos = Modulo::orderBy('nombre')->get();

        return view('admin.empleados.permisos', compact('empleado', 'modulos'));
    }

    /**
     * Procesa y sincroniza los permisos asignados al empleado.
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::withTrashed()->findOrFail($id);

        // Nadie puede modificar los permisos del admin principal.
        if ($bloqueo = $this->bloquearSiEsAdmin($empleado)) {
            return $bloqueo;
        }

        $todosLosModulos = Modulo::pluck('id');
        $permisosEnviados = $request->input('permisos', []);

        foreach ($todosLosModulos as $moduloId) {
            $acciones = $permisosEnviados[$moduloId] ?? [];

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

        return redirect()->route('admin.empleados.index')
                         ->with('success', "Permisos de {$empleado->nombre} actualizados correctamente.");
    }

    /**
     * Aplica la baja lógica (SoftDelete) o eliminación definitiva de un empleado.
     */
    public function destroy($id)
    {
        $empleado = User::withTrashed()->findOrFail($id);

        // El admin principal jamás puede ser dado de baja ni eliminado, por nadie.
        if ($bloqueo = $this->bloquearSiEsAdmin($empleado)) {
            return $bloqueo;
        }

        if ($empleado->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        if ($empleado->esta_activo == false) {
            $empleado->permisos()->delete();
            $empleado->forceDelete();

            return redirect()->back()->with('success', 'El empleado ha sido eliminado permanentemente de la base de datos.');
        }

        $empleado->update(['esta_activo' => false]);
        $empleado->delete();

        return redirect()->back()->with('success', 'Empleado dado de baja.');
    }

    /**
     * Restaura un empleado dado de baja lógica.
     */
    public function reactivar($id)
    {
        $empleado = User::withTrashed()->findOrFail($id);
        $empleado->restore();
        $empleado->update(['esta_activo' => true]);

        return redirect()->back()->with('success', "Empleado reactivado exitosamente.");
    }
}