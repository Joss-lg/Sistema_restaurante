<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanoEspacialController extends Controller
{
    /**
     * Mostrar el índice del plano espacial
     */
    public function index()
    {
        $mesas = Mesa::with('ordenes')
            ->orderBy('numero', 'asc')
            ->get();

        $zonas = ['salon', 'terraza', 'vip'];

        return view('admin.mesas.plano-espacial', compact('mesas', 'zonas'));
    }

    /**
     * Obtener todas las mesas (API)
     */
    public function getMesas(Request $request): JsonResponse
    {
        $zona = $request->query('zona');

        $query = Mesa::with(['ordenes', 'mesero'])
            ->orderBy('numero', 'asc');

        // Filtrar por zona si se proporciona
        if ($zona && in_array($zona, ['salon', 'terraza', 'vip'])) {
            $query->where('zona', $zona);
        }

        $mesas = $query->get();

        // Mapear datos para el frontend
        $mesasFormateadas = $mesas->map(function ($mesa) {
            return [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'zona' => $mesa->zona ?? 'salon',
                'forma' => $mesa->forma ?? 'redonda',
                'posicion_x' => $mesa->posicion_x ?? 0,
                'posicion_y' => $mesa->posicion_y ?? 0,
                'ancho' => $mesa->ancho ?? 60,
                'alto' => $mesa->alto ?? 60,
                'estadoVisual' => $mesa->estado_visual,
                'mesero' => $mesa->mesero?->name ?? 'Sin asignar',
                'totalConsumo' => $mesa->total_consumo ?? 0,
                'ordenesActivas' => $mesa->ordenes_activas()->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mesasFormateadas,
        ]);
    }

    /**
     * Guardar el plano espacial (actualizar posiciones y propiedades de mesas)
     */
    public function guardarPlano(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mesas' => 'required|array|min:1',
                'mesas.*.id' => 'required|integer|exists:mesas,id',
                'mesas.*.posicion_x' => 'required|numeric|min:0',
                'mesas.*.posicion_y' => 'required|numeric|min:0',
                'mesas.*.ancho' => 'nullable|integer|min:30|max:200',
                'mesas.*.alto' => 'nullable|integer|min:30|max:200',
                'mesas.*.forma' => 'nullable|in:redonda,cuadrada',
                'mesas.*.zona' => 'nullable|in:salon,terraza,vip',
                'mesas.*.numero' => 'nullable|string',
                'mesas.*.capacidad' => 'nullable|integer|min:1|max:20',
            ]);

            // Procesar actualización de cada mesa
            foreach ($validated['mesas'] as $mesaData) {
                $mesa = Mesa::findOrFail($mesaData['id']);

                // Actualizar posiciones
                $mesa->posicion_x = $mesaData['posicion_x'];
                $mesa->posicion_y = $mesaData['posicion_y'];

                // Actualizar dimensiones si se proporcionan
                if (isset($mesaData['ancho'])) {
                    $mesa->ancho = $mesaData['ancho'];
                }
                if (isset($mesaData['alto'])) {
                    $mesa->alto = $mesaData['alto'];
                }

                // Actualizar propiedades si se proporcionan
                if (isset($mesaData['forma'])) {
                    $mesa->forma = $mesaData['forma'];
                }
                if (isset($mesaData['zona'])) {
                    $mesa->zona = $mesaData['zona'];
                }

                // Actualizar datos básicos si se proporcionan
                if (isset($mesaData['numero'])) {
                    $mesa->numero = $mesaData['numero'];
                }
                if (isset($mesaData['capacidad'])) {
                    $mesa->capacidad = $mesaData['capacidad'];
                }

                $mesa->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Plano espacial guardado correctamente.',
                'data' => $validated['mesas'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el plano: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener una mesa específica
     */
    public function getMesa($id): JsonResponse
    {
        try {
            $mesa = Mesa::with(['ordenes', 'mesero'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $mesa->id,
                    'numero' => $mesa->numero,
                    'capacidad' => $mesa->capacidad,
                    'estado' => $mesa->estado,
                    'zona' => $mesa->zona ?? 'salon',
                    'forma' => $mesa->forma ?? 'redonda',
                    'posicion_x' => $mesa->posicion_x ?? 0,
                    'posicion_y' => $mesa->posicion_y ?? 0,
                    'ancho' => $mesa->ancho ?? 60,
                    'alto' => $mesa->alto ?? 60,
                    'mesero' => $mesa->mesero?->name ?? 'Sin asignar',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada.',
            ], 404);
        }
    }

    /**
     * Eliminar una mesa del plano (solo resetear posiciones)
     */
    public function eliminarDelPlano($id): JsonResponse
    {
        try {
            $mesa = Mesa::findOrFail($id);

            // Solo limpiamos las posiciones, no eliminamos la mesa
            $mesa->posicion_x = null;
            $mesa->posicion_y = null;
            $mesa->save();

            return response()->json([
                'success' => true,
                'message' => 'Mesa removida del plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar del plano.',
            ], 500);
        }
    }

    /**
     * Crear nueva mesa en el plano
     */
    public function crearMesa(Request $request): JsonResponse
    {
        try {
            \Log::info('crearMesa() - Iniciando validación', [
                'datos_recibidos' => $request->all(),
            ]);

            $validated = $request->validate([
                'numero' => 'required|string|unique:mesas,numero',
                'capacidad' => 'required|integer|min:1|max:20',
                'estado' => 'nullable|in:disponible,ocupada,reservada,limpieza',
                'zona' => 'nullable|in:salon,terraza,vip',
                'forma' => 'nullable|in:redonda,cuadrada',
                'posicion_x' => 'nullable|numeric|min:0',
                'posicion_y' => 'nullable|numeric|min:0',
                'ancho' => 'nullable|integer|min:30|max:200',
                'alto' => 'nullable|integer|min:30|max:200',
            ]);

            \Log::info('crearMesa() - Validación exitosa', [
                'datos_validados' => $validated,
            ]);

            // Crear mesa
            $mesa = Mesa::create([
                'numero' => $validated['numero'],
                'capacidad' => $validated['capacidad'],
                'estado' => $validated['estado'] ?? 'disponible',
                'zona' => $validated['zona'] ?? 'salon',
                'forma' => $validated['forma'] ?? 'redonda',
                'posicion_x' => $validated['posicion_x'] ?? 20,
                'posicion_y' => $validated['posicion_y'] ?? 20,
                'ancho' => $validated['ancho'] ?? 60,
                'alto' => $validated['alto'] ?? 60,
            ]);

            \Log::info('crearMesa() - Mesa creada en objeto Eloquent', [
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
            ]);

            // TEST: Verificar que la mesa se guardó en la BD
            $mesaEnBD = Mesa::where('numero', $validated['numero'])->withTrashed()->first();
            
            if ($mesaEnBD) {
                \Log::info('crearMesa() - ✓ Mesa CONFIRMADA en BD', [
                    'mesa_id' => $mesaEnBD->id,
                    'numero' => $mesaEnBD->numero,
                    'en_bd' => true,
                ]);
            } else {
                \Log::error('crearMesa() - ✗ Mesa NO encontrada en BD', [
                    'numero' => $validated['numero'],
                    'mesa_id_eloquent' => $mesa->id,
                    'en_bd' => false,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mesa creada exitosamente.',
                'data' => [
                    'id' => $mesa->id,
                    'numero' => $mesa->numero,
                    'capacidad' => $mesa->capacidad,
                    'estado' => $mesa->estado,
                    'zona' => $mesa->zona,
                    'forma' => $mesa->forma,
                    'posicion_x' => $mesa->posicion_x,
                    'posicion_y' => $mesa->posicion_y,
                    'ancho' => $mesa->ancho,
                    'alto' => $mesa->alto,
                    'estadoVisual' => $mesa->estado_visual,
                    'mesero' => 'Sin asignar',
                    'totalConsumo' => 0,
                    'ordenesActivas' => 0,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('crearMesa() - Error de validación', [
                'errores' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('crearMesa() - Excepción', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la mesa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store: Crear mesa desde formulario modal (alias de crearMesa)
     */
    public function store(Request $request): JsonResponse
    {
        // Debug: Registrar la solicitud
        \Log::info('PlanoEspacialController::store() - Recibida solicitud', [
            'metodo' => $request->method(),
            'url' => $request->url(),
            'datos' => $request->all(),
        ]);

        $resultado = $this->crearMesa($request);
        
        // Debug: Registrar el resultado
        \Log::info('PlanoEspacialController::store() - Resultado', [
            'status' => $resultado->getStatusCode(),
            'contenido' => $resultado->getContent(),
        ]);

        return $resultado;
    }

    /**
     * Test: Crear mesa de prueba
     */
    public function testCrearMesa($numero, $capacidad)
    {
        try {
            \Log::info('TEST: Intentando crear mesa', [
                'numero' => $numero,
                'capacidad' => $capacidad,
            ]);

            $mesa = Mesa::create([
                'numero' => $numero,
                'capacidad' => (int)$capacidad,
                'estado' => 'disponible',
                'zona' => 'salon',
                'forma' => 'redonda',
                'posicion_x' => 20,
                'posicion_y' => 20,
                'ancho' => 60,
                'alto' => 60,
            ]);

            \Log::info('TEST: Mesa creada', ['mesa_id' => $mesa->id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Test: Mesa creada con ID ' . $mesa->id,
                'mesa' => $mesa->toArray(),
            ]);
        } catch (\Exception $e) {
            \Log::error('TEST: Error', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
