<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Mesa;
use App\Models\Categoria;
use App\Models\Producto;

class ComandaController extends Controller
{
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
        $productos = Producto::with(['categoria', 'modificadores'])->get();
        $mesasAbiertas = collect();
        if ($esCapitan) {
            $mesasAbiertas = Mesa::where('estado', 'ocupada')
                ->orderBy('numero', 'asc')
                ->get();
        }

        return view('mesero.comanda', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan'));
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
                        if ($mesa->mesero_id !== $usuario->id) {
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

                \App\Models\DetalleOrden::create($detalleData);
            }

            $mesa->estado = 'ocupada';
            if (Schema::hasColumn('mesas', 'mesero_id') && is_null($mesa->mesero_id)) {
                $mesa->mesero_id = auth()->id();
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesa->total_consumo = $totalConIva;
            }
            $mesa->save();

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa marcada como ocupada',
                'orden_id' => $orden->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la orden. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifica el NIP del capitán y retorna las mesas abiertas.
     */
    public function verificarCapitan(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:50',
        ]);

        $nip = $request->input('nip');

        // Buscar usuario con ese NIP que tenga rol 'capitan'
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

        // Devolver mesas abiertas
        $mesasAbiertas = Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get(['id', 'numero', 'estado']);

        return response()->json(['success' => true, 'mesas' => $mesasAbiertas]);
    }

    /**
     * API: Retorna mesas abiertas (ocupada) para refrescar UI.
     */
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
            // Mesero normal: solo sus mesas (si existe mesero_id)
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesasForUser = Mesa::where('mesero_id', auth()->id())->orderBy('numero', 'asc')->get();
            } else {
                // Si no hay mesero_id, devolvemos las mesas ocupadas (limitado)
                $mesasForUser = Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get();
            }
        }

        $mesasAbiertas = $mesasForUser->where('estado', 'ocupada')->values();
        $mesasLibres = $mesasForUser->where('estado', 'disponible')->values();

        // Mapear solo campos necesarios y proteger total_consumo
        $mesasAbiertasArr = $mesasAbiertas->map(function ($m) {
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
            'mesas_libres' => $mesasLibres->values(),
            'conteo_abiertas' => $mesasAbiertasArr->count(),
            'conteo_total' => $mesasForUser->count()
        ]);
    }

    // ==========================================
    // NUEVA FUNCIÓN PARA GUARDAR LA MESA
    // ==========================================
    public function storeMesa(Request $request)
    {
        // 1. Validamos que los datos vengan correctamente desde los inputs
        $request->validate([
            'numero' => 'required|string|max:20',
            'capacidad' => 'required|integer|min:1',
            'cuenta_dividida' => 'boolean',
            'total_cuentas_division' => 'nullable|integer|min:2|max:10',
        ]);

        // 2. Buscamos la mesa por número
        $mesa = Mesa::where('numero', $request->numero)->first();

        if ($mesa && $mesa->estado === 'ocupada') {
            return response()->json([
                'success' => false,
                'message' => 'La mesa ya está ocupada. Elige otra mesa o cierra la orden antes de abrir una nueva.'
            ], 409);
        }

        // 3. Creamos o actualizamos la mesa para abrirla
        $mesa = $mesa ?? new Mesa(['numero' => $request->numero]);
        $mesa->capacidad = $request->capacidad;
        $mesa->estado = 'ocupada';
        
        $usuario = auth()->user();
        if (!$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }
        
        if (strtolower(trim($usuario->rol?->slug ?? '')) === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
            $mesa->mesero_id = auth()->id();
        }
        $mesa->save();

        // 4. Si la cuenta está dividida, creamos múltiples órdenes vacías
        if ($request->boolean('cuenta_dividida') && $request->filled('total_cuentas_division')) {
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
        }

        return response()->json([
            'success' => true,
            'message' => 'Mesa abierta correctamente',
            'mesa' => $mesa
        ]);
    }
}