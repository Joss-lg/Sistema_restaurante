<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
 use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::all();
        return view('admin.mesas.index', compact('mesas'));
    }

    public function getMesas(): JsonResponse
    {
        $mesas = Mesa::with('mesero')->get();

        $mesasConEstado = $mesas->map(function ($mesa) {
            $ordenActiva = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->orderByDesc('abierta_el')
                ->first();

            $ordenesActivas = $ordenActiva ? 1 : 0;
            // Si no hay órdenes activas pero el estado es ocupada, cambiar a disponible
            $estadoReal = $mesa->estado;
            if ($ordenesActivas === 0 && $mesa->estado === 'ocupada') {
                // Hay un desfase: la mesa está marcada como ocupada pero no tiene órdenes activas
                // Actualizar el estado en BD
                DB::table('mesas')->where('id', $mesa->id)->update([
                    'estado' => 'disponible',
                    'updated_at' => now(),
                ]);
                $estadoReal = 'disponible';
                
                // Limpiar mesero_id y total_consumo si existen las columnas
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['mesero_id' => null]);
                }
                if (Schema::hasColumn('mesas', 'total_consumo')) {
                    DB::table('mesas')->where('id', $mesa->id)->update(['total_consumo' => 0]);
                }
            }
            
            $ocupada = $estadoReal === 'ocupada' || $ordenesActivas > 0;
            $minutosActiva = 0;
            if ($ordenActiva) {
                $fechaInicio = $ordenActiva->abierta_el ?? $ordenActiva->created_at;
                if ($fechaInicio) {
                    $minutosActiva = now()->diffInMinutes($fechaInicio);
                }
            }

            $zonaValida = in_array($mesa->seccion, ['Salón', 'Terraza', 'VIP']);
            return [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado' => $estadoReal,
                'seccion' => $mesa->seccion,
                'zona' => $zonaValida ? $mesa->seccion : null,
                'fusionada' => $mesa->seccion && !$zonaValida,
                'posicion_x' => $mesa->posicion_x,
                'posicion_y' => $mesa->posicion_y,
                'mesero_nombre' => $mesa->mesero?->nombre ?? 'Mesero Asignado',
                'total_cuenta' => $ordenActiva ? (float) $ordenActiva->total : 0,
                'minutos_activa' => $minutosActiva,
                'ordenes_activas' => $ordenesActivas,
                'ocupada' => $ocupada,
            ];
        });

        return response()->json($mesasConEstado);
    }

    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        $nuevoEstado = $request->input('estado');
        
        $mesa->update(['estado' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la mesa actualizado',
            'mesa' => $mesa
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero,'.$mesa->id,
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|string|in:disponible,ocupada,reservada',
        ]);

        $estado = strtolower($validated['estado']);
        if ($estado === 'libre') {
            $estado = 'disponible';
        }

        $mesa->update([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
            'estado' => $estado,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:disponible,ocupada,reservada',
            'posicion_x' => 'nullable|integer',
            'posicion_y' => 'nullable|integer',
        ]);

        $estado = strtolower($validated['estado'] ?? 'disponible');
        if ($estado === 'libre') {
            $estado = 'disponible';
        }

        $mesa = Mesa::create([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
            'estado' => $estado,
            'posicion_x' => $validated['posicion_x'] ?? null,
            'posicion_y' => $validated['posicion_y'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function updatePosicion(Request $request, $id): JsonResponse
    {
        if (!Schema::hasColumn('mesas', 'posicion_x') || !Schema::hasColumn('mesas', 'posicion_y')) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla mesas no tiene las columnas posicion_x y posicion_y. Ejecuta la migración correspondiente.',
            ], 500);
        }

        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'posicion_x' => 'required|integer',
            'posicion_y' => 'required|integer',
        ]);

        $mesa->update([
            'posicion_x' => $validated['posicion_x'],
            'posicion_y' => $validated['posicion_y'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Posición de la mesa guardada',
            'mesa' => $mesa
        ]);
    }

    public function guardarPosiciones(Request $request): JsonResponse
    {
        if (!Schema::hasColumn('mesas', 'posicion_x') || !Schema::hasColumn('mesas', 'posicion_y')) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla mesas no tiene las columnas posicion_x y posicion_y. Ejecuta la migración correspondiente.',
            ], 500);
        }

        $validated = $request->validate([
            'coordenadas' => 'required|array',
            'coordenadas.*.id' => 'required|integer|exists:mesas,id',
            'coordenadas.*.x' => 'nullable|integer',
            'coordenadas.*.y' => 'nullable|integer',
        ]);

        foreach ($validated['coordenadas'] as $coord) {
            $updateData = [];
            if (array_key_exists('x', $coord)) {
                $updateData['posicion_x'] = $coord['x'];
            }
            if (array_key_exists('y', $coord)) {
                $updateData['posicion_y'] = $coord['y'];
            }
            if (!empty($updateData)) {
                Mesa::where('id', $coord['id'])->update($updateData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Posiciones de las mesas guardadas correctamente',
        ]);
    }

    public function fusionarMesas(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesas' => 'required|array|min:2',
            'mesas.*' => 'required|integer|exists:mesas,id',
        ]);

        $fusionId = 'fusion-' . now()->timestamp . '-' . rand(100, 999);

        Mesa::whereIn('id', $validated['mesas'])->update([
            'seccion' => $fusionId,
            'estado' => 'ocupada',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesas unidas correctamente',
            'fusion_id' => $fusionId,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        Log::warning('=== INICIANDO DESTROY MESA ===', ['mesa_id' => $id]);

        try {
            Log::info('1. Buscando mesa', ['id' => $id]);
            $mesa = Mesa::findOrFail($id);
            Log::info('2. Mesa encontrada', ['mesa_id' => $mesa->id, 'numero' => $mesa->numero]);

            // Verificar si la mesa tiene órdenes activas
            Log::info('3. Verificando órdenes activas');
            $ordenesActivas = DB::table('ordenes')
                ->where('mesa_id', $mesa->id)
                ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
                ->whereNull('deleted_at')
                ->count();

            Log::info('4. Órdenes activas encontradas', ['cantidad' => $ordenesActivas]);

            if ($ordenesActivas > 0) {
                Log::warning('Mesa tiene órdenes activas, no se puede eliminar', [
                    'mesa_id' => $mesa->id,
                    'ordenes_activas' => $ordenesActivas
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la mesa ' . $mesa->numero . ' porque tiene ' . $ordenesActivas . ' orden(es) activa(s). Finaliza el cobro primero.',
                ], 422);
            }

            // Usar transacción para asegurar integridad
            Log::info('5. Iniciando transacción');
            DB::transaction(function () use ($mesa) {
                Log::info('6. Dentro de transacción');
                
                // Obtener todas las órdenes asociadas
                $ordenesId = DB::table('ordenes')
                    ->where('mesa_id', $mesa->id)
                    ->whereNull('deleted_at')
                    ->pluck('id');

                Log::info('7. Órdenes asociadas encontradas', ['cantidad' => $ordenesId->count()]);

                // Eliminar detalles de órdenes (si es necesario)
                if ($ordenesId->isNotEmpty()) {
                    Log::info('8. Eliminando detalles de órdenes');
                    $detallesEliminados = DB::table('detalles_orden')
                        ->whereIn('orden_id', $ordenesId->toArray())
                        ->delete();
                    Log::info('9. Detalles eliminados', ['cantidad' => $detallesEliminados]);

                    // Soft delete las órdenes
                    Log::info('10. Soft-deletando órdenes');
                    $ordenesAfectadas = DB::table('ordenes')
                        ->where('mesa_id', $mesa->id)
                        ->update(['deleted_at' => now()]);
                    Log::info('11. Órdenes soft-deleted', ['cantidad' => $ordenesAfectadas]);
                }

                // Eliminar la mesa (debe ser delete porque Mesa no tiene soft_deletes)
                Log::info('12. Eliminando mesa', ['mesa_id' => $mesa->id]);
                $resultado = $mesa->delete();
                Log::info('13. Resultado del delete', ['resultado' => $resultado]);

                Log::info('14. Mesa eliminada correctamente', [
                    'mesa_id' => $mesa->id,
                    'mesa_numero' => $mesa->numero,
                    'usuario_id' => auth()->id(),
                    'ordenes_asociadas' => $ordenesId->count(),
                ]);
            });

            // Verificar que realmente se eliminó
            Log::info('15. Verificando que mesa fue eliminada');
            $mesaVerifica = Mesa::where('id', $mesa->id)->first();
            Log::info('16. Verificación', ['mesa_existe' => $mesaVerifica !== null]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa ' . $mesa->numero . ' eliminada correctamente.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Mesa no encontrada', ['mesa_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'La mesa no existe.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('ERROR AL ELIMINAR MESA', [
                'mesa_id' => $id,
                'error' => $e->getMessage(),
                'clase_error' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* --- NUEVO MÉTODO PARA LA CAJA (OLLINTEM PRO) --- */
    public function cobrar($id)
    {
        $mesa = Mesa::findOrFail($id);

        // Buscamos la orden activa
        $orden = DB::table('ordenes')
            ->where('mesa_id', $id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('deleted_at')
            ->first();

        if (!$orden) {
            return redirect()->back()->with('error', 'No hay una orden activa para esta mesa.');
        }

        // Lógica de cuenta dividida (verifica que estos nombres existan en tu DB)
        $cuentasDivididas = isset($orden->cuenta_dividida) ? $orden->cuenta_dividida : false;
        $totalCuentasDivision = isset($orden->numero_cuenta_division) ? $orden->numero_cuenta_division : 1;

        // Totales base
        $subtotal = $orden->total ?? 0;
        $iva = $subtotal * 0.16;
        $propina = 0; 
        $totalPagar = $subtotal + $iva;

        // Preparamos la información detallada para cada cuenta si está dividida
        $cuentasDividadasInfo = [];
        if ($cuentasDivididas) {
            for ($i = 1; $i <= $totalCuentasDivision; $i++) {
                $cuentasDividadasInfo[] = [
                    'numero_cuenta' => $i,
                    'subtotal' => $subtotal / $totalCuentasDivision,
                    'iva' => $iva / $totalCuentasDivision,
                    'propina' => $propina / $totalCuentasDivision,
                    'total' => $totalPagar / $totalCuentasDivision,
                    'productos' => DB::table('detalle_ordenes')->where('orden_id', $orden->id)->get()
                ];
            }
        }

        // Productos para el listado lateral
        $productos = DB::table('detalle_ordenes')
            ->where('orden_id', $orden->id)
            ->get();

        return view('admin.caja.cobrar', compact(
            'mesa',
            'orden',
            'productos',
            'cuentasDivididas',
            'totalCuentasDivision',
            'cuentasDividadasInfo',
            'subtotal',
            'iva',
            'propina',
            'totalPagar'
        ));
    }
}