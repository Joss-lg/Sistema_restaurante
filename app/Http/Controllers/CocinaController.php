<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    /**
     * Muestra la pantalla de cocina/barra con las órdenes activas y contadores.
     *
     * IMPORTANTE: cada tarjeta que ve Cocina ya NO representa una Orden
     * completa, sino un "lote_envio" (una ronda de "Enviar Orden" o de
     * traspaso). Así, si a una mesa se le manda un pedido y luego, ya
     * enviado, se le agrega y envía algo más, aparecen dos tarjetas
     * separadas en vez de mezclarse en una sola.
     *
     * NUEVO: cada tarjeta también se separa por ÁREA (Cocina/Barra),
     * usando el mismo campo 'area_impresion' de la categoría del
     * producto que ya usa ComandaService para los tickets de impresión.
     * Si un mismo lote trae productos de cocina Y de barra mezclados,
     * se generan dos tarjetas independientes (una por área), cada una
     * mostrando solo sus productos correspondientes.
     */
    public function index(Request $request)
    {
        // Área seleccionada por query param: ?area=barra (default: cocina)
        $areaSeleccionada = strtolower($request->query('area', 'cocina')) === 'barra' ? 'Barra' : 'Cocina';

        $ordenes = Orden::with(['mesa:id,numero', 'mesero:id,nombre', 'detalles.producto.categoria'])
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereHas('detalles')
            ->orderBy('abierta_el', 'asc')
            ->get();

        // Función auxiliar: resuelve el área de un detalle exactamente
        // igual que ComandaService::procesarEnvio, para que ambos lugares
        // siempre coincidan.
        $resolverArea = function ($detalle) {
            $area = $detalle->producto->categoria->area_impresion ?? 'Cocina';
            return $area !== 'Barra' ? 'Cocina' : 'Barra';
        };

        // Explotamos cada Orden en tantas "comandas" (tarjetas) como
        // combinaciones de lote de envío + área tenga.
        $comandasTodas = collect();
        foreach ($ordenes as $orden) {
            $porLote = $orden->detalles->groupBy(function ($detalle) {
                return $detalle->lote_envio ?? 'sin-lote';
            });

            foreach ($porLote as $lote => $detallesLote) {
                $porArea = $detallesLote->groupBy($resolverArea);

                foreach ($porArea as $area => $detallesArea) {
                    $comandasTodas->push((object) [
                        'id'        => $orden->id . '-' . $lote . '-' . $area, // id compuesto único
                        'orden_id'  => $orden->id,
                        'lote'      => $lote,
                        'area'      => $area,
                        'mesa'      => $orden->mesa,
                        'mesero'    => $orden->mesero,
                        'estado'    => $orden->estado,
                        'detalles'  => $detallesArea,
                        'creado_en' => $detallesArea->min('created_at'),
                    ]);
                }
            }
        }

        // Filtramos solo las comandas del área que se está viendo
        $comandas = $comandasTodas
            ->where('area', $areaSeleccionada)
            ->sortBy('creado_en')
            ->values();

        // Contadores calculados SOLO sobre el área seleccionada
        $pendientes = $comandas->where('estado', 'pendiente')->count();
        $enProceso  = $comandas->where('estado', 'en proceso')->count();

        // "Servidas" se mantiene como conteo global de órdenes servidas
        // recientes (no depende de lote/área, es informativo del turno)
        $servidas = Orden::whereHas('detalles')->where('estado', 'servida')->count();

        // Total de "órdenes activas" mostradas en esta área (tarjetas únicas)
        $ordenesActivasEnArea = $comandas->pluck('orden_id')->unique()->count();

        return view('admin.cocina.index', compact(
            'comandas', 'pendientes', 'enProceso', 'servidas', 'areaSeleccionada', 'ordenesActivasEnArea'
        ));
    }

    /**
     * Actualiza el estado de una orden.
     *
     * OJO: esto sigue actuando sobre la Orden COMPLETA (todas sus tarjetas/
     * lotes/áreas a la vez), no solo sobre la tarjeta individual en la que
     * se dio clic. Si necesitas que cada tarjeta (o cada área) tenga su
     * propio estado independiente, es un cambio adicional (mover 'estado'
     * a DetalleOrden o crear una tabla de lotes) — dime si lo quieres y lo
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

        return redirect()->route('admin.cocina.index', ['area' => strtolower($request->query('area', 'cocina'))])
                         ->with('success', 'Estado de la orden actualizado correctamente.');
    }
}