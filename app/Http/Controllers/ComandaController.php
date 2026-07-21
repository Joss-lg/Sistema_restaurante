<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Schema, DB, Log};
use App\Models\{Mesa, Categoria, Producto, Orden, DetalleOrden, User, Configuracion};
use App\Services\ComandaService;

class ComandaController extends Controller
{
    protected $comandaService;

    public function __construct(ComandaService $comandaService)
    {
        $this->comandaService = $comandaService;
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();
        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
        $esCapitan = $rolSlug === 'capitan';

        // Restricciones de acceso
        if ($rolSlug === 'mesero' && Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
            abort(403, 'No tienes permiso para ver esta mesa.');
        }
        if ($esCapitan && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }

        $categorias = Categoria::all();
        $productos = Producto::with(['categoria', 'modificadores'])->orderBy('nombre', 'asc')->get();
        $mesasAbiertas = $esCapitan ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get() : collect();

        $comandaActiva = Orden::where('mesa_id', $mesa->id)->where('estado', '!=', 'pagada')->latest()->first();
        $platillosEnviados = $comandaActiva
        ? $comandaActiva->detalles()->with('producto')->get()->map(function ($detalle) {
            return (object) [
                'id'       => $detalle->id,
                'nombre'   => $detalle->producto->nombre ?? 'Platillo',
                'cantidad' => $detalle->cantidad,
                'precio'   => $detalle->precio_unitario,
                'estado'   => $detalle->estado,
            ];
        })
        : collect();

        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();

        return view('mesero.index', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan', 'comandaActiva', 'platillosEnviados', 'ivaHabilitado', 'ivaPorcentaje'));
    }

    public function enviar(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'platillos' => 'required|array|min:1',
            'platillos.*.id' => 'required|exists:productos,id',
            'platillos.*.cantidad' => 'required|integer|min:1',
            'platillos.*.precio' => 'required|numeric',
            'platillos.*.modificadores' => 'nullable|array',
            'platillos.*.gramaje' => 'nullable|string',
            'platillos.*.tiempo' => 'nullable|string',
            'total' => 'required|numeric|min:0',
            'personas' => 'required|integer|min:1',
            'descuento_porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $usuario = auth()->user();

            if ($usuario->rol?->slug !== 'capitan') {
                if (Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
                    return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
                }
            }

            $orden = $this->comandaService->procesarEnvio(
                $mesa,
                $request->platillos,
                $usuario,
                $request->total,
                $request->personas,
                $request->descuento_porcentaje
            );

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa actualizada con éxito.',
                'orden_id' => $orden->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al procesar comanda: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verificarCapitan(Request $request)
    {
        $request->validate(['nip' => 'required|string']);

        $usuario = User::where('codigo_empleado', $request->nip)->first();

        if (!$usuario || $usuario->rol_id !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'NIP inválido o el usuario no tiene permisos de Capitán.'
            ], 403);
        }

        // CAMBIO: ahora regresamos TODAS las mesas (ocupadas y disponibles),
        // ya que el capitán debe poder traspasar tanto a mesas con pedido
        // abierto como a mesas libres (que se abrirán automáticamente al
        // recibir el traspaso). El frontend distingue el estado con un badge.
        $mesas = Mesa::orderBy('numero', 'asc')
                     ->get(['id', 'numero', 'estado']);

        return response()->json(['success' => true, 'mesas' => $mesas]);
    }

    /**
     * PENDIENTE DE COMPLETAR: necesito ver ComandaService.php, Orden.php y
     * DetalleOrden.php para implementar esto correctamente sin adivinar
     * nombres de columnas/relaciones. Este stub documenta el contrato
     * esperado desde el frontend.
     *
     * Espera un JSON:
     * {
     *   mesa_origen_id: number,
     *   mesa_destino_id: number,
     *   productos_nuevos: [ { id, nombre, cantidad, precio, notas, modificadores, gramaje, tiempo } ],   // del ticket aún no enviado
     *   productos_enviados_ids: [ number, ... ]  // ids de DetalleOrden ya enviados a transferir
     * }
     */
public function transferirProductos(Request $request)
    {
        $request->validate([
            'mesa_origen_id'  => 'required|exists:mesas,id',
            'mesa_destino_id' => 'required|exists:mesas,id|different:mesa_origen_id',
            'productos_nuevos' => 'nullable|array',
            'productos_nuevos.*.id' => 'required_with:productos_nuevos|exists:productos,id',
            'productos_nuevos.*.cantidad' => 'required_with:productos_nuevos|integer|min:1',
            'productos_nuevos.*.precio' => 'required_with:productos_nuevos|numeric',
            'productos_enviados_ids' => 'nullable|array',
            'productos_enviados_ids.*' => 'integer|exists:detalles_orden,id',
        ]);

        if (empty($request->productos_nuevos) && empty($request->productos_enviados_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No seleccionaste ningún producto para traspasar.'
            ], 422);
        }

        try {
            $mesaOrigen  = Mesa::findOrFail($request->mesa_origen_id);
            $mesaDestino = Mesa::findOrFail($request->mesa_destino_id);
            $usuario = auth()->user();

            $resultado = $this->comandaService->transferirProductos(
                $mesaOrigen,
                $mesaDestino,
                $request->productos_nuevos ?? [],
                $request->productos_enviados_ids ?? [],
                $usuario
            );

            return response()->json([
                'success' => true,
                'message' => 'Productos traspasados correctamente a Mesa ' . $mesaDestino->numero . '.',
                'orden_destino_id' => $resultado['orden_destino']->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al transferir productos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiMesasAbiertas()
    {
        $mesas = Mesa::all();
        return response()->json([
            'success' => true,
            'mesas_abiertas' => $mesas->where('estado', 'ocupada')->values(),
            'mesas_libres' => $mesas->where('estado', 'disponible')->values(),
        ]);
    }

    public function storeMesa(Request $request)
    {
        $request->validate(['numero' => 'required', 'capacidad' => 'required|integer']);

        $mesa = Mesa::updateOrCreate(['numero' => $request->numero], [
            'estado' => 'ocupada',
            'capacidad' => $request->capacidad,
            'mesero_id' => auth()->user()->id
        ]);

        return response()->json(['success' => true, 'mesa' => $mesa]);
    }

    public function reabrir(Request $request)
    {
        $mesa = Mesa::findOrFail($request->mesa_id);
        $mesa->update(['estado' => 'ocupada', 'mesero_id' => auth()->user()->id]);

        return response()->json(['success' => true]);
    }

    /**
     * Pre-cuenta imprimible (ticket informativo, NO fiscal) con todo lo
     * que la mesa ya tiene enviado a cocina. Se abre en una pestaña nueva
     * desde el botón "Pre Cuenta" del POS del mesero y dispara el diálogo
     * de impresión del navegador (donde se puede elegir "Guardar como PDF").
     */
    public function precuenta($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);

        $orden = Orden::where('mesa_id', $mesa->id)
            ->where('estado', '!=', 'pagada')
            ->with(['detalles.producto', 'mesero:id,nombre'])
            ->latest()
            ->first();

        $detalles = $orden ? $orden->detalles : collect();

        $subtotal = $detalles->sum(fn ($d) => $d->cantidad * $d->precio_unitario);

        $descuentoPorcentaje = 0;
        if ($orden && Schema::hasColumn('ordenes', 'descuento_porcentaje')) {
            $descuentoPorcentaje = (float) ($orden->descuento_porcentaje ?? 0);
        }

        $descuento = $subtotal * ($descuentoPorcentaje / 100);
        $subtotalConDescuento = max(0, $subtotal - $descuento);

        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje();
        $iva = $ivaHabilitado ? $subtotalConDescuento * ($ivaPorcentaje / 100) : 0;
        
        // --- EXTRAER PROPINA ---
        $propina = 0;
        if ($orden && Schema::hasColumn('ordenes', 'propina')) {
            $propina = (float) ($orden->propina ?? 0);
        }

        // El total final incluye el subtotal con descuento, el IVA y la propina voluntaria
        $total = $subtotalConDescuento + $iva + $propina;

        return view('mesero.precuenta', [
            'mesa'      => $mesa,
            'orden'     => $orden,
            'detalles'  => $detalles,
            'subtotal'  => $subtotal,
            'descuento' => $descuento,
            'iva'       => $iva,
            'propina'   => $propina, // <-- Pasamos la propina a la vista
            'total'     => $total,
            'fecha'     => now(),
        ]);
    }
}