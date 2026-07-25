<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaDivision extends Model
{
    use HasFactory;

    protected $table = 'cuentas_division';

    protected $fillable = [
        'mesa_id',
        'tipo',
        'numero_cuenta',
        'total_partes',
        'subtotal',
        'iva',
        'propina',
        'total',
        'estado',
        'pagada_el',
        'caja_movimiento_id',
    ];

    protected $casts = [
        'subtotal'   => 'decimal:2',
        'iva'        => 'decimal:2',
        'propina'    => 'decimal:2',
        'total'      => 'decimal:2',
        'pagada_el'  => 'datetime',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function cajaMovimiento(): BelongsTo
    {
        return $this->belongsTo(CajaMovimiento::class);
    }

    public function getEstaPagadaAttribute(): bool
    {
        return $this->estado === 'pagada';
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }
}