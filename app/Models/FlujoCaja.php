<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlujoCaja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flujo_caja';

    protected $fillable = [
        'caja_movimiento_id', // Enlace directo con el turno operativo
        'tipo',
        'categoria',
        'concepto',
        'monto',
        'metodo_pago',
        'referencia',
        'fecha',
        'flujoable_id',
        'flujoable_type',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2', // Cambiado a decimal para evitar imprecisiones de coma flotante
        'fecha' => 'datetime',
    ];

    /**
     * RELACIÓN: Este movimiento pertenece a un turno/sesión de caja específico.
     */
    public function cajaMovimiento(): BelongsTo
    {
        return $this->belongsTo(CajaMovimiento::class, 'caja_movimiento_id');
    }

    /**
     * RELACIÓN POLIMÓRFICA: Conecta dinámicamente con Pedidos, Gastos, Nóminas, etc.
     */
    public function flujoable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- SCOPES ---

    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeEgresos($query)
    {
        return $query->where('tipo', 'egreso');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopeDelMes($query, $mes = null, $año = null)
    {
        $mes = $mes ?? now()->month;
        $año = $año ?? now()->year;

        return $query->whereMonth('fecha', $mes)
                     ->whereYear('fecha', $año);
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();

        return $query->whereDate('fecha', $fecha);
    }

    public function scopeEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [
            $fechaInicio . ' 00:00:00', 
            $fechaFin . ' 23:59:59'
        ]);
    }

    public function scopeOrdenado($query, $orden = 'desc')
    {
        return $query->orderBy('fecha', $orden);
    }

    // --- MÉTODOS AUXILIARES ---
    
    public function getTipoLegible(): string
    {
        return ucfirst($this->tipo); // Retorna 'Ingreso' o 'Egreso'
    }

    public function getSimboloTipo(): string
    {
        return $this->tipo === 'ingreso' ? '+' : '-';
    }

    public function getMontoConSigno()
    {
        return $this->tipo === 'ingreso' ? $this->monto : -$this->monto;
    }
}