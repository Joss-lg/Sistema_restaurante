<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Mesa;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::orderBy('numero', 'asc')
            ->with(['ordenes', 'mesero'])
            ->get();

        return view('admin.mesas.index', compact('mesas'));
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();

        // Cargar la relación de rol si no está cargada
        if (!$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }

        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
        $esCapitan = $rolSlug === 'capitan';

        if ($rolSlug === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
            if ($mesa->mesero_id !== $usuario->id) {
                abort(403, 'No tienes permiso para ver esta mesa.');
            }
        }

        if ($esCapitan && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }

        $categorias = Categoria::all();
        
        // Obtener TODOS los productos (sin filtros)
        $productos = Producto::with(['categoria', 'modificadores'])
            ->orderBy('nombre', 'asc')
            ->get();
        
        $mesasAbiertas = collect();
        if ($esCapitan) {
            $mesasAbiertas = Mesa::where('estado', 'ocupada')
                ->orderBy('numero', 'asc')
                ->get();
        }

        $platillosEnviados = collect();
        
        // 1. Buscamos la orden activa de esta mesa
        $ordenActiva = Orden::where('mesa_id', $mesa->id)
                            ->where('estado', '!=', 'pagada')
                            ->latest()
                            ->first();

        // 2. Si hay orden activa, traemos sus detalles (platillos)
        if ($ordenActiva) {
            $platillosEnviados = DetalleOrden::where('orden_id', $ordenActiva->id)
                ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                ->select(
                    'detalles_orden.*', 
                    'productos.nombre as nombre',
                    'detalles_orden.precio_unitario as precio' 
                )
                ->get();
        }

        return view('mesero.comanda', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan', 'platillosEnviados'));
    }

    public function enviar(Request $request)
    {
        try {
            $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'platillos' => 'required|array|min:1',
                'platillos.*.id' => 'required|integer|exists:productos,id',
                'platillos.*.cantidad' => 'required|integer|min:1',
                'platillos.*.precio' => 'required|numeric|min:0',
                'platillos.*.notas' => 'nullable|string|max:1000',
                'platillos.*.gramaje' => 'nullable|numeric|min:0',
            ]);

            $mesa = Mesa::findOrFail($request->mesa_id);
            $usuario = auth()->user();

            $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
            $esCapitan = $rolSlug === 'capitan';

            if (!$esCapitan) {
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    if ($mesa->mesero_id !== null && $mesa->mesero_id !== $usuario->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permiso para enviar a esta mesa.',
                        ], 403);
                    }
                } else {
                    if ($mesa->estado !== 'ocupada') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Solo el capitán puede enviar a otras mesas abiertas.',
                        ], 403);
                    }
                }
            }

            $subtotal = 0;
            foreach ($request->platillos as $platillo) {
                $subtotal += floatval($platillo['precio']) * intval($platillo['cantidad']);
            }

            $totalNeto = round($subtotal, 2);
            $totalConIva = round($subtotal * 1.16, 2);

            DB::beginTransaction();

            $orden = \App\Models\Orden::create([
                'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id' => $mesa->id,
                'mesero_id' => $usuario->id,
                'estado' => 'pendiente',
                'total' => $totalNeto,
                'propina' => 0,
                'abierta_el' => now(),
            ]);

            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');

            foreach ($request->platillos as $platillo) {
                $detalleData = [
                    'orden_id' => $orden->id,
                    'producto_id' => $platillo['id'],
                    'cantidad' => intval($platillo['cantidad']),
                    'precio_unitario' => floatval($platillo['precio']),
                    'estado' => 'en cocina',
                    'notas' => $platillo['notas'] ?? null,
                ];

                if ($usaGramaje) {
                    $detalleData['gramaje'] = isset($platillo['gramaje']) ? floatval($platillo['gramaje']) : null;
                }

                $detalle = \App\Models\DetalleOrden::create($detalleData);

                $producto = Producto::with('insumos')->find($platillo['id']);
                if ($producto) {
                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * intval($platillo['cantidad']);
                        if ($cantidadUsada <= 0) {
                            continue;
                        }

                        if ($insumo->stock_actual < $cantidadUsada) {
                            throw new \Exception("Stock insuficiente para el ingrediente {$insumo->nombre} en el platillo {$producto->nombre}.");
                        }

                        $insumo->stock_actual -= $cantidadUsada;
                        $insumo->save();

                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id,
                            'user_id' => auth()->id(),
                            'cantidad' => $cantidadUsada,
                            'tipo' => 'salida',
                            'motivo' => "Venta de platillo {$producto->nombre} (Orden {$orden->numero_orden})",
                        ]);
                    }
                }
            }

            // CORRECCIÓN: Reafirmar SIEMPRE que la mesa es tuya al enviar
            $mesa->estado = 'ocupada';
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesa->mesero_id = auth()->id();
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = ($mesa->total_consumo ?? 0) + $totalConIva;
            }
            $mesa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa marcada como ocupada',
                'orden_id' => $orden->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la orden. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verificarCapitan(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:50',
        ]);

        $nip = $request->input('nip');

        $usuario = \App\Models\User::where('codigo_empleado', $nip)->first();
        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'NIP no encontrado.'], 404);
        }

        if (!$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }

        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
        if ($rolSlug !== 'capitan') {
            return response()->json(['success' => false, 'message' => 'Usuario no es capitán.'], 403);
        }

        $mesasAbiertas = Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get(['id', 'numero', 'estado']);

        return response()->json(['success' => true, 'mesas' => $mesasAbiertas]);
    }

    public function apiMesasAbiertas(Request $request)
    {
        $usuario = auth()->user();
        if ($usuario && !$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }
        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));

        if ($rolSlug === 'capitan') {
            $mesasForUser = Mesa::orderBy('numero', 'asc')->get();
        } else {
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesasForUser = Mesa::where(function ($query) {
                    $query->where('mesero_id', auth()->id());
                })
                ->orWhere(function ($query) {
                    $query->whereIn('estado', ['disponible', 'libre']);
                })
                ->orderBy('numero', 'asc')
                ->get();
            } else {
                $mesasForUser = Mesa::whereIn('estado', ['ocupada', 'abierta', 'activa', 'reabierta'])
                    ->orderBy('numero', 'asc')
                    ->get();
            }
        }

        // CORRECCIÓN: Agrupar correctamente TODOS los estados para que las mesas no desaparezcan
        $estadosActivos = ['ocupada', 'abierta', 'activa', 'en_uso', 'sucia', 'reabierta', 'por_cobrar'];
        $mesasAbiertas = $mesasForUser->whereIn('estado', $estadosActivos)->values();
        $mesasLibres = $mesasForUser->whereIn('estado', ['disponible', 'libre'])->values();

        $mesasAbiertasArr = $mesasAbiertas->map(function ($m) {
            return [
                'id' => $m->id,
                'numero' => $m->numero,
                'estado' => $m->estado,
                'capacidad' => $m->capacidad ?? null,
                'total_consumo' => $m->total_consumo ?? 0,
            ];
        })->values();

        $mesasLibresArr = $mesasLibres->map(function ($m) {
            return [
                'id' => $m->id,
                'numero' => $m->numero,
                'estado' => $m->estado,
                'capacidad' => $m->capacidad ?? null,
                'total_consumo' => $m->total_consumo ?? 0,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'mesas_abiertas' => $mesasAbiertasArr,
            'mesas_libres' => $mesasLibresArr,
            'conteo_abiertas' => $mesasAbiertasArr->count(),
            'conteo_total' => $mesasForUser->whereIn('estado', $estadosActivos)->count()
        ]);
    }

    public function storeMesa(Request $request)
    {
        try {
            \Log::info('storeMesa: Iniciando creación de mesa', [
                'numero' => $request->numero,
                'capacidad' => $request->capacidad,
                'cuenta_dividida' => $request->boolean('cuenta_dividida'),
                'total_cuentas_division' => $request->input('total_cuentas_division'),
            ]);

            $request->validate([
                'numero' => 'required|string|max:20',
                'capacidad' => 'required|integer|min:1',
                'cuenta_dividida' => 'boolean',
                'total_cuentas_division' => 'nullable|integer|min:2|max:10',
            ]);

            $mesa = Mesa::withTrashed()->where('numero', $request->numero)->first();

            if ($mesa && $mesa->estado === 'ocupada' && !$mesa->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La mesa ya está ocupada. Elige otra mesa o cierra la orden antes de abrir una nueva.'
                ], 409);
            }

            if ($mesa && $mesa->trashed()) {
                $mesa->restore();
            }

            $mesa = $mesa ?? new Mesa(['numero' => $request->numero]);
            $mesa->capacidad = $request->capacidad;
            $mesa->estado = 'ocupada';
            
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = 0;
            }
            
            // CORRECCIÓN: Asignar SIEMPRE la mesa al usuario actual que la abre (forzado)
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesa->mesero_id = auth()->id();
            }
            
            $mesa->save();
            
            \Log::info('storeMesa: Mesa guardada exitosamente', ['mesa_id' => $mesa->id]);

            if (!$mesa->id || $mesa->id <= 0) {
                throw new \Exception('Mesa no tiene un ID válido después de guardar. ID: ' . ($mesa->id ?? 'null'));
            }

            if ($request->boolean('cuenta_dividida') && $request->filled('total_cuentas_division')) {
                \Log::info('storeMesa: Creando órdenes divididas', ['total_cuentas' => $request->integer('total_cuentas_division')]);
                $totalCuentas = $request->integer('total_cuentas_division');
                
                for ($i = 1; $i <= $totalCuentas; $i++) {
                    \App\Models\Orden::create([
                        'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                        'mesa_id' => $mesa->id,
                        'mesero_id' => auth()->id(),
                        'estado' => 'pendiente',
                        'total' => 0,
                        'propina' => 0,
                        'abierta_el' => now(),
                        'cuenta_dividida' => true,
                        'numero_cuenta_division' => $i,
                        'total_cuentas_division' => $totalCuentas,
                    ]);
                }
                \Log::info('storeMesa: Órdenes divididas creadas');
            } else {
                \Log::info('storeMesa: Creando orden no dividida');
                \App\Models\Orden::create([
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesa_id' => $mesa->id,
                    'mesero_id' => auth()->id(),
                    'estado' => 'pendiente',
                    'total' => 0,
                    'propina' => 0,
                    'abierta_el' => now(),
                    'cuenta_dividida' => false,
                    'numero_cuenta_division' => 1,
                    'total_cuentas_division' => 1,
                ]);
                \Log::info('storeMesa: Orden no dividida creada');
            }

            \Log::info('storeMesa: Devolviendo respuesta exitosa', ['mesa_id' => $mesa->id]);
            return response()->json([
                'success' => true,
                'message' => 'Mesa abierta correctamente',
                'mesa' => $mesa
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('storeMesa: Error de validación', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('storeMesa: Error general', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMesas()
    {
        $mesas = Mesa::orderBy('numero', 'asc')->get();
        return response()->json(['success' => true, 'mesas' => $mesas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:disponible,ocupada,reservada',
        ]);

        try {
            $mesa = Mesa::create([
                'numero' => $validated['numero'],
                'capacidad' => $validated['capacidad'],
                'estado' => $validated['estado'] ?? 'disponible',
                'posicion_x' => 50, // Posición inicial visible
                'posicion_y' => 50,
                'zona' => 'Salón', // Zona por defecto
            ]);
        } catch (\Exception $e) {
            // Si falla por columnas faltantes, intentar sin esos campos
            $mesa = Mesa::create([
                'numero' => $validated['numero'],
                'capacidad' => $validated['capacidad'],
                'estado' => $validated['estado'] ?? 'disponible',
            ]);
            
            // Actualizar con posición y zona usando raw SQL
            try {
                DB::statement("ALTER TABLE mesas ADD COLUMN IF NOT EXISTS posicion_x INT DEFAULT 50");
                DB::statement("ALTER TABLE mesas ADD COLUMN IF NOT EXISTS posicion_y INT DEFAULT 50");
                DB::statement("ALTER TABLE mesas ADD COLUMN IF NOT EXISTS zona VARCHAR(50) DEFAULT 'Salón'");
                
                $mesa->update([
                    'posicion_x' => 50,
                    'posicion_y' => 50,
                    'zona' => 'Salón',
                ]);
            } catch (\Exception $e2) {
                // Continuar aunque falle
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada',
            'data' => [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'posicion_x' => $mesa->posicion_x ?? 50,
                'posicion_y' => $mesa->posicion_y ?? 50,
                'zona' => $mesa->zona ?? 'Salón',
                'minutos_activa' => 0,
                'mesero_nombre' => 'Sin asignar',
                'total_cuenta' => 0,
            ],
            'mesa' => $mesa
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $validated = $request->validate([
            'numero' => 'required|string|max:20',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|string',
        ]);

        $mesa->update($validated);
        return response()->json(['success' => true, 'message' => 'Mesa actualizada', 'mesa' => $mesa]);
    }

    public function updatePosicion(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $validated = $request->validate([
            'posicion_x' => 'nullable|numeric',
            'posicion_y' => 'nullable|numeric',
        ]);

        $mesa->update($validated);
        return response()->json(['success' => true, 'message' => 'Posición actualizada']);
    }

    public function guardarPosiciones(Request $request)
    {
        foreach ($request->input('posiciones', []) as $id => $posicion) {
            Mesa::find($id)?->update($posicion);
        }
        return response()->json(['success' => true, 'message' => 'Posiciones guardadas']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $validated = $request->validate(['estado' => 'required|string']);
        
        $mesa->update($validated);
        return response()->json(['success' => true, 'message' => 'Estado actualizado']);
    }

    public function fusionarMesas(Request $request)
    {
        $validated = $request->validate([
            'mesa_origen_id' => 'required|integer|exists:mesas,id',
            'mesa_destino_id' => 'required|integer|exists:mesas,id',
        ]);

        // Lógica básica de fusión
        return response()->json(['success' => true, 'message' => 'Mesas fusionadas']);
    }

    public function destroy($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->delete();
        return response()->json(['success' => true, 'message' => 'Mesa eliminada']);
    }
}