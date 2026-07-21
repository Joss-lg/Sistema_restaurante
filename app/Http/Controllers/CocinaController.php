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
     * Actualiza el estado de UNA tarjeta específica (orden + lote + área),
     * NO de la Orden completa. Así, avanzar el estado en Barra no toca los
     * productos de Cocina de la misma orden, y viceversa.
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

        // Filtramos, dentro del lote indicado, solo los detalles que
        // pertenecen al área objetivo (misma lógica que en construirComandas).
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

        // Si TODOS los detalles de la Orden (todas las áreas y lotes) ya
        // están servidos, marcamos la Orden completa como 'servida' para
        // que el resto del sistema (caja, mesero) lo refleje también.
        $orden->refresh();
        $todosServidos = $orden->detalles->every(fn ($d) => $d->estado_preparacion === 'servida');
        if ($todosServidos && $orden->estado !== 'servida') {
            $orden->update(['estado' => 'servida']);
        } elseif (!$todosServidos && $orden->estado === 'pendiente') {
            // Si al menos un detalle ya avanzó, reflejamos "en proceso" a
            // nivel Orden (usado por otras pantallas que sí leen Orden->estado).
            $orden->update(['estado' => 'en proceso']);
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

    /**
     * Construye las comandas (tarjetas) y los contadores para el área
     * seleccionada. Compartido entre index() y apiComandas() para que
     * ambos siempre devuelvan exactamente lo mismo.
     */
    private function construirComandas(string $areaSeleccionada): array
    {
        $ordenes = Orden::with(['mesa:id,numero', 'mesero:id,nombre', 'detalles.producto.categoria'])
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereHas('detalles')
            ->orderBy('abierta_el', 'asc')
            ->get();

        $comandasTodas = collect();
        foreach ($ordenes as $orden) {
            $porLote = $orden->detalles->groupBy(function ($detalle) {
                return $detalle->lote_envio ?? 'sin-lote';
            });

            foreach ($porLote as $lote => $detallesLote) {
                $porArea = $detallesLote->groupBy(fn ($detalle) => $this->resolverAreaDetalle($detalle));

                foreach ($porArea as $area => $detallesArea) {
                    // El estado de la tarjeta es el estado_preparacion de
                    // sus detalles. Si por alguna razón vienen mezclados
                    // (no debería pasar, siempre se actualizan juntos),
                    // usamos el "más atrasado": pendiente > en proceso > servida.
                    $estadosPresentes = $detallesArea->pluck('estado_preparacion')->unique();
                    if ($estadosPresentes->contains('pendiente')) {
                        $estadoTarjeta = 'pendiente';
                    } elseif ($estadosPresentes->contains('en proceso')) {
                        $estadoTarjeta = 'en proceso';
                    } else {
                        $estadoTarjeta = 'servida';
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

        $pendientes = $comandas->where('estado', 'pendiente')->count();
        $enProceso  = $comandas->where('estado', 'en proceso')->count();

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