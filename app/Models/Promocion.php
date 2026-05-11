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
        'valor_descuento', 
        'fecha_inicio', 
        'fecha_fin',
        'esta_activa'
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