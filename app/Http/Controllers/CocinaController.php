<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    /**
     * Muestra la pantalla de cocina con las órdenes activas y contadores.
     *
     * IMPORTANTE: cada tarjeta que ve Cocina ya NO representa una Orden
     * completa, sino un "lote_envio" (una ronda de "Enviar Orden" o de
     * traspaso). Así, si a una mesa se le manda un pedido y luego, ya
     * enviado, se le agrega y envía algo más, aparecen dos tarjetas
     * separadas en vez de mezclarse en una sola.
     */
    public function index()
    {
        // 1. CORRECCIÓN: Cambiado 'name' por 'nombre' para coincidir con tu modelo User
        $ordenes = Orden::with(['mesa:id,numero', 'mesero:id,nombre', 'detalles.producto:id,nombre'])
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereHas('detalles')
            ->orderBy('abierta_el', 'asc')
            ->get();

        // NUEVO: explotamos cada Orden en tantas "comandas" (tarjetas) como
        // lotes de envío distintos tenga. Los detalles antiguos que no
        // tengan lote_envio (antes de esta migración) caen todos juntos
        // bajo 'sin-lote', para no perder datos.
        $comandas = collect();
        foreach ($ordenes as $orden) {
            $porLote = $orden->detalles->groupBy(function ($detalle) {
                return $detalle->lote_envio ?? 'sin-lote';
            });

            foreach ($porLote as $lote => $detalles) {
                $comandas->push((object) [
                    'id'        => $orden->id . '-' . $lote, // id compuesto único para la vista/JS
                    'orden_id'  => $orden->id,
                    'lote'      => $lote,
                    'mesa'      => $orden->mesa,
                    'mesero'    => $orden->mesero,
                    'estado'    => $orden->estado,
                    'detalles'  => $detalles,
                    'creado_en' => $detalles->min('created_at'),
                ]);
            }
        }
        $comandas = $comandas->sortBy('creado_en')->values();

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

        return view('admin.cocina.index', compact('ordenes', 'comandas', 'pendientes', 'enProceso', 'servidas'));
    }

    /**
     * Actualiza el estado de una orden.
     *
     * OJO: esto sigue actuando sobre la Orden COMPLETA (todas sus tarjetas/
     * lotes a la vez), no solo sobre el lote individual en el que se dio
     * clic. Si necesitas que cada tarjeta tenga su propio estado
     * independiente, es un cambio adicional (mover 'estado' a
     * DetalleOrden o crear una tabla de lotes) — dime si lo quieres y lo
     * hacemos aparte.
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