<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orden extends Model
{
    use HasFactory, SoftDeletes;

    // --- CONSTANTES DE ESTADO ---
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en proceso';
    const ESTADO_SERVIDA   = 'servida';
    const ESTADO_PAGADA    = 'pagada';

    /**
     * Retorna los estados que se consideran "activos" o "en servicio".
     * Útil para consultas en el controlador.
     */
    public static function getEstadosActivos(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_EN_PROCESO,
            self::ESTADO_SERVIDA,
        ];
    }

    protected $table = 'ordenes';

    protected $fillable = [
        'numero_orden',
        'mesa_id',
        'mesero_id',
        'capitan_id',
        'estado',
        'total',
        'propina',
        'metodo_pago',
        'abierta_el',
        'cerrada_el',
        'cuenta_dividida',
        'numero_cuenta_division',
        'total_cuentas_division',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'propina' => 'decimal:2',
        'cuenta_dividida' => 'boolean',
        'numero_cuenta_division' => 'integer',
        'abierta_el' => 'datetime',
        'cerrada_el' => 'datetime',
    ];

    // --- RELACIONES ---

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function capitan()
    {
        return $this->belongsTo(User::class, 'capitan_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }

    // --- ACCESORES ---

    public function getTotalConImpuestosAttribute()
    {
        return $this->total + ($this->propina ?? 0);
    }

    public function getMontoPorPersonaAttribute()
    {
        if ($this->cuenta_dividida && $this->numero_cuenta_division > 1) {
            return $this->total / $this->numero_cuenta_division;
        }

        return $this->total;
    }
}