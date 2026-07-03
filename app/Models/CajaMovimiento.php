<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    // Definimos explícitamente el nombre de la tabla
    protected $table = 'caja_movimientos';

    protected $fillable = [
        'user_id',
        'turno', // Agregado para soportar tus etiquetas 'Matutino' / 'Vespertino'
        'estado', // 'abierta' o 'cerrada'
        'monto_inicial',
        'monto_final_esperado',
        'monto_final_real',
        'diferencia',
        'comentarios',
    ];

    // Cambiado a decimal para evitar problemas de precisión matemática con floats
    protected $casts = [
        'monto_inicial'        => 'decimal:2',
        'monto_final_esperado' => 'decimal:2',
        'monto_final_real'     => 'decimal:2',
        'diferencia'           => 'decimal:2',
    ];

    /**
     * Obtener el usuario (mesero/cajero) que operó este turno de caja.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtener todos los movimientos financieros (ingresos/egresos) 
     * que se realizaron durante este turno de caja.
     */
    public function flujos(): HasMany
    {
        return $this->hasMany(FlujoCaja::class, 'caja_movimiento_id');
    }
}