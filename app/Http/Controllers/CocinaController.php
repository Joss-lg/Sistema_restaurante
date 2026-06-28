<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    /**
     * Muestra la pantalla de cocina con las órdenes activas y contadores.
     */
    public function index()
    {
        // 1. CORRECCIÓN: Cambiado 'name' por 'nombre' para coincidir con tu modelo User
        $ordenes = Orden::with(['mesa:id,numero', 'mesero:id,nombre', 'detalles.producto:id,nombre'])
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereHas('detalles') 
            ->orderBy('abierta_el', 'asc')
            ->get();

        // 2. Agrupamos y contamos los estados en una sola consulta optimizada
        $estadoCounts = Orden::whereHas('detalles')
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->groupBy('estado')
            ->selectRaw('estado, count(*) as total')
            ->pluck('total', 'estado');

        // Accedemos de forma segura a los valores del collection
        $pendientes = $estadoCounts->get('pendiente', 0);
        $enProceso  = $estadoCounts->get('en proceso', 0);
        $servidas   = $estadoCounts->get('servida', 0);

        return view('admin.cocina.index', compact('ordenes', 'pendientes', 'enProceso', 'servidas'));
    }

    /**
     * Actualiza el estado de una orden.
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en proceso,servida',
        ]);

        $orden = Orden::findOrFail($id);
        $orden->update([
            'estado' => $request->estado
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado de la orden actualizado correctamente.',
                'estado'  => $orden->estado
            ]);
        }

        return redirect()->route('admin.cocina.index')
                         ->with('success', 'Estado de la orden actualizado correctamente.');
    }
}