<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanoEspacialController extends Controller
{
    /**
     * Renderiza la vista del mapa
     */
    public function index()
    {
        $mesas = Mesa::orderBy('numero', 'asc')->get();
        $zonas = ['salon', 'terraza', 'vip'];

        return view('admin.mesas.plano-espacial', compact('mesas', 'zonas'));
    }

    /**
     * Este endpoint es el que tu JS lee constantemente (polling) o al recargar.
     */
    public function getMesas(Request $request): JsonResponse
    {
        $zona = $request->query('zona');

        // CAMBIO: Se cambió 'mesero:id,name' por 'mesero:id,nombre'
        $query = Mesa::with(['mesero:id,nombre'])
            ->withCount(['ordenes as ordenes_activas_count' => function ($q) {
                $q->where('estado', '!=', 'pagada');
            }])
            ->orderBy('numero', 'asc');

        if ($zona && in_array($zona, ['salon', 'terraza', 'vip'], true)) {
            $query->where('zona', $zona);
        }

        $mesas = $query->get();

        $mesasFormateadas = $mesas->map(function ($mesa) {
            return [
                'id'            => $mesa->id,
                'numero'        => $mesa->numero,
                'capacidad'     => (int)$mesa->capacidad,
                'estado'        => $mesa->estado, 
                'zona'          => $mesa->zona ?? 'salon',
                'forma'         => $mesa->forma ?? 'redonda',
                'posicion_x'    => $mesa->posicion_x !== null ? (float)$mesa->posicion_x : 20,
                'posicion_y'    => $mesa->posicion_y !== null ? (float)$mesa->posicion_y : 20,
                'ancho'         => (int)($mesa->ancho ?? 60),
                'alto'          => (int)($mesa->alto ?? 60),
                'estadoVisual'  => $mesa->estado, 
                // CAMBIO: Se cambió 'mesero?->name' por 'mesero?->nombre'
                'mesero'        => $mesa->mesero?->nombre ?? 'Sin asignar',
                'totalConsumo'  => (float)($mesa->total_consumo ?? 0),
                'ordenesActivas' => (int)$mesa->ordenes_activas_count,
            ];
       });
    
        return response()->json([
            'success' => true,
            'data'    => $mesasFormateadas,
        ]);
    }

    /**
     * Guarda el arrastre/redimensionamiento (Drag & Drop) desde el Plano
     */
    public function guardarPlano(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mesas'            => 'required|array|min:1',
                'mesas.*.id'       => 'required|integer|exists:mesas,id',
                'mesas.*.posicion_x' => 'required|numeric|min:0',
                'mesas.*.posicion_y' => 'required|numeric|min:0',
                'mesas.*.ancho'      => 'nullable|integer|min:30|max:200',
                'mesas.*.alto'       => 'nullable|integer|min:30|max:200',
                'mesas.*.forma'      => 'nullable|in:redonda,cuadrada',
                'mesas.*.zona'       => 'nullable|in:salon,terraza,vip',
            ]);

            DB::transaction(function () use ($validated) {
                foreach ($validated['mesas'] as $mesaData) {
                    $updateData = [
                        'posicion_x' => $mesaData['posicion_x'],
                        'posicion_y' => $mesaData['posicion_y'],
                    ];

                    if (isset($mesaData['ancho']))    $updateData['ancho'] = $mesaData['ancho'];
                    if (isset($mesaData['alto']))     $updateData['alto'] = $mesaData['alto'];
                    if (isset($mesaData['forma']))    $updateData['forma'] = $mesaData['forma'];
                    if (isset($mesaData['zona']))     $updateData['zona'] = $mesaData['zona'];

                    Mesa::where('id', $mesaData['id'])->update($updateData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Plano espacial guardado correctamente.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error en guardarPlano: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al guardar la disposición.',
            ], 500);
        }
    }

    /**
     * Limpia las posiciones para sacarla del mapa sin borrarla de la BD
     */
    public function eliminarDelPlano($id): JsonResponse
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->update([
                'posicion_x' => null,
                'posicion_y' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa removida del plano visual visualmente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover la mesa del mapa.',
            ], 500);
        }
        
    }
public function store(Request $request): JsonResponse
{
    try {
        // 1. Validar los datos recibidos
        $validated = $request->validate([
            'numero'    => 'required|string|max:50|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1|max:20',
            'estado'    => 'required|in:disponible,reservada,limpieza',
        ]);

        // 2. Crear la mesa con valores por defecto para el plano
        $mesa = Mesa::create([
            'numero'     => $validated['numero'],
            'capacidad'  => $validated['capacidad'],
            'estado'     => $validated['estado'],
            'zona'       => 'salon', // Zona por defecto
            'forma'      => 'redonda', // Forma por defecto
            'posicion_x' => 20, // Aparecerá en la esquina superior izquierda
            'posicion_y' => 20,
            'ancho'      => 60, // Tamaño estándar inicial
            'alto'       => 60,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada correctamente.',
            'data'    => $mesa
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación: ' . $e->getMessage(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error al crear mesa: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno al crear la mesa.',
        ], 500);
    }
}

public function update(Request $request, $id)
{
    $mesa = Mesa::findOrFail($id);

    // Solo validamos lo que el usuario realmente edita en el panel de propiedades
    $validated = $request->validate([
        'numero' => 'required|string|max:50',
        'capacidad' => 'required|integer|min:1',
    ]);

    $mesa->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Mesa actualizada correctamente'
    ]);
}


}