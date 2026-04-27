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
        'tiempo_preparacion',
        'esta_disponible'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
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
}