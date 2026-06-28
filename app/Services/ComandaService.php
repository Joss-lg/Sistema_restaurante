<?php

namespace App\Services;

use App\Models\{Orden, DetalleOrden, MovimientoInventario, Producto, Mesa};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComandaService
{
    public function procesarEnvio(Mesa $mesa, array $platillos, $usuario): Orden
    {
        return DB::transaction(function () use ($mesa, $platillos, $usuario) {
            $orden = Orden::firstOrCreate(
                ['mesa_id' => $mesa->id, 'estado' => 'pendiente'],
                [
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesero_id'    => $usuario->id,
                    'abierta_el'   => now(),
                ]
            );

            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $subtotalNuevo = 0;

            foreach ($platillos as $platillo) {
                // 1. Crear Detalle
                $detalleData = [
                    'orden_id' => $orden->id,
                    'producto_id' => $platillo['id'],
                    'cantidad' => $platillo['cantidad'],
                    'precio_unitario' => $platillo['precio'],
                    'estado' => 'en cocina',
                    'notas' => $platillo['notas'] ?? null,
                ];
                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                DetalleOrden::create($detalleData);

                // 2. Inventario
                $producto = Producto::with('insumos')->find($platillo['id']);
                foreach ($producto->insumos as $insumo) {
                    $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];
                    if ($insumo->stock_actual < $cantidadUsada) {
                        throw new \Exception("Stock insuficiente: {$insumo->nombre}");
                    }
                    $insumo->decrement('stock_actual', $cantidadUsada);
                    MovimientoInventario::create([
                        'insumo_id' => $insumo->id, 'user_id' => $usuario->id,
                        'cantidad' => $cantidadUsada, 'tipo' => 'salida',
                        'motivo' => "Venta: {$producto->nombre}"
                    ]);
                }
                $subtotalNuevo += ($platillo['precio'] * $platillo['cantidad']);
            }

            // 3. Actualizar Mesa
            $mesa->update(['estado' => 'ocupada', 'total_consumo' => ($mesa->total_consumo ?? 0) + ($subtotalNuevo * 1.16)]);

            return $orden;
        });
    }
}