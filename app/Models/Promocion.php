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
        'tipo_promocion',
        'valor_descuento',
        'fecha_inicio',
        'fecha_fin',
        'dias_semana',
        'esta_activa',
    ];

    protected $casts = [
        'dias_semana'  => 'array',
        'esta_activa'  => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];


    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'promocion_productos',
            'promocion_id',
            'producto_id'
        )->withTimestamps();
    }

    public function ordenesAplicadas()
    {
        return $this->hasMany(OrdenPromocion::class, 'promocion_id');
    }


    public function getEstadoTextoAttribute()
    {
        return $this->esta_activa ? 'Activa' : 'Inactiva';
    }

    public function getValorFormateadoAttribute()
    {
        return match ($this->tipo_promocion) {

            'porcentaje' =>
                $this->valor_descuento.'%',

            'descuento_fijo' =>
                '$'.number_format($this->valor_descuento,2),

            'dos_por_uno' =>
                '2x1',

            'combo' =>
                'Combo',

            default =>
                '-'
        };
    }


    public function scopeActivas($query)
    {
        return $query->where('esta_activa',true);
    }


    public function aplicaHoy(): bool
    {
        if (!$this->esta_activa) {
            return false;
        }

        $hoy = now();

        if ($this->fecha_inicio && $hoy->lt($this->fecha_inicio)) {
            return false;
        }

        if ($this->fecha_fin && $hoy->gt($this->fecha_fin->copy()->endOfDay())) {
            return false;
        }

        if (!empty($this->dias_semana)) {
            $diasSemana = array_map(fn ($d) => mb_strtolower((string) $d), $this->dias_semana);
            $nombreDia  = mb_strtolower($hoy->locale('es')->isoFormat('dddd'));
            $numeroDia  = (string) $hoy->dayOfWeekIso;

            if (!in_array($nombreDia, $diasSemana) && !in_array($numeroDia, $diasSemana)) {
                return false;
            }
        }

        return true;
    }

    public function calcularDescuento(float $precioUnitario, int $cantidad): float
    {
        $subtotal = $precioUnitario * $cantidad;

        return match ($this->tipo_promocion) {
            'porcentaje'     => round($subtotal * ($this->valor_descuento / 100), 2),
            'descuento_fijo' => (float) min($this->valor_descuento, $subtotal),
            'dos_por_uno'    => round(intdiv($cantidad, 2) * $precioUnitario, 2),
            default          => 0.0,
        };
    }
}