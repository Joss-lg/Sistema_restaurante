<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropinaMesero extends Model
{
    protected $table = 'propinas_meseros';

    protected $fillable = [
        'caja_movimiento_id', 'orden_id', 'mesa_id', 'mesero_id',
        'metodo_pago', 'monto', 'pagada', 'pagada_el', 'flujo_caja_id',
    ];

    protected $casts = [
        'monto'     => 'decimal:2',
        'pagada'    => 'boolean',
        'pagada_el' => 'datetime',
    ];

    public function cajaMovimiento()
    {
        return $this->belongsTo(CajaMovimiento::class);
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function flujoCaja()
    {
        return $this->belongsTo(FlujoCaja::class, 'flujo_caja_id');
    }

    // Filas de propina pendientes de entregar, útil para el resumen del corte
    public function scopePendientes($query)
    {
        return $query->where('pagada', false);
    }
}