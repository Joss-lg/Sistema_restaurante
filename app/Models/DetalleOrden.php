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
        'producto_id',
        'cantidad',
        'precio_unitario',
        'estado',
        'notas',
        'gramaje',
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

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Calcular subtotal del detalle
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
