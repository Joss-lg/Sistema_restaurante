<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FlujoCaja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flujo_caja';

    protected $fillable = [
        'tipo',
        'categoria',
        'concepto',
        'monto',
        'metodo_pago',
        'fecha',
        'flujoable_id',
        'flujoable_type',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha' => 'datetime',
    ];

    // --- RELACIONES POLIMÓRFICAS ---

    /**
     * Relación polimórfica genérica
     * Puede relacionarse con Transaccion, Gasto, PagoNomina, etc.
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
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopeOrdenado($query, $orden = 'desc')
    {
        return $query->orderBy('fecha', $orden);
    }

    // --- MÉTODOS HELPERS ---

    /**
     * Obtiene el nombre legible del tipo
     */
    public function getTipoLegible(): string
    {
        return $this->tipo === 'ingreso' ? 'Ingreso' : 'Egreso';
    }

    /**
     * Obtiene el símbolo del tipo (+ para ingreso, - para egreso)
     */
    public function getSimboloTipo(): string
    {
        return $this->tipo === 'ingreso' ? '+' : '-';
    }

    /**
     * Calcula el monto con signo según el tipo
     */
    public function getMontoConSigno(): float
    {
        return $this->tipo === 'ingreso' ? $this->monto : -$this->monto;
    }
}
