<?php

namespace App\Services;

use App\Models\{Orden, DetalleOrden, MovimientoInventario, Producto, Mesa, Promocion, OrdenPromocion, PrintJob};
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
        float $descuentoPorcentaje = 0,
        bool $permitirSinStock = true // <-- Parámetro con valor por defecto
    ): Orden {
        // IMPORTANTE: Se agrega $permitirSinStock en el "use" de la transacción
        return DB::transaction(function () use ($mesa, $platillos, $usuario, $personas, $descuentoPorcentaje, $permitirSinStock) {
            
            // 1. Buscar orden activa pendiente o crearla
            $orden = Orden::firstOrCreate(
                ['mesa_id' => $mesa->id, 'estado' => 'pendiente'],
                [
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesero_id'    => $usuario->id,
                    'abierta_el'   => now(),
                ]
            );

            $loteEnvio = now()->format('YmdHis') . '-' . rand(1000, 9999);

            $ordenDataUpdate = [];
            if (Schema::hasColumn('ordenes', 'personas')) $ordenDataUpdate['personas'] = $personas;
            if (Schema::hasColumn('ordenes', 'descuento_porcentaje')) $ordenDataUpdate['descuento_porcentaje'] = $descuentoPorcentaje;
            if (!empty($ordenDataUpdate)) {
                $orden->update($ordenDataUpdate);
            }

            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo = Schema::hasColumn('detalles_orden', 'tiempo');

            $productosParaTicket = [];

            // Acumuladores para que el backend sea la fuente de verdad del total
            $totalCalculado = 0.0;
            $totalDescuentosPromos = 0.0;

            foreach ($platillos as $platillo) {
                
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;

                $detalleData = [
                    'orden_id' => $orden->id,
                    'lote_envio' => $loteEnvio,
                    'producto_id' => $platillo['id'],
                    'cantidad' => $platillo['cantidad'],
                    'precio_unitario' => $platillo['precio'],
                    'estado' => 'en cocina',
                    'estado_preparacion' => 'pendiente', // <-- NUEVO: arranca en la cola de Cocina/Barra
                    'notas' => $notasFinales,
                ];

                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                if ($usaTiempo) $detalleData['tiempo'] = $platillo['tiempo'] ?? null;

                $detalle = DetalleOrden::create($detalleData); 

                // Subtotal de esta línea
                $subtotalProducto = $platillo['cantidad'] * $platillo['precio'];

                // Detectar y registrar promociones vigentes para este producto
                $descuentoProducto = 0.0;

                $promocionesAplicables = Promocion::activas()
                    ->whereHas('productos', function ($q) use ($platillo) {
                        $q->where('productos.id', $platillo['id']);
                    })
                    ->get();

                foreach ($promocionesAplicables as $promo) {
                    if (!$promo->aplicaHoy()) {
                        continue;
                    }

                    $montoDescuento = $promo->calcularDescuento($platillo['precio'], $platillo['cantidad']);

                    if ($montoDescuento > 0) {
                        OrdenPromocion::create([
                            'orden_id'         => $orden->id,
                            'promocion_id'     => $promo->id,
                            'detalle_orden_id' => $detalle->id,
                            'monto_descuento'  => $montoDescuento,
                        ]);
                        $descuentoProducto += $montoDescuento;
                    }
                }

                $totalDescuentosPromos += $descuentoProducto;
                $totalCalculado += ($subtotalProducto - $descuentoProducto);

                // 3. Gestión y Deducción del Inventario (Receta por Insumos)
                $producto = Producto::with(['insumos', 'categoria'])->find($platillo['id']);
                if ($producto) {
                    
                    $areaAsignada = $producto->categoria->area_impresion ?? 'Cocina';
                    if ($areaAsignada !== 'Barra') {
                        $areaAsignada = 'Cocina';
                    }

                    $productosParaTicket[] = [
                        'nombre'    => $producto->nombre,
                        'cantidad'  => $platillo['cantidad'],
                        'notas'     => $notasFinales,
                        'area'      => $areaAsignada,
                        'tiempo'    => $platillo['tiempo'] ?? null
                    ];

                    foreach ($producto->insumos as $insumo) {
                        $cantidadUsada = floatval($insumo->pivot->cantidad_usada) * $platillo['cantidad'];
                        
                        // --- AJUSTE AQUÍ: Solo valida stock si $permitirSinStock es false ---
                        if (!$permitirSinStock && $insumo->stock_actual < $cantidadUsada) {
                            throw new \Exception("Stock insuficiente en cocina para preparar: {$insumo->nombre}");
                        }
                        
                        // Si $permitirSinStock es true, pasa de largo y lo descuenta (pudiendo quedar negativo)
                        $insumo->decrement('stock_actual', $cantidadUsada);
                        
                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id, 
                            'user_id'   => $usuario->id,
                            'cantidad'  => $cantidadUsada, 
                            'tipo'      => 'salida',
                            'motivo'    => "Venta POS: {$producto->nombre}"
                        ]);
                    }
                }
            }

            // 4. Actualizar Estado Financiero Global de la Mesa
            $mesaUpdateData = ['estado' => 'ocupada'];
            if (Schema::hasColumn('mesas', 'total_consumo')) {
                $mesaUpdateData['total_consumo'] = ($mesa->total_consumo ?? 0) + $totalCalculado;
            }
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $mesaUpdateData['mesero_id'] = $usuario->id;
            }

            $mesa->update($mesaUpdateData);

            // Reflejamos también el total acumulado directamente en la Orden
            if (Schema::hasColumn('ordenes', 'total')) {
                $orden->increment('total', $totalCalculado);
            }

            // Creamos los tickets separados por área
            $this->crearPrintJobs($orden, $loteEnvio, $productosParaTicket, $mesa, $usuario);

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

            // NUEVO: los productos traspasados (tanto los ya enviados como
            // los nuevos) forman su propia ronda/lote en la orden destino,
            // para que Cocina los vea como una tarjeta separada.
            $loteEnvio = now()->format('YmdHis') . '-' . rand(1000, 9999);

            $montoTransferido = 0;

            // 2. Mover productos ya enviados (sin volver a descontar inventario)
            if (!empty($detalleIds)) {
                $detallesExistentes = DetalleOrden::whereIn('id', $detalleIds)->get();
                foreach ($detallesExistentes as $detalle) {
                    $montoTransferido += $detalle->cantidad * $detalle->precio_unitario;
                }
                DetalleOrden::whereIn('id', $detalleIds)->update([
                    'orden_id' => $ordenDestino->id,
                    'lote_envio' => $loteEnvio, // NUEVO
                    'estado_preparacion' => 'pendiente', // <-- NUEVO: reinicia la cola en la mesa destino
                ]);
            }

            // 3. Crear productos nuevos (del ticket) en la orden destino y descontar inventario
            $usaGramaje = Schema::hasColumn('detalles_orden', 'gramaje');
            $usaTiempo  = Schema::hasColumn('detalles_orden', 'tiempo');

            foreach ($productosNuevos as $platillo) {
                // El tiempo YA NO se anexa a 'notas': tiene su propia columna.
                $notasArray = $platillo['modificadores'] ?? [];
                if (!empty($platillo['notas'])) {
                    $notasArray[] = $platillo['notas'];
                }
                $notasFinales = !empty($notasArray) ? implode(', ', $notasArray) : null;

                $detalleData = [
                    'orden_id'        => $ordenDestino->id,
                    'lote_envio'      => $loteEnvio, // NUEVO
                    'producto_id'     => $platillo['id'],
                    'cantidad'        => $platillo['cantidad'],
                    'precio_unitario' => $platillo['precio'],
                    'estado'          => 'en cocina',
                    'estado_preparacion' => 'pendiente', // <-- NUEVO
                    'notas'           => $notasFinales,
                ];
                if ($usaGramaje) $detalleData['gramaje'] = $platillo['gramaje'] ?? null;
                if ($usaTiempo)  $detalleData['tiempo']  = $platillo['tiempo'] ?? null;

                DetalleOrden::create($detalleData);

                $subtotalProducto = $platillo['cantidad'] * $platillo['precio'];
                $descuentoProducto = 0.0;

                $promocionesAplicables = Promocion::activas()
                    ->whereHas('productos', function ($q) use ($platillo) {
                        $q->where('productos.id', $platillo['id']);
                    })
                    ->get();

                foreach ($promocionesAplicables as $promo) {
                    if (!$promo->aplicaHoy()) {
                        continue;
                    }

                    $montoDescuento = $promo->calcularDescuento($platillo['precio'], $platillo['cantidad']);

                    if ($montoDescuento > 0) {
                        OrdenPromocion::create([
                            'orden_id'        => $ordenDestino->id,
                            'promocion_id'    => $promo->id,
                            'monto_descuento' => $montoDescuento,
                        ]);
                        $descuentoProducto += $montoDescuento;
                    }
                }

                $montoTransferido += $subtotalProducto - $descuentoProducto;
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
     * NUEVO: Agrupa los platillos de este envío por área (Cocina/Barra) y
     * crea un registro pendiente de impresión por cada área. El agente
     * local en cada PC (Cocina/Barra) va a consultar estos registros vía
     * la API y los imprimirá en su impresora local.
     */
    private function crearPrintJobs(
        Orden $orden,
        string $loteEnvio,
        array $productos,
        Mesa $mesa,
        $usuario
    ): void {
        $agrupados = collect($productos)->groupBy('area');

        foreach ($agrupados as $area => $items) {
            $contenido = $this->formatearTicketTexto($area, $mesa, $usuario, $items);

            PrintJob::create([
                'orden_id'    => $orden->id,
                'lote_envio'  => $loteEnvio,
                'area'        => $area,
                'contenido'   => $contenido,
                'estado'      => 'pendiente',
            ]);
        }
    }

    /**
     * NUEVO: Genera el texto plano del ticket. Se manda tal cual al agente
     * local, que a su vez lo imprime por el puerto paralelo (copy /b a la
     * impresora). Usamos texto plano (no ESC/POS binario) porque es lo más
     * simple y confiable de mandar por 'copy /b' a una impresora paralela.
     */
    private function formatearTicketTexto($area, Mesa $mesa, $usuario, $items): string
    {
        $ancho = 32; // ancho típico de una impresora térmica de 58-80mm en modo texto
        $linea = str_repeat('-', $ancho) . "\n";

        $texto  = str_pad('', $ancho, '=', STR_PAD_BOTH) . "\n";
        $texto .= $this->centrarTexto('COMANDA: ' . mb_strtoupper($area), $ancho) . "\n";
        $texto .= str_pad('', $ancho, '=', STR_PAD_BOTH) . "\n";
        $texto .= "Mesa: " . ($mesa->numero ?? $mesa->id) . "\n";
        $texto .= "Mesero: " . ($usuario->nombre ?? $usuario->name ?? 'N/A') . "\n";
        $texto .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
        $texto .= $linea;

        foreach ($items as $item) {
            $texto .= ($item['cantidad'] . 'x ' . $item['nombre']) . "\n";

            if (!empty($item['tiempo']) && $item['tiempo'] !== 'sin-tiempo') {
                $texto .= '   [TIEMPO: ' . mb_strtoupper($item['tiempo']) . "]\n";
            }
            if (!empty($item['notas'])) {
                $texto .= '   * ' . $item['notas'] . "\n";
            }
        }

        $texto .= $linea;
        $texto .= "\n\n\n"; // espacio para el corte manual/avance de papel

        return $texto;
    }

    private function centrarTexto(string $texto, int $ancho): string
    {
        $len = mb_strlen($texto);
        if ($len >= $ancho) return $texto;
        $espacios = intdiv($ancho - $len, 2);
        return str_repeat(' ', $espacios) . $texto;
    }

    /**
     * Lógica interna para conectar con las impresoras Ethernet/IP del restaurante.
     * NOTA: ya no se usa (tus impresoras son por puerto paralelo, no de red),
     * se deja aquí sin llamar por si en el futuro cambian a impresoras IP.
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