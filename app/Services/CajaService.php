<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\CuentaDivision;
use App\Models\DetalleOrden;
use App\Models\Mesa;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CajaService
{
    /**
     * Calcula el desglose de una orden o grupo de órdenes de una mesa.
     */
    public function obtenerDesgloseMesa(Mesa $mesa): array
    {
        $ordenesActivas = $mesa->ordenesActivas()
            ->with([
                'detalles.producto',
                'detalles.promocionAplicada.promocion',
                'promocionesAplicadas.promocion',
                'promocionesAplicadas.detalleOrden.producto',
            ])
            ->get();

        // NUEVO: los productos cancelados nunca deben cobrarse. Se excluyen
        // aquí, en el punto único de verdad que usan cobro, precuenta y propina.
        $subtotalBruto = $ordenesActivas->sum(function ($orden) {
            return $orden->detalles
                ->where('estado', '!=', 'cancelado')
                ->sum(fn($detalle) => $detalle->cantidad * $detalle->precio_unitario);
        });

        $descuentoPromociones = $ordenesActivas->sum(function ($orden) {
            return $orden->promocionesAplicadas
                ->filter(fn($op) => optional($op->detalleOrden)->estado !== 'cancelado')
                ->sum('monto_descuento');
        });

        // Lista de productos con su descuento, para mostrar en la vista
        $productosConDescuento = $ordenesActivas->flatMap(function ($orden) {
            return $orden->promocionesAplicadas
                ->filter(fn($op) => optional($op->detalleOrden)->estado !== 'cancelado')
                ->map(function ($op) {
                    return [
                        'producto'        => $op->detalleOrden->producto->nombre ?? 'Producto',
                        'promocion'       => $op->promocion->nombre ?? 'Promoción',
                        'monto_descuento' => (float) $op->monto_descuento,
                    ];
                });
        })->values();

        $subtotal = round($subtotalBruto - $descuentoPromociones, 2);
        $propina  = $ordenesActivas->sum('propina');

        // --- AJUSTE: IVA habilitable desde configuración global ---
        $ivaHabilitado = Configuracion::ivaHabilitado();
        $ivaPorcentaje = Configuracion::ivaPorcentaje(); // ej. 16

        $iva = $ivaHabilitado
            ? round($subtotal * ($ivaPorcentaje / 100), 2)
            : 0;

        $total = round($subtotal + $iva + $propina, 2);

        $division = $this->obtenerEstadoDivision($mesa);

        return [
            'subtotal'              => $subtotal,
            'subtotalBruto'         => round($subtotalBruto, 2),
            'descuentoPromociones'  => round($descuentoPromociones, 2),
            'productosConDescuento' => $productosConDescuento,
            'iva'                   => $iva,
            'ivaHabilitado'         => $ivaHabilitado,
            'ivaPorcentaje'         => $ivaPorcentaje,
            'propina'               => round($propina, 2),
            'total'                 => $total,
            'ordenes'               => $ordenesActivas,
            // NUEVO: división real de la cuenta (null si la mesa no está dividida)
            'division'              => $division,
            'cuentasDivididas'      => $division['cuentas'] ?? [],
            'totalCuentasDivision'  => $division['total_partes'] ?? 1,
        ];
    }

    /**
     * Devuelve el estado actual de la división de una mesa, o null si no
     * está dividida. Incluye cada "cuenta" (persona) con su monto y, en
     * modo 'por_producto', los productos que le fueron asignados.
     */
    public function obtenerEstadoDivision(Mesa $mesa): ?array
    {
        $cuentas = $mesa->cuentasDivision()->orderBy('numero_cuenta')->get();

        if ($cuentas->isEmpty()) {
            return null;
        }

        $tipo = $cuentas->first()->tipo;

        $productosPorCuenta = [];
        if ($tipo === 'por_producto') {
            $productosPorCuenta = $mesa->ordenesActivas()
                ->with('detalles.producto')
                ->get()
                ->flatMap(fn($orden) => $orden->detalles->where('estado', '!=', 'cancelado'))
                ->groupBy('cuenta_division_numero');
        }

        return [
            'tipo'         => $tipo,
            'total_partes' => (int) $cuentas->first()->total_partes,
            'completa'     => $cuentas->every(fn($c) => $c->estado === 'pagada'),
            'cuentas'      => $cuentas->map(function ($cuenta) use ($tipo, $productosPorCuenta) {
                return [
                    'id'             => $cuenta->id,
                    'numero_cuenta'  => $cuenta->numero_cuenta,
                    'estado_orden'   => $cuenta->estado, // 'pendiente' | 'pagada' (nombre mantenido por compatibilidad con la vista)
                    'subtotal'       => (float) $cuenta->subtotal,
                    'iva'            => (float) $cuenta->iva,
                    'propina'        => (float) $cuenta->propina,
                    'total'          => (float) $cuenta->total,
                    'productos'      => $tipo === 'por_producto'
                        ? ($productosPorCuenta->get($cuenta->numero_cuenta, collect())->values())
                        : [],
                ];
            })->values(),
        ];
    }

    /**
     * Inicia la división de la cuenta de una mesa entre $numPersonas partes.
     *
     * - 'equitativa': el total se reparte en partes iguales (la última
     *   parte absorbe el residuo de redondeo, para que la suma cuadre
     *   siempre con el total real).
     * - 'por_producto': se crean las N partes en $0 a la espera de que
     *   se le vayan asignando productos con asignarProductoAPersona().
     */
    public function iniciarDivision(Mesa $mesa, string $tipo, int $numPersonas): array
    {
        if ($numPersonas < 2) {
            throw new Exception('Se necesitan al menos 2 personas para dividir la cuenta.');
        }

        if (!in_array($tipo, ['equitativa', 'por_producto'], true)) {
            throw new Exception('Tipo de división inválido.');
        }

        return DB::transaction(function () use ($mesa, $tipo, $numPersonas) {
            // Si ya había una división previa (p. ej. el cajero se equivocó
            // y quiere volver a repartir), la limpiamos primero.
            $this->cancelarDivision($mesa);

            $desglose = $this->obtenerDesgloseMesa($mesa);

            if ($tipo === 'equitativa') {
                $subtotalParte = round($desglose['subtotal'] / $numPersonas, 2);
                $ivaParte      = round($desglose['iva'] / $numPersonas, 2);
                $propinaParte  = round($desglose['propina'] / $numPersonas, 2);
                $totalParte    = round($desglose['total'] / $numPersonas, 2);

                for ($i = 1; $i <= $numPersonas; $i++) {
                    $esUltima = $i === $numPersonas;

                    CuentaDivision::create([
                        'mesa_id'       => $mesa->id,
                        'tipo'          => 'equitativa',
                        'numero_cuenta' => $i,
                        'total_partes'  => $numPersonas,
                        // La última parte absorbe el residuo de redondeo
                        'subtotal'      => $esUltima ? round($desglose['subtotal'] - $subtotalParte * ($numPersonas - 1), 2) : $subtotalParte,
                        'iva'           => $esUltima ? round($desglose['iva'] - $ivaParte * ($numPersonas - 1), 2) : $ivaParte,
                        'propina'       => $esUltima ? round($desglose['propina'] - $propinaParte * ($numPersonas - 1), 2) : $propinaParte,
                        'total'         => $esUltima ? round($desglose['total'] - $totalParte * ($numPersonas - 1), 2) : $totalParte,
                        'estado'        => 'pendiente',
                    ]);
                }
            } else {
                // por_producto: se crean las cuentas en $0; se recalculan
                // cada vez que se asigna un producto.
                for ($i = 1; $i <= $numPersonas; $i++) {
                    CuentaDivision::create([
                        'mesa_id'       => $mesa->id,
                        'tipo'          => 'por_producto',
                        'numero_cuenta' => $i,
                        'total_partes'  => $numPersonas,
                        'estado'        => 'pendiente',
                    ]);
                }
            }

            return $this->obtenerEstadoDivision($mesa);
        });
    }

    /**
     * Asigna (o reasigna) un producto de la comanda a una persona, y
     * recalcula el monto de cada cuenta 'por_producto' de la mesa.
     */
    public function asignarProductoAPersona(Mesa $mesa, DetalleOrden $detalle, int $numeroCuenta): array
    {
        return DB::transaction(function () use ($mesa, $detalle, $numeroCuenta) {
            $cuenta = $mesa->cuentasDivision()->where('numero_cuenta', $numeroCuenta)->first();

            if (!$cuenta) {
                throw new Exception('Esa parte de la cuenta no existe.');
            }

            if ($cuenta->tipo !== 'por_producto') {
                throw new Exception('Esta mesa no está dividida por producto.');
            }

            if ($cuenta->estado === 'pagada') {
                throw new Exception('No se puede reasignar un producto a una parte ya pagada.');
            }

            $detalle->update(['cuenta_division_numero' => $numeroCuenta]);

            $this->recalcularCuentasPorProducto($mesa);

            return $this->obtenerEstadoDivision($mesa);
        });
    }

    /**
     * Recalcula subtotal/iva/propina/total de cada cuenta 'por_producto',
     * prorrateando IVA y propina de la mesa según el peso de cada persona
     * en el subtotal (los descuentos por promoción ya vienen aplicados en
     * el precio de línea a través de CajaService::obtenerDesgloseMesa,
     * así que aquí trabajamos directo con precio_unitario * cantidad).
     */
    protected function recalcularCuentasPorProducto(Mesa $mesa): void
    {
        $desgloseMesa = $this->obtenerDesgloseMesa($mesa->fresh());
        $subtotalMesa = $desgloseMesa['subtotal'];

        $detalles = $mesa->ordenesActivas()
            ->with('detalles.promocionAplicada')
            ->get()
            ->flatMap(fn($orden) => $orden->detalles->where('estado', '!=', 'cancelado'));

        $cuentas = $mesa->cuentasDivision()->where('tipo', 'por_producto')->get();
        $numPersonas = $cuentas->count();

        foreach ($cuentas as $cuenta) {
            if ($cuenta->estado === 'pagada') {
                continue; // no tocar lo ya cobrado
            }

            $detallesCuenta = $detalles->where('cuenta_division_numero', $cuenta->numero_cuenta);

            $subtotalCuenta = round($detallesCuenta->sum(function ($d) {
                $descuento = optional($d->promocionAplicada)->monto_descuento ?? 0;
                return ($d->cantidad * $d->precio_unitario) - $descuento;
            }), 2);

            // Prorrateo de IVA y propina según el peso de esta parte en el subtotal total
            $proporcion = $subtotalMesa > 0 ? ($subtotalCuenta / $subtotalMesa) : 0;
            $ivaCuenta = round($desgloseMesa['iva'] * $proporcion, 2);
            $propinaCuenta = round($desgloseMesa['propina'] * $proporcion, 2);

            $cuenta->update([
                'subtotal' => $subtotalCuenta,
                'iva'      => $ivaCuenta,
                'propina'  => $propinaCuenta,
                'total'    => round($subtotalCuenta + $ivaCuenta + $propinaCuenta, 2),
            ]);
        }
    }

    /**
     * Cancela la división activa de una mesa: borra las cuentas y libera
     * los productos que estaban asignados a una persona.
     */
    public function cancelarDivision(Mesa $mesa): void
    {
        DB::transaction(function () use ($mesa) {
            if ($mesa->cuentasDivision()->where('estado', 'pagada')->exists()) {
                throw new Exception('No se puede cancelar la división: ya hay partes pagadas. Debes cobrar o revertir el pago primero.');
            }

            $mesa->ordenesActivas()
                ->get()
                ->each(fn($orden) => $orden->detalles()->update(['cuenta_division_numero' => null]));

            $mesa->cuentasDivision()->delete();
        });
    }

    /**
     * Libera una mesa y limpia sus estados.
     */
    public function liberarMesa(Mesa $mesa): bool
    {
        return DB::transaction(function () use ($mesa) {
            // Pasamos a pagadas todas las órdenes que estaban en proceso en la mesa
            $mesa->ordenes()
                ->whereIn('estado', Orden::getEstadosActivos())
                ->update([
                    'estado'     => Orden::ESTADO_PAGADA,
                    'cerrada_el' => Carbon::now(),
                ]);

            // Reseteamos por completo la mesa para dejarla lista para nuevos clientes
            $mesa->update([
                'estado'        => Mesa::ESTADO_DISPONIBLE,
                'mesero_id'     => null,
                'total_consumo' => 0,
                'updated_at'    => Carbon::now(),
            ]);

            // Limpiamos cualquier división de cuenta: ya está todo cobrado
            // y la mesa queda libre para nuevos comensales.
            $mesa->cuentasDivision()->delete();

            return true;
        });
    }
}