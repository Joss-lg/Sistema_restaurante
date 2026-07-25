<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cuántas UNIDADES de un producto (detalle_orden) le tocan a cada
 * persona cuando la mesa se divide "por consumo". Permite partir la
 * cantidad de un mismo renglón entre varias personas (ej: de 3 pizzas,
 * 2 para la Persona 1 y 1 para la Persona 2).
 */
class DetalleOrdenDivision extends Model
{
    use HasFactory;

    protected $table = 'detalle_orden_divisiones';

    protected $fillable = [
        'detalle_orden_id',
        'numero_cuenta',
        'cantidad',
    ];

    protected $casts = [
        'numero_cuenta' => 'integer',
        'cantidad'      => 'integer',
    ];

    public function detalleOrden(): BelongsTo
    {
        return $this->belongsTo(DetalleOrden::class, 'detalle_orden_id');
    }
}