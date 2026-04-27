<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'slug', 'color', 'orden_visualizacion'];

    // Relación: Una categoría tiene muchos insumos en el inventario
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'categoria_id');
    }
}