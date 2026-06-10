<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre', 
        'descripcion', 
        'tipo_promocion',
        'valor_descuento', 
        'fecha_inicio', 
        'fecha_fin',
        'dias_semana',
        'esta_activa'
    ];

    /**
     * El casteo de atributos.
     * Hace que coincida el formato JSON de la BD con arreglos nativos de PHP.
     */
    protected $casts = [
        'dias_semana' => 'array',
        'esta_activa' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Relación: Una promoción se aplica a muchos productos.
     */
    public function productos()
    {
        // 'promocion_productos' es el nombre de tu tabla intermedia
        return $this->belongsToMany(Producto::class, 'promocion_productos');
    }
}