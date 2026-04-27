<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    // Tenemos que decirle explícitamente a Laravel cómo se llama la tabla, 
    // porque por defecto buscaría "movimiento_inventarios"
    protected $table = 'movimientos_inventario'; 

    protected $fillable = [
        'insumo_id',
        'user_id',
        'cantidad',
        'tipo', // 'entrada', 'salida', 'ajuste', 'venta'
        'motivo'
    ];

    protected $casts = [
        'cantidad' => 'decimal:3', // Usamos 3 decimales como en tu migración (0.150 kg)
    ];

    // Relación: El movimiento pertenece a un Insumo (ej: Tomate)
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    // Relación: El movimiento fue registrado por un Usuario (ej: El Capitán Sebas)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}