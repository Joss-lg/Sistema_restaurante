<?php
// Reemplazar el método pagar() con este código más simple

public function pagar(Request $request): JsonResponse
{
    try {
        Log::info('=== PAGAR INICIADO ===', ['mesa_id' => $request->mesa_id]);
        
        $validated = $request->validate([
            'mesa_id' => 'required|integer',
            'orden_id' => 'nullable|integer',
            'efectivo' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'iva' => 'nullable|numeric',
            'propina' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
        ]);

        // Obtener mesa
        $mesa = Mesa::find($validated['mesa_id']);
        if (!$mesa) throw new \Exception('Mesa no encontrada');

        // Obtener orden si se proporciona
        $orden = null;
        if (!empty($validated['orden_id'])) {
            $orden = Orden::find($validated['orden_id']);
            if (!$orden) throw new \Exception('Orden no encontrada');
            if ($orden->estado === 'pagada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya fue pagada',
                    'mesa_liberada' => false,
                ], 422);
            }
        }

        // Calcular total
        $detalleTotal = $orden 
            ? DB::table('detalles_orden')->where('orden_id', $orden->id)->sum(DB::raw('cantidad * precio_unitario'))
            : 0;
        
        $ivaTotal = $validated['iva'] ?? round($detalleTotal * 0.16, 2);
        $propinaTotal = $validated['propina'] ?? 0;
        $descuento = $validated['descuento'] ?? 0;
        $total = round($detalleTotal + $ivaTotal + $propinaTotal - $descuento, 2);

        Log::info('Total calculado', ['total' => $total, 'efectivo' => $validated['efectivo']]);

        // TRANSACCIÓN
        DB::transaction(function () use ($orden, $mesa, $validated, $ivaTotal, $propinaTotal, $total) {
            if ($orden) {
                $updated = DB::table('ordenes')
                    ->where('id', $orden->id)
                    ->where('estado', '!=', 'pagada')
                    ->update([
                        'estado' => 'pagada',
                        'metodo_pago' => $validated['metodo_pago'],
                        'propina' => $propinaTotal,
                        'cerrada_el' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \Exception('No se pudo actualizar la orden');
                }
            }

            // Registrar movimiento
            if (Schema::hasTable('caja_movimientos')) {
                CajaMovimiento::create([
                    'concepto' => 'Pago de mesa ' . $mesa->numero,
                    'monto' => $total,
                    'tipo' => 'Ingreso',
                    'responsable' => auth()->user()?->nombre ?? 'Sistema',
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'Completado',
                ]);
            }
        });

        // Verificar si liberar
        $ordenesActivas = DB::table('ordenes')
            ->where('mesa_id', $mesa->id)
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->count();

        $mesaLiberada = false;
        if ($ordenesActivas === 0) {
            DB::table('mesas')->where('id', $mesa->id)->update(['estado' => 'disponible']);
            $mesaLiberada = true;
        }

        Log::info('✅ PAGO EXITOSO', ['mesa_liberada' => $mesaLiberada]);

        return response()->json([
            'success' => true,
            'message' => 'Pago procesado',
            'mesa_liberada' => $mesaLiberada,
            'estado_mesa_final' => $mesaLiberada ? 'disponible' : 'ocupada',
        ]);

    } catch (\Exception $e) {
        Log::error('❌ ERROR PAGAR', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'mesa_liberada' => false,
        ], 422);
    }
}
