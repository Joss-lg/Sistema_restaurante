<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\User;
use App\Models\Modulo;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    /**
     * Esta función recibe los permisos marcados en un formulario.
     * Se espera que el formulario envíe algo como: permisos[modulo_id][accion] = true
     */
    public function asignarPermisos(Request $request, $userId)
    {
        $empleado = User::findOrFail($userId);

        // Validamos que recibimos un array de permisos
        $request->validate([
            'permisos' => 'required|array',
        ]);

        // Recorremos el array de permisos enviado desde el formulario
        // Estructura esperada: permisos[1] = ['mostrar' => 1, 'crear' => 1, ...]
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

        return back()->with('success', "Permisos actualizados correctamente para {$empleado->nombre}.");
    }
}