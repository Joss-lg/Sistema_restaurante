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
        'cuenta_dividida',
        'numero_cuenta_division', // El número de personas
        'total_cuentas_division',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'propina' => 'decimal:2',
        'cuenta_dividida' => 'boolean',
        'numero_cuenta_division' => 'integer',
        'abierta_el' => 'datetime',
        'cerrada_el' => 'datetime',
    ];

    // --- RELACIONES ---

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function capitan()
    {
        return $this->belongsTo(User::class, 'capitan_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }

    // --- ACCESORES (Lógica de negocio) ---

    // Calcular total con propina
    public function getTotalConImpuestosAttribute()
    {
        return $this->total + ($this->propina ?? 0);
    }

    // NUEVO: Calcular cuánto paga cada persona automáticamente
    public function getMontoPorPersonaAttribute()
    {
        // Si la cuenta está dividida y hay más de una persona, dividimos
        if ($this->cuenta_dividida && $this->numero_cuenta_division > 1) {
            return $this->total / $this->numero_cuenta_division;
        }

        // Si no está dividida, el monto por persona es el total
        return $this->total;
    }
}