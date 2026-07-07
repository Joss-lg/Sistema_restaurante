<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    // --- Definición de Constantes ---
    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_OCUPADA = 'ocupada';

    protected $table = 'mesas';

    protected $fillable = [
        'numero', 
        'capacidad', 
        'estado', 
        'seccion', 
        'zona',
        'forma',
        'posicion_x', 
        'posicion_y',
        'ancho',
        'alto',
        'mesero_id',
        'total_consumo',
    ];

    // Relación con Mesero
    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    // Relación con Órdenes
    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'mesa_id');
    }

    // Relación con Órdenes activas (usando las constantes de Orden si es posible)
    public function ordenesActivas()
    {
        return $this->ordenes()
            ->whereIn('ordenes.estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('ordenes.deleted_at');
    }

    public function getTotalConsumoAttribute()
    {
        // Si ya tienes la relación cargada en el controlador, úsala de la memoria
        $total = $this->ordenesActivas->sum(function($orden) {
            return $orden->detalles->sum(function($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
        });

        return round($total * 1.16, 2);
    }

    public function getProductosAttribute()
    {
        return $this->ordenesActivas()
            ->with('detalles.producto')
            ->get()
            ->flatMap(function ($orden) {
                return $orden->detalles;
            });
    }

    public function getNumeroProductosPendientesAttribute()
    {
        return $this->getProductosAttribute()->where('estado', '!=', 'entregado')->count();
    }

    // --- Método actualizado con la constante ---
    public function getEstadoVisualAttribute()
    {
        // Usamos la constante en lugar del string "duro"
        if ($this->estado === self::ESTADO_DISPONIBLE) {
            return 'blue'; 
        }

        $ordenActiva = $this->ordenesActivas()->latest()->first();
        if (!$ordenActiva) {
            return 'blue';
        }

        $tiempoDesdeCreacion = now()->diffInMinutes($ordenActiva->created_at);
        
        if ($tiempoDesdeCreacion < 30) {
            return 'blue'; 
        } elseif ($tiempoDesdeCreacion < 60) {
            return 'yellow'; 
        } else {
            return 'red'; 
        }
    }
}