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
     * Muestra la lista de empleados.
     */
    public function index(Request $request)
    {
        // CAMBIO CLAVE: Usamos withTrashed() para que Laravel no nos oculte a los inactivos
        $query = User::withTrashed();

        if (!$request->has('ver_inactivos')) {
            // Si NO queremos ver inactivos, filtramos solo los activos y que no estén borrados
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

        // CAMBIO CLAVE: Generamos un correo único agregando un número aleatorio si no se proporciona uno
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
        // Usamos withTrashed por si acaso estamos editando a alguien inactivo
        $empleado = User::withTrashed()->findOrFail($id);
        
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
        
        // Se envían los módulos a la vista para que el formulario pueda iterar sobre ellos y armar la matriz
        $modulos = Modulo::orderBy('nombre')->get(); 
        
        return view('admin.empleados.permisos', compact('empleado', 'modulos'));
    }

    /**
     * Procesa y sincroniza los permisos asignados al empleado.
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $empleado = User::withTrashed()->findOrFail($id);
        $todosLosModulos = Modulo::pluck('id'); 
        $permisosEnviados = $request->input('permisos', []);

        foreach ($todosLosModulos as $moduloId) {
            $acciones = $permisosEnviados[$moduloId] ?? [];

            // MEJORA: updateOrCreate reduce la lógica de if/else y hace exactamente lo mismo de forma más limpia
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
        // CAMBIO CLAVE: withTrashed() para poder encontrarlo si ya estaba inactivo
        $empleado = User::withTrashed()->findOrFail($id);
        
        if ($empleado->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        // Lógica de borrado definitivo (ahora sí lo borrará de MySQL)
        if ($empleado->esta_activo == false) {
            // Eliminar dependencias primero (permisos) para evitar errores de llave foránea si no hay onDelete('cascade')
            $empleado->permisos()->delete(); 
            $empleado->forceDelete(); 
            
            return redirect()->back()->with('success', 'El empleado ha sido eliminado permanentemente de la base de datos.');
        }

        // Baja lógica: Lo marcamos como inactivo y aplicamos el SoftDelete
        $empleado->update(['esta_activo' => false]);
        $empleado->delete(); 
        
        return redirect()->back()->with('success', 'Empleado dado de baja.');
    }

    /**
     * Restaura un empleado dado de baja lógica.
     */
    public function reactivar($id)
    {
        // CAMBIO CLAVE: withTrashed() para encontrarlo y restaurarlo
        $empleado = User::withTrashed()->findOrFail($id);
        $empleado->restore(); // Le quitamos el SoftDelete
        $empleado->update(['esta_activo' => true]);
        
        return redirect()->back()->with('success', "Empleado reactivado exitosamente.");
    }
}