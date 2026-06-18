<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = [
        'numero', 
        'capacidad', 
        'estado', 
        'seccion', 
        'posicion_x', 
        'posicion_y',
        'mesero_id',
        'total_consumo',  // ✅ Agregado para permitir asignación
    ];

    // Relación con Mesero
    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    // Relación con Órdenes - Una mesa puede tener múltiples órdenes
    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'mesa_id');
    }

    // Relación con Órdenes activas (no pagadas)
    public function ordenesActivas()
    {
        return $this->ordenes()
            ->whereIn('ordenes.estado', ['pendiente', 'en proceso', 'servida'])
            ->whereNull('ordenes.deleted_at');
    }

    // Accesador para calcular el total dinámicamente con IVA incluido a partir de los detalles reales
    // PERO: Si hay un valor almacenado en la BD, usarlo primero (es más rápido que recalcular)
    public function getTotalConsumoAttribute()
    {
        // ✅ Si existe un valor en BD (no es null o 0), usarlo directamente
        $totalEnBD = $this->attributes['total_consumo'] ?? null;
        if ($totalEnBD !== null && $totalEnBD > 0) {
            return floatval($totalEnBD);
        }

        // Si no hay valor en BD, calcular dinámicamente desde órdenes activas
        $totalDetalles = $this->ordenesActivas()
            ->join('detalles_orden', 'ordenes.id', '=', 'detalles_orden.orden_id')
            ->selectRaw('SUM(detalles_orden.cantidad * detalles_orden.precio_unitario) as total_detalle')
            ->value('total_detalle');

        $totalDetalles = floatval($totalDetalles ?: 0);
        return round($totalDetalles * 1.16, 2);
    }

    // Accesador para obtener todos los detalles de órdenes activas
    public function getProductosAttribute()
    {
        return $this->ordenesActivas()
            ->with('detalles.producto')
            ->get()
            ->flatMap(function ($orden) {
                return $orden->detalles;
            });
    }

    // Accesador para obtener número total de productos pendientes
    public function getNumeroProductosPendientesAttribute()
    {
        return $this->getProductosAttribute()->where('estado', '!=', 'entregado')->count();
    }
}