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
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];
}
