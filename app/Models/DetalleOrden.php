<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden';

    protected $fillable = [
        'orden_id',
        'lote_envio', // NUEVO: identifica la "ronda" de envío a cocina dentro de la misma Orden
        'producto_id',
        'cantidad',
        'precio_unitario',
        'estado',
        'estado_preparacion', // NUEVO: pendiente / en proceso / servida — independiente por lote+área
        'notas',
        'gramaje',
        'tiempo', // NUEVO: tiempo de cocina (sin-tiempo, primer-tiempo, segundo-tiempo, tercer-tiempo)
        'transaccion_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
    ];

    // Relación con Orden
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function transaccion()
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id');
    }

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function promocionAplicada()
    {
        return $this->hasOne(OrdenPromocion::class, 'detalle_orden_id');
    }

    // Calcular subtotal del detalle
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}