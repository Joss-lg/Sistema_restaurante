<?php

namespace App\Services;

use App\Models\{Orden, DetalleOrden, MovimientoInventario, Producto, Mesa};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Exception;

class ComandaService
{
    /**
     * Procesa el envío de platillos a cocina, descuenta inventario y actualiza totales.
     */
    public function procesarEnvio(
        Mesa $mesa, 
        array $platillos, 
        $usuario, 
        float $totalGeneral = 0, 
        int $personas = 4, 
        float $descuentoPorcentaje = 0
    ): Orden {
        return DB::transaction(function () use ($mesa, $platillos, $usuario, $totalGeneral, $personas, $descuentoPorcentaje) {
            
            // 1. Buscar orden activa pendiente o crearla
            $orden = Orden::firstOrCreate(
                ['mesa_id' => $mesa->id, 'estado' => 'pendiente'],
                [
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesero_id'    => $usuario->id,
                    'abierta_el'   => now(),
                ]
            );

            // Actualizamos los metadatos dinámicos calculados en el POS
            $ordenDataUpdate = [];
            if (Schema::hasColumn('ordenes', 'personas')) $ordenDataUpdate['personas'] = $personas;
            if (Schema::hasColumn('ordenes', 'descuento_porcentaje')) $ordenDataUpdate['descuento_porcentaje'] = $descuentoPorcentaje;
            if (!empty($ordenDataUpdate)) {
                $orden->update($ordenDataUpdate);
            }

            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo = Schema::hasColumn('detalles_orden', 'tiempo');

            // Array temporal para recopilar los datos listos para el formato de ticket
            $productosParaTicket = [];

            foreach ($platillos as $platillo) {
                
                // Mapeo e integración de notas combinadas (Modificadores + Nota de teclado)
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                
                // Si la base no soporta columna de tiempos por separado, lo anexamos estéticamente a las notas
                if (!$usaTiempo && !empty($platillo['tiempo'])) {
                    $notasArray[] = "Tiempo: " . strtoupper($platillo['tiempo']);
                }

                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;

                // 2. Crear Registro del Detalle
                $detalleData = [
                    'orden_id' => $orden->id,
                    'producto_id' => $platillo['id'],
                    'cantidad' => $platillo['cantidad'],
                    'precio_unitario' => $platillo['precio'],
                    'estado' => 'en cocina',
                    'notas' => $notasFinales,
                ];

                if ($usaGramaje) {
                    $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                }
                if ($usaTiempo) {
                    $detalleData['tiempo'] = $platillo['tiempo'] ?? null;
                }

                DetalleOrden::create($detalleData);

                // 3. Gestión y Deducción del Inventario (Receta por Insumos)
                // Cargamos de una vez la categoría para usarla en la impresión
                $producto = Producto::with(['insumos', 'categoria'])->find($platillo['id']);
                if ($producto) {
                    
                    // FILTRO DE SEGURIDAD: Si el área no es Barra, forzamos que sea Cocina 
                    // (Esto reasigna automáticamente cualquier producto viejo de Parrilla)
                    $areaAsignada = $producto->categoria->area_impresion ?? 'Cocina';
                    if ($areaAsignada !== 'Barra') {
                        $areaAsignada = 'Cocina';
                    }

                    // Almacenamos en nuestro array lo necesario para imprimir
                    $productosParaTicket[] = [
                        'nombre'    => $producto->nombre,
                        'cantidad'  => $platillo['cantidad'],
                        'notas'     => $notasFinales,
                        'area'      => $areaAsignada,
                        'tiempo'    => $platillo['tiempo'] ?? null
                    ];

                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];
                        
                        if ($insumo->stock_actual < $cantidadUsada) {
                            throw new \Exception("Stock insuficiente en cocina para preparar: {$insumo->nombre}");
                        }
                        
                        $insumo->decrement('stock_actual', $cantidadUsada);
                        
                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id, 
                            'user_id' => $usuario->id,
                            'cantidad' => $cantidadUsada, 
                            'tipo' => 'salida',
                            'motivo' => "Venta POS: {$producto->nombre}"
                        ]);
                    }
                }
            }

            // 4. Actualizar Estado Financiero Global de la Mesa
            $mesaUpdateData = ['estado' => 'ocupada'];
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesaUpdateData['total_consumo'] = ($mesa->total_consumo ?? 0) + $totalGeneral;
            }
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesaUpdateData['mesero_id'] = $usuario->id;
            }

            $mesa->update($mesaUpdateData);

            // 5. ¡DISPARAR LA IMPRESIÓN POR RED AUTOMÁTICA!
            // Lo ejecutamos de forma segura para que si falla una impresora, no arruine el guardado del pedido
            //$this->enviarImpresionRed($productosParaTicket, $mesa->nombre ?? $mesa->id, $usuario->name ?? 'Mesero');

            return $orden;
        });
    }

    /**
     * Traspasa productos de una mesa origen a una mesa destino.
     * - $detalleIds: DetalleOrden ya enviados a cocina (solo se reasignan, sin re-descontar inventario).
     * - $productosNuevos: platillos del ticket aún no enviados (se crean y sí se descuenta inventario).
     */
    public function transferirProductos(
        Mesa $mesaOrigen,
        Mesa $mesaDestino,
        array $productosNuevos,
        array $detalleIds,
        $usuario
    ): array {
        return DB::transaction(function () use ($mesaOrigen, $mesaDestino, $productosNuevos, $detalleIds, $usuario) {

            // 1. Buscar orden activa de la mesa destino, o crear una nueva pendiente
            $ordenDestino = Orden::where('mesa_id', $mesaDestino->id)
                ->whereIn('estado', Orden::getEstadosActivos())
                ->latest()
                ->first();

            if (!$ordenDestino) {
                $ordenDestino = Orden::create([
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesa_id'      => $mesaDestino->id,
                    'mesero_id'    => $usuario->id,
                    'estado'       => Orden::ESTADO_PENDIENTE,
                    'abierta_el'   => now(),
                ]);
            }

            $montoTransferido = 0;

            // 2. Mover productos ya enviados (sin volver a descontar inventario)
            if (!empty($detalleIds)) {
                $detallesExistentes = DetalleOrden::whereIn('id', $detalleIds)->get();
                foreach ($detallesExistentes as $detalle) {
                    $montoTransferido += $detalle->cantidad * $detalle->precio_unitario;
                }
                DetalleOrden::whereIn('id', $detalleIds)->update(['orden_id' => $ordenDestino->id]);
            }

            // 3. Crear productos nuevos (del ticket) en la orden destino y descontar inventario
            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo  = Schema::hasColumn('detalles_orden', 'tiempo');

            foreach ($productosNuevos as $platillo) {
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                if (!$usaTiempo && !empty($platillo['tiempo'])) {
                    $notasArray[] = "Tiempo: " . strtoupper($platillo['tiempo']);
                }
                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;

                $detalleData = [
                    'orden_id'        => $ordenDestino->id,
                    'producto_id'     => $platillo['id'],
                    'cantidad'        => $platillo['cantidad'],
                    'precio_unitario' => $platillo['precio'],
                    'estado'          => 'en cocina',
                    'notas'           => $notasFinales,
                ];
                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                if ($usaTiempo)  $detalleData['tiempo']  = $platillo['tiempo'] ?? null;

                DetalleOrden::create($detalleData);
                $montoTransferido += $platillo['cantidad'] * $platillo['precio'];

                $producto = Producto::with('insumos')->find($platillo['id']);
                if ($producto) {
                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];

                        if ($insumo->stock_actual < $cantidadUsada) {
                            throw new Exception("Stock insuficiente para preparar: {$insumo->nombre}");
                        }

                        $insumo->decrement('stock_actual', $cantidadUsada);

                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id,
                            'user_id'   => $usuario->id,
                            'cantidad'  => $cantidadUsada,
                            'tipo'      => 'salida',
                            'motivo'    => "Traspaso POS: {$producto->nombre}",
                        ]);
                    }
                }
            }

            // 4. Actualizar mesa destino (la abre automáticamente si estaba disponible)
            $destinoUpdate = ['estado' => 'ocupada'];
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $destinoUpdate['mesero_id'] = $usuario->id;
            }
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $destinoUpdate['total_consumo'] = ($mesaDestino->total_consumo ?? 0) + $montoTransferido;
            }
            $mesaDestino->update($destinoUpdate);

            // 5. Descontar el monto transferido de la mesa origen
            // (nota: este monto es sin IVA/descuento; puede haber una pequeña
            // diferencia frente a total_consumo original, que sí incluye esos ajustes)
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesaOrigen->update([
                    'total_consumo' => max(0, ($mesaOrigen->total_consumo ?? 0) - $montoTransferido),
                ]);
            }

            return [
                'orden_destino'     => $ordenDestino,
                'monto_transferido' => $montoTransferido,
            ];
        });
    }

    /**
     * Lógica interna para conectar con las impresoras Ethernet/IP del restaurante
     */
    private function enviarImpresionRed(array $productos, $nombreMesa, $nombreMesero)
    {
        // ASIGNA AQUÍ LAS DIRECCIONES IP REALES DE TU RESTAURANTE (Parrilla eliminada)
        $impresorasIps = [
            'Cocina'   => '192.168.1.200',
            'Barra'    => '192.168.1.201',
        ];

        // Agrupamos el array de productos por su área asignada automáticamente
        $comandasAgrupadas = collect($productos)->groupBy('area');

        foreach ($comandasAgrupadas as $area => $items) {
            try {
                $ip = $impresorasIps[$area] ?? $impresorasIps['Cocina'];
                
                // Conectamos directo por puerto de red estándar 9100
                $connector = new NetworkPrintConnector($ip, 9100);
                $printer = new Printer($connector);

                // --- Cabecera del Ticket ---
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
                $printer->text("COMANDA: " . strtoupper($area) . "\n");
                $printer->selectPrintMode();
                $printer->text("--------------------------------\n");
                
                // --- Datos de Control ---
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Mesa: " . $nombreMesa . "\n");
                $printer->text("Mesero: " . $nombreMesero . "\n");
                $printer->text("Fecha: " . now()->format('d/m/Y h:i A') . "\n");
                $printer->text("--------------------------------\n");

                // --- Cuerpo del Pedido ---
                $printer->setEmphasis(true);
                $printer->text(str_pad("Cant.", 6) . "Producto\n");
                $printer->setEmphasis(false);
                $printer->text("--------------------------------\n");

                foreach ($items as $item) {
                    $cantStr = $item['cantidad'] . "x";
                    $printer->text(str_pad($cantStr, 6) . $item['nombre'] . "\n");
                    
                    if (!empty($item['tiempo'])) {
                        $printer->text("   [Tiempo: " . strtoupper($item['tiempo']) . "]\n");
                    }
                    if (!empty($item['notas'])) {
                        $printer->text("   * Ojo: " . $item['notas'] . "\n");
                    }
                }

                $printer->text("--------------------------------\n\n\n");
                
                // Corte de papel e instrucciones finales
                $printer->cut();
                $printer->close();

            } catch (Exception $e) {
                // Si la impresora de la Barra está desconectada, se salta y continúa imprimiendo la Cocina
                \Log::error("No se pudo imprimir en el área {$area} (IP: {$ip}): " . $e->getMessage());
            }
        }
    }
}