<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\DetalleOrden;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    /**
     * Muestra la pantalla de cocina/barra con las órdenes activas y contadores.
     *
     * IMPORTANTE: cada tarjeta que ve Cocina/Barra representa un
     * "lote_envio" + "área" (una ronda de "Enviar Orden" o de traspaso,
     * separada además por Cocina/Barra usando area_impresion). El estado
     * de cada tarjeta (pendiente/en proceso/servida) vive en el campo
     * 'estado_preparacion' de cada DetalleOrden, y se actualiza SOLO para
     * los detalles de esa tarjeta específica — así, marcar Barra como
     * lista ya no afecta a Cocina, ni viceversa.
     */
    public function index(Request $request)
    {
        $areaSeleccionada = $this->resolverAreaSeleccionada($request);

        $datos = $this->construirComandas($areaSeleccionada);

        return view('admin.cocina.index', array_merge($datos, [
            'areaSeleccionada' => $areaSeleccionada,
        ]));
    }

    /**
     * NUEVO: endpoint JSON consultado cada 5 segundos por la pantalla de
     * Cocina/Barra (polling) para reflejar pedidos nuevos y cambios de
     * estado sin que nadie tenga que recargar la página. Devuelve el HTML
     * ya renderizado de las tarjetas (partial) más los contadores, para
     * que el JS solo reemplace el contenido sin duplicar lógica de Blade.
     */
    public function apiComandas(Request $request)
    {
        $areaSeleccionada = $this->resolverAreaSeleccionada($request);

        $datos = $this->construirComandas($areaSeleccionada);

        $html = view('admin.cocina.partials.comandas', array_merge($datos, [
            'areaSeleccionada' => $areaSeleccionada,
        ]))->render();

        return response()->json([
            'success'              => true,
            'html'                 => $html,
            'pendientes'           => $datos['pendientes'],
            'enProceso'            => $datos['enProceso'],
            'servidas'             => $datos['servidas'],
            'ordenesActivasEnArea' => $datos['ordenesActivasEnArea'],
        ]);
    }

    /**
 * Actualiza el estado de UNA tarjeta específica (orden + lote + área).
 */
public function actualizarEstado(Request $request, $id)
{
    $request->validate([
        'estado' => 'required|in:pendiente,en proceso,servida',
        'lote'   => 'required|string',
        'area'   => 'required|in:cocina,barra',
    ]);

    $areaObjetivo = $request->area === 'barra' ? 'Barra' : 'Cocina';

    $orden = Orden::with('detalles.producto.categoria')->findOrFail($id);

    // 1. Buscamos y actualizamos solo los detalles del lote y área seleccionada
    $idsAActualizar = $orden->detalles
        ->filter(function ($detalle) use ($request) {
            $lote = $detalle->lote_envio ?? 'sin-lote';
            return $lote === $request->lote;
        })
        ->filter(function ($detalle) use ($areaObjetivo) {
            return $this->resolverAreaDetalle($detalle) === $areaObjetivo;
        })
        ->pluck('id');

    if ($idsAActualizar->isNotEmpty()) {
        DetalleOrden::whereIn('id', $idsAActualizar)->update([
            'estado_preparacion' => $request->estado,
        ]);
    }

    // 2. Sincronizamos el estado global de la Orden
    $orden->refresh();

    // NUEVO: Excluimos los productos cancelados de la verificación
    $detallesRelevantes = $orden->detalles->where('estado', '!=', 'cancelado');

    $todosServidos = $detallesRelevantes->isNotEmpty()
        && $detallesRelevantes->every(fn ($d) => $d->estado_preparacion === 'servida');

    if ($todosServidos) {
        if ($orden->estado !== 'servida') {
            $orden->update(['estado' => 'servida']);
        }
    } else {
        // NUEVO: Si no están todos servidos pero ya entró a cocina, pasa a 'en proceso'
        if ($orden->estado === 'pendiente') {
            $orden->update(['estado' => 'en proceso']);
        }
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'estado'  => $request->estado,
        ]);
    }

    return redirect()->route('admin.cocina.index', ['area' => $request->area])
                     ->with('success', 'Estado actualizado correctamente.');
}

    /**
     * Lee el área seleccionada desde el query param ?area=, con 'cocina'
     * como valor por defecto.
     */
    private function resolverAreaSeleccionada(Request $request): string
    {
        return strtolower($request->query('area', 'cocina')) === 'barra' ? 'Barra' : 'Cocina';
    }

    /**
     * Resuelve el área de un DetalleOrden exactamente igual que
     * ComandaService::procesarEnvio, para que ambos lugares siempre
     * coincidan.
     */
    private function resolverAreaDetalle(DetalleOrden $detalle): string
    {
        $area = $detalle->producto->categoria->area_impresion ?? 'Cocina';
        return $area !== 'Barra' ? 'Cocina' : 'Barra';
    }

        
    private function construirComandas(string $areaSeleccionada): array
    {
        $ordenes = Orden::with(['mesa:id,numero', 'mesero:id,nombre', 'detalles.producto.categoria'])
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereHas('detalles')
            ->orderBy('abierta_el', 'asc')
            ->get();

        $comandasTodas = collect();
        foreach ($ordenes as $orden) {
            // Los productos cancelados nunca deben llegar a la cocina/barra.
            $detallesActivos = $orden->detalles->where('estado', '!=', 'cancelado');

            $porLote = $detallesActivos->groupBy(function ($detalle) {
                return $detalle->lote_envio ?? 'sin-lote';
            });

            foreach ($porLote as $lote => $detallesLote) {
                $porArea = $detallesLote->groupBy(fn ($detalle) => $this->resolverAreaDetalle($detalle));

                foreach ($porArea as $area => $detallesArea) {
                    
                    $estadosPresentes = $detallesArea->pluck('estado_preparacion')->unique();
                    
                    // NUEVA LÓGICA: Si los detalles no están servidos, nacen DIRECTO en proceso.
                    if ($estadosPresentes->contains('servida') && $estadosPresentes->count() === 1) {
                        $estadoTarjeta = 'servida';
                    } else {
                        // Si contiene 'pendiente' o 'en proceso', entra directo como 'en proceso'
                        $estadoTarjeta = 'en proceso';
                    }

                    // No mostramos tarjetas ya servidas en el tablero activo
                    if ($estadoTarjeta === 'servida') {
                        continue;
                    }

                    $comandasTodas->push((object) [
                        'id'        => $orden->id . '-' . $lote . '-' . $area,
                        'orden_id'  => $orden->id,
                        'lote'      => $lote,
                        'area'      => $area,
                        'mesa'      => $orden->mesa,
                        'mesero'    => $orden->mesero,
                        'estado'    => $estadoTarjeta,
                        'detalles'  => $detallesArea,
                        'creado_en' => $detallesArea->min('created_at'),
                    ]);
                }
            }
        }

        $comandas = $comandasTodas
            ->where('area', $areaSeleccionada)
            ->sortBy('creado_en')
            ->values();

        // Ajuste de contadores: Ya no hay 'pendientes' aislados en cocina
        $pendientes = 0; 
        $enProceso  = $comandas->count(); // Todo lo que está en pantalla está en proceso

        // "Servidas" del turno: detalles marcados como servidos hoy, en esta área
        $servidas = DetalleOrden::where('estado_preparacion', 'servida')
            ->whereDate('updated_at', now()->toDateString())
            ->whereHas('producto.categoria', function ($q) use ($areaSeleccionada) {
                if ($areaSeleccionada === 'Barra') {
                    $q->where('area_impresion', 'Barra');
                } else {
                    $q->where(function ($sub) {
                        $sub->where('area_impresion', '!=', 'Barra')->orWhereNull('area_impresion');
                    });
                }
            })
            ->count();

        $ordenesActivasEnArea = $comandas->pluck('orden_id')->unique()->count();

        return compact('comandas', 'pendientes', 'enProceso', 'servidas', 'ordenesActivasEnArea');
    }
}