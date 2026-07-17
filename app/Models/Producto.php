<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'precio',
        'se_vende_por_peso',
        'precio_por_100g',
        'descripcion',
        'esta_disponible'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'se_vende_por_peso' => 'boolean',
        'precio_por_100g' => 'decimal:2',
        'tiempo_preparacion' => 'integer',
        'esta_disponible' => 'boolean',
    ];

    // A qué categoría del menú pertenece (Ej: Postres)
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // LA MAGIA: Qué ingredientes lleva este platillo
    // Se conecta a Insumo pasando por la tabla pivote 'recetas'
    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'recetas', 'producto_id', 'insumo_id')
                    ->withPivot('cantidad_usada') // Trae la columna extra de la tabla pivote
                    ->withTimestamps();
    }

    public function modificadores()
    {
        // Conecta el Producto con los Modificadores pasando por tu tabla pivote 'producto_modificadores'
        return $this->belongsToMany(Modificador::class, 'producto_modificadores');
    }
    public function promociones()
    {
        return $this->belongsToMany(Promocion::class, 'promocion_productos');
    }
}