<?php

namespace App\Services;

use App\Models\Orden;
use App\Models\Transaccion;
use App\Models\DetalleOrden;
use Illuminate\Support\Facades\DB;
use Exception;

class DivisionCuentaService
{
    public function procesarPago(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orden = Orden::findOrFail($data['orden_id']);
            
            // 1. Validar saldo
            $pendiente = $this->calcularSaldoPendiente($orden);
            if ($data['monto'] > $pendiente) {
                throw new Exception("El monto excede el saldo pendiente de la mesa.");
            }

            // 2. Crear Transacción
            $transaccion = Transaccion::create([
                'orden_id'      => $orden->id,
                'monto'         => $data['monto'],
                'tipo_division' => $data['tipo_division'],
                'turno_id'      => session('turno_id'),
                'cajero_id'     => auth()->id(),
                'metodo_pago'   => $data['metodo_pago'] ?? 'efectivo',
            ]);

            // 3. Lógica por producto
            if ($data['tipo_division'] === 'por_producto') {
                DetalleOrden::whereIn('id', $data['detalles_ids'])
                    ->update(['transaccion_id' => $transaccion->id]);
            }

            // 4. Marcar como pagada si el saldo llega a cero
            if (($pendiente - $data['monto']) <= 0) {
                $orden->update(['estado' => Orden::ESTADO_PAGADA]);
            }

            return $transaccion;
        });
    }

    public function calcularSaldoPendiente(Orden $orden): float
    {
        $pagado = Transaccion::where('orden_id', $orden->id)->sum('monto');
        return (float) ($orden->total - $pagado);
    }
}