<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    // Los campos que permitimos guardar desde los formularios
    protected $fillable = [
        'nombre',
        'slug',
        'color',
        'orden_visualizacion'
    ];

    // Le decimos a Laravel qué tipo de dato es cada campo
    protected $casts = [
        'orden_visualizacion' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Una categoría puede tener muchos Alimentos/Productos (Menú)
     * Ej: La categoría "Bebidas" tiene Refresco, Cerveza, Agua.
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Una categoría puede tener muchos Insumos (Materia Prima)
     * Ej: La categoría "Verduras" tiene Jitomate, Cebolla, Cilantro.
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }

    // =========================================================================
    // ACCESORES / AYUDANTES (Opcionales pero muy útiles)
    // =========================================================================

    /**
     * Devuelve el color de la categoría o un gris por defecto si está vacío.
     */
    public function getColorHexAttribute()
    {
        return $this->color ?? '#6B7280'; 
    }
}