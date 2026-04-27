<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Usamos 'Pivot' en lugar de 'Model' porque es una tabla intermedia
class Receta extends Pivot 
{
    protected $table = 'recetas';

    protected $fillable = [
        'producto_id',
        'insumo_id',
        'cantidad_usada'
    ];

    protected $casts = [
        'cantidad_usada' => 'decimal:3', // 3 decimales exactos como en tu migración
    ];
}