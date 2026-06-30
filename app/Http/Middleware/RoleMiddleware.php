<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
{
    // Si el usuario logueado tiene ID 1, pasa. Si no, lo bloqueas.
    if ($request->user() && $request->user()->id === 1) {
        return $next($request);
    }

    return redirect()->route('mesero.dashboard')
                     ->with('error', 'Acceso denegado. Solo el Super Administrador puede entrar aquí.');
}
}