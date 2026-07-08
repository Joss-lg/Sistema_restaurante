<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Maneja las solicitudes entrantes.
     * Ahora acepta un cuarto parámetro opcional para la acción ($accion).
     * Si no se envía en la ruta, por defecto será 'mostrar'.
     */
    public function handle(Request $request, Closure $next, $nombreModulo, $accion = 'mostrar')
    {
        $user = $request->user();

        // 1. Si no hay usuario autenticado, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Super Admin (ID 1) siempre tiene acceso total (tu modelo ya lo hace, pero lo dejamos por doble capa)
        if ($user->id === 1) {
            return $next($request);
        }

        // 3. Limpiamos las cadenas recibidas por seguridad
        $modulo = trim($nombreModulo);
        $accion = trim($accion);

        // 4. Validamos pasando tanto el Módulo como la Acción específica
        if ($user->tienePermiso($modulo, $accion)) {
            return $next($request);
        }

        // 5. Si no cuenta con los privilegios, denegamos el acceso con un mensaje descriptivo
        return redirect()->route('mesero.dashboard')
            ->with('error', 'Acceso denegado. No tienes los privilegios para [' . $accion . '] en el módulo: ' . $modulo);
    }
}