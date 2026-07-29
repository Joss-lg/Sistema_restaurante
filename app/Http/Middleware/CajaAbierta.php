<?php

namespace App\Http\Middleware;

use App\Models\CajaMovimiento;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea las acciones que generan consumo (abrir mesa, enviar a cocina,
 * reabrir una cuenta, crear un pedido de delivery) cuando NO hay un turno
 * de caja abierto.
 *
 * Sin esto, un mesero puede levantar pedidos con la caja cerrada: esos
 * consumos quedan huérfanos, no entran a ningún corte y descuadran el
 * inventario y las ventas del día.
 *
 * Se valida en el servidor a propósito. Ocultar los botones en pantalla
 * ayuda al usuario, pero no impide que alguien llegue por la URL directa
 * o que la caja se cierre justo mientras la pantalla ya estaba abierta.
 */
class CajaAbierta
{
    public function handle(Request $request, Closure $next): Response
    {
        if (CajaMovimiento::where('estado', 'abierta')->exists()) {
            return $next($request);
        }

        $mensaje = 'La caja está cerrada. Pide que se abra el turno de caja antes de levantar pedidos.';

        // Casi todo el módulo de mesero funciona por fetch/AJAX, así que la
        // respuesta normal es JSON. 409 (Conflict) porque no es un problema
        // de permisos del usuario, sino del estado del sistema.
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => false,
                'caja_cerrada' => true,
                'message'      => $mensaje,
            ], 409);
        }

        return redirect()
            ->route('mesero.dashboard')
            ->with('error', $mensaje);
    }
}