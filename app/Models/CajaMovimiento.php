<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    protected $fillable = [
        'concepto',
        'monto',
        'tipo',
        'responsable',
        'comentarios',
        'estado',
        'metodo_pago',
        'referencia',
        'comprobante',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];
}
