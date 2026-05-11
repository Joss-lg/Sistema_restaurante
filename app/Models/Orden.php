<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orden extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes';

    protected $fillable = [
        'numero_orden',
        'mesa_id',
        'mesero_id',
        'capitan_id',
        'estado',
        'total',
        'propina',
        'metodo_pago',
        'abierta_el',
        'cerrada_el',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'propina' => 'decimal:2',
        'abierta_el' => 'datetime',
        'cerrada_el' => 'datetime',
    ];

    // Relación con Mesa
    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    // Relación con Mesero
    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    // Relación con Capitán
    public function capitan()
    {
        return $this->belongsTo(User::class, 'capitan_id');
    }

    // Relación con DetalleOrden
    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }

    // Calcular total con impuestos
    public function getTotalConImpuestosAttribute()
    {
        return $this->total + $this->propina;
    }
}
