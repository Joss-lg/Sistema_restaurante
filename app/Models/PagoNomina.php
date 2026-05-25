<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PagoNomina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos_nomina';

    protected $fillable = [
        'user_id',
        'periodo',
        'sueldo_base',
        'bonos',
        'deducciones',
        'monto_neto',
        'estado',
        'fecha_pago',
        'metodo_pago',
        'observaciones',
    ];

    protected $casts = [
        'sueldo_base' => 'decimal:2',
        'bonos' => 'decimal:2',
        'deducciones' => 'decimal:2',
        'monto_neto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Relación con el empleado (User)
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    public function scopePorMes($query, $mes, $año)
    {
        return $query->whereMonth('created_at', $mes)
                     ->whereYear('created_at', $año);
    }

    // --- MÉTODOS HELPERS ---

    /**
     * Calcula el monto neto automáticamente
     */
    public static function calcularMontoNeto($sueldoBase, $bonos, $deducciones)
    {
        return $sueldoBase + $bonos - $deducciones;
    }
}
