<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Transaccion extends Model
{
    use HasFactory;

    protected $table = 'transacciones';

    protected $fillable = [
        'orden_id',
        'turno_id',
        'cajero_id',
        'metodo_pago',
        'monto',
        'tipo_division', // <--- Nuevo campo
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'transaccion_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /**
     * Relación polimórfica con FlujoCaja
     */
    public function flujo(): MorphOne
    {
        return $this->morphOne(FlujoCaja::class, 'flujoable');
    }

    // --- SCOPES ---

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopePorCajero($query, $cajeroId)
    {
        return $query->where('cajero_id', $cajeroId);
    }

    public function scopeDelMes($query, $mes = null, $año = null)
    {
        $mes = $mes ?? now()->month;
        $año = $año ?? now()->year;

        return $query->whereMonth('created_at', $mes)
                     ->whereYear('created_at', $año);
    }
}
