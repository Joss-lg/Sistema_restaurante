<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Maneja las solicitudes entrantes.
     */
    public function handle(Request $request, Closure $next, $nombreModulo)
    {
        $user = $request->user();

        // 1. Si no hay usuario, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Super Admin (ID 1) siempre tiene acceso total
        if ($user->id === 1) {
            return $next($request);
        }

        // 3. Limpiamos el nombre del módulo recibido por seguridad
        $modulo = trim($nombreModulo);

        // 4. Validamos si tiene permiso usando la lógica del modelo User
        if ($user->tienePermiso($modulo)) {
            return $next($request);
        }

        // 5. Si no es admin y no tiene el permiso, denegamos el acceso
        // Usamos back() para que no se pierda la referencia o redirigimos al dashboard del mesero
        return redirect()->route('mesero.dashboard')
                            ->with('error', 'Acceso denegado. No tienes los privilegios para: ' . $modulo);
    }
}