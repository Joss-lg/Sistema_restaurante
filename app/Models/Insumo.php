<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos'; // Opcional, pero buena práctica

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'precio_compra',
        'esta_activo'
    ];

    // Para que los números se traten como decimales y los booleanos como true/false
    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'esta_activo' => 'boolean',
    ];

    // Relación con Categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Relación con Movimientos
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'insumo_id');
    }

    // Método de utilidad: ¿Hay que hacer pedido de este insumo?
    public function getRequiereReabastecimientoAttribute()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
        // En qué platillos se utiliza este ingrediente
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'recetas', 'insumo_id', 'producto_id')
                    ->withPivot('cantidad_usada')
                    ->withTimestamps();
    }
}