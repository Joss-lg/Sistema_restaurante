<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaMovimiento extends Model
{
    // Definimos explícitamente el nombre de la tabla por si Laravel intenta buscar "caja_movimientos"
    protected $table = 'caja_movimientos';

    protected $fillable = [
        'user_id',
        'accion',
        'monto_inicial',
        'monto_final_esperado',
        'monto_final_real',
        'diferencia',
        'estado',
        'comentarios',
    ];

    protected $casts = [
        'monto_inicial'        => 'float',
        'monto_final_esperado' => 'float',
        'monto_final_real'     => 'float',
        'diferencia'           => 'float',
    ];

    /**
     * Obtener el usuario (mesero/cajero) que abrió o cerró esta caja.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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