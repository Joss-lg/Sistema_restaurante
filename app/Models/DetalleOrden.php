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
        'lote_envio',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'estado',
        'estado_preparacion',
        'notas',
        'gramaje',
        'tiempo',
        'transaccion_id',
        'cancelado_motivo',
        'cancelado_por',
        'cancelado_en',
        'cuenta_division_numero',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'cancelado_en' => 'datetime',
        'cuenta_division_numero' => 'integer',
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

    // NUEVO: quién autorizó la cancelación (Capitán/Admin)
    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    // NUEVO: scope para excluir cancelados en cualquier consulta
    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'cancelado');
    }

    // NUEVO: helper de conveniencia
    public function getEstaCanceladoAttribute(): bool
    {
        return $this->estado === 'cancelado';
    }

    // Calcular subtotal del detalle
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}