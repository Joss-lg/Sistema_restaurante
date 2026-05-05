<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modificador extends Model
{
    use HasFactory;

    // Le decimos a Laravel exactamente cómo se llama tu tabla en PostgreSQL
    protected $table = 'modificadores';

    // Los campos que vas a guardar
    protected $fillable = [
        'nombre', 
    ];

    // ==========================================
    // RELACIÓN INVERSA (Buena práctica)
    // ==========================================
    public function productos()
    {
        // Un modificador puede pertenecer a muchos productos a través de la tabla pivote
        // Nota: Asegúrate de que aquí diga Producto::class o Alimento::class, dependiendo de cómo se llame tu modelo principal
        return $this->belongsToMany(Producto::class, 'producto_modificadores');
    }
}