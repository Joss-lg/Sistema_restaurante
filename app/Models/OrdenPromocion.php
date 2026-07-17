<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenPromocion extends Model
{
    use HasFactory;

    protected $table = 'orden_promociones';

    protected $fillable = [
        'orden_id',
        'promocion_id',
        'detalle_orden_id', // NUEVO
        'monto_descuento',
    ];

    protected $casts = [
        'monto_descuento' => 'decimal:2',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

    public function detalleOrden()
    {
        return $this->belongsTo(DetalleOrden::class, 'detalle_orden_id');
    }
}