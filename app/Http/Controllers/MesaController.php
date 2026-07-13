<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Promocion;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Services\MesaService;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MesaController extends Controller
{
    protected $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    public function index()
    {
        $mesas = $this->mesaService->obtenerMesasParaUsuario(auth()->user());
        return view('admin.mesas.index', compact('mesas'));
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();

        $this->mesaService->verificarAccesoMesa($mesa, $usuario);
        $esCapitan = $this->mesaService->esCapitan($usuario);

        $categorias = Categoria::all();
        $productos = Producto::with(['categoria', 'modificadores'])->orderBy('nombre', 'asc')->get();

        $nombreRol = strtolower(trim($usuario->rol?->nombre ?? ''));
        $esAdmin = $nombreRol === 'administrador';

        $mesasAbiertas = ($esCapitan || $esAdmin)
            ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get()
            : collect();

        $ordenActiva = Orden::where('mesa_id', $mesa->id)
            ->where('estado', '!=', 'pagada')
            ->latest()
            ->first();

        $platillosEnviados = collect();
        if ($ordenActiva) {
            $platillosEnviados = DetalleOrden::where('orden_id', $ordenActiva->id)
                ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                ->select('detalles_orden.*', 'productos.nombre as nombre', 'detalles_orden.precio_unitario as precio')
                ->get();
        }

        return view('mesero.index', compact(
            'mesa', 'categorias', 'productos', 'mesasAbiertas',
            'esCapitan', 'platillosEnviados', 'ordenActiva'
        ));
    }

    public function enviar(Request $request)
    {
        try {
            $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'platillos' => 'required|array|min:1',
                'total' => 'nullable|numeric|min:0',
                'personas' => 'nullable|integer|min:1',
                'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            ]);

            $mesa = Mesa::findOrFail($request->mesa_id);

            $orden = app(ComandaService::class)->procesarEnvio(
                $mesa,
                $request->platillos,
                auth()->user(),
                $request->total ?? 0,
                $request->personas ?? $mesa->capacidad ?? 4,
                $request->descuento_porcentaje ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada correctamente',
                'orden_id' => $orden->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Persiste el número de personas de la cuenta activa al instante,
     * sin esperar a "Enviar Orden". Si aún no hay orden activa para la
     * mesa, crea una en estado pendiente para poder guardarlo desde ya.
     */
    public function actualizarPersonas(Request $request, $mesaId)
    {
        $request->validate(['personas' => 'required|integer|min:1']);

        $mesa = Mesa::findOrFail($mesaId);

        $orden = Orden::where('mesa_id', $mesa->id)
            ->whereIn('estado', Orden::getEstadosActivos())
            ->latest()
            ->first();

        if (!$orden) {
            $orden = Orden::create([
                'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id'      => $mesa->id,
                'mesero_id'    => auth()->id(),
                'estado'       => Orden::ESTADO_PENDIENTE,
                'abierta_el'   => now(),
                'personas'     => $request->personas,
            ]);
        } else {
            $orden->update(['personas' => $request->personas]);
        }

        return response()->json(['success' => true, 'personas' => $orden->personas]);
    }

    /**
     * Devuelve, en JSON, las promociones activas hoy (filtradas por
     * fecha_inicio/fecha_fin y por día de la semana actual usando
     * dias_semana). La usa el modal de Promociones del mesero para
     * pintar las tarjetas y aplicar el descuento automático al ticket.
     *
     * AJUSTE: ahora precargamos la relación productos() y mandamos
     * 'producto_ids' en cada promo. Esto es lo que necesita el JS
     * para poder automatizar el tipo 'combo': sin saber qué productos
     * exactos lo componen, no hay forma de detectar cuándo aplica.
     */
    public function promocionesActivas(): JsonResponse
    {
        try {
            $diaSemana = now()->dayOfWeekIso; // 1 = lunes ... 7 = domingo
            $hoy = now()->toDateString();

            $promos = Promocion::activas()
                ->with('productos:id') // NUEVO: para poder mandar producto_ids
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy);
                })
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
                })
                ->get()
                ->filter(function ($promo) use ($diaSemana) {
                    // Defensivo: algunos registros viejos guardan dias_semana
                    // como JSON doble-codificado (mismo caso que ya manejas
                    // en admin.promociones.index), así que normalizamos aquí
                    // también para no truenar con in_array().
                    $dias = $promo->dias_semana;

                    if (is_string($dias)) {
                        $decoded = json_decode($dias, true);
                        if (is_string($decoded)) {
                            $decoded = json_decode($decoded, true);
                        }
                        $dias = $decoded;
                    }

                    $dias = is_array($dias) ? $dias : [];

                    return empty($dias) || in_array($diaSemana, $dias);
                })
                ->map(function ($promo) {
                    // NUEVO: producto_ids explícito para que el JS pueda
                    // detectar automáticamente cuándo el ticket cumple el combo.
                    return [
                        'id'              => $promo->id,
                        'nombre'          => $promo->nombre,
                        'descripcion'     => $promo->descripcion,
                        'tipo_promocion'  => $promo->tipo_promocion,
                        'valor_descuento' => $promo->valor_descuento,
                        'dias_semana'     => $promo->dias_semana,
                        'producto_ids'    => $promo->productos->pluck('id')->values(),
                    ];
                })
                ->values();

            return response()->json(['success' => true, 'promociones' => $promos]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al obtener promociones activas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener promociones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function abrirMesa(Request $request): JsonResponse
    {
        return $this->mesaService->abrirMesa($request);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:disponible,ocupada,reservada',
            'zona' => 'nullable|string|in:salon,terraza,vip',
            'forma' => 'nullable|string|in:redonda,cuadrada'
        ]);

        $mesa = Mesa::create(array_merge($validated, [
            'posicion_x' => 20, 'posicion_y' => 20, 'ancho' => 60, 'alto' => 60
        ]));

        return response()->json(['success' => true, 'message' => 'Mesa creada', 'mesa' => $mesa], 201);
    }

   public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update($request->validate([
            'numero' => 'sometimes|string|max:20',
            'capacidad' => 'sometimes|integer|min:1',
            'zona' => 'sometimes|string',
            'forma' => 'sometimes|string',
            // Agrega posición por si quieres editarla manualmente también
            'posicion_x' => 'sometimes|integer',
            'posicion_y' => 'sometimes|integer',
        ]));
        return response()->json(['success' => true, 'mesa' => $mesa]);
    }

    public function destroy($id)
    {
        Mesa::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Mesa eliminada']);
    }
    // Método para obtener todas las mesas (para el renderizado inicial de tu JS)
    public function apiIndex(): JsonResponse
    {
        return response()->json(Mesa::all());
    }

    // Método para guardar el plano (recibe el array de mesas movidas)
    public function guardarPlano(Request $request): JsonResponse
    {
        $request->validate([
            'mesas' => 'required|array'
        ]);

        try {
            foreach ($request->mesas as $mesaData) {
                Mesa::where('id', $mesaData['id'])->update([
                    'posicion_x' => $mesaData['posicion_x'],
                    'posicion_y' => $mesaData['posicion_y']
                ]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}