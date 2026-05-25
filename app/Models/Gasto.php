<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Gasto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gastos';

    protected $fillable = [
        'concepto',
        'monto',
        'categoria',
        'metodo_pago',
        'estado',
        'fecha',
        'descripcion',
        'documento',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // --- RELACIONES POLIMÓRFICAS ---

    /**
     * Relación polimórfica con FlujoCaja
     */
    public function flujo(): MorphOne
    {
        return $this->morphOne(FlujoCaja::class, 'flujoable');
    }

    // --- SCOPES ---

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorMes($query, $mes, $año)
    {
        return $query->whereMonth('fecha', $mes)
                     ->whereYear('fecha', $año);
    }
}
