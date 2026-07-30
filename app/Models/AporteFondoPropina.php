<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aporte de un mesero al fondo de propinas de barra y cocina.
 *
 * Se guarda uno por mesero y por turno (indice unico), asi que volver a
 * aplicarlo simplemente actualiza el existente en lugar de duplicarlo.
 *
 * Se conservan venta_base y porcentaje ademas del monto a proposito: si
 * manana se cambia el porcentaje general, los aportes ya registrados
 * siguen mostrando con que numeros se calcularon.
 */
class AporteFondoPropina extends Model
{
    protected $table = 'aportes_fondo_propina';

    protected $fillable = [
        'caja_movimiento_id',
        'mesero_id',
        'fecha',
        'venta_base',
        'porcentaje',
        'monto',
        'registrado_por',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'venta_base' => 'decimal:2',
        'porcentaje' => 'decimal:2',
        'monto'      => 'decimal:2',
    ];

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function cajaMovimiento()
    {
        return $this->belongsTo(CajaMovimiento::class, 'caja_movimiento_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}