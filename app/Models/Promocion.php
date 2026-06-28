<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    // CORREGIDO: Nombres alineados perfectamente con la lógica de tu controlador
    protected $fillable = [
        'nombre', 
        'descripcion', 
        'tipo_promocion',        // Cambiado de 'tipo_promocion' a 'tipo'
        'valor_descuento',       // Cambiado de 'valor_descuento' a 'valor'
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
        // 'promocion_productos' es el nombre de tu tabla intermedia explícita
        return $this->belongsToMany(Producto::class, 'promocion_productos');
    }
}