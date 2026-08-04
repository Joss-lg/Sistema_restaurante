<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un renglón por venta ticketeada. El id de este modelo ES el folio
 * correlativo (se formatea con ceros a la izquierda al mostrarlo: 1 -> "001").
 *
 * Se crea una sola vez por venta, la primera vez que se genera su ticket
 * (justo después de cobrarla). Reabrir esa misma pantalla despues NO crea
 * un folio nuevo: se detecta por orden_referencia_id y se reutiliza el
 * mismo folio y la misma fecha/hora original.
 */
class TicketImpreso extends Model
{
    protected $table = 'tickets_impresos';

    protected $fillable = [
        'orden_referencia_id',
        'mesa_numero',
        'mesero_id',
        'cajero_id',
        'impreso_en',
    ];

    protected $casts = [
        'impreso_en' => 'datetime',
    ];

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /**
     * Folio con ceros a la izquierda para mostrar en el ticket: 1 -> "001".
     * A partir de 1000 deja de rellenar en vez de truncar el número.
     */
    public function getFolioFormateadoAttribute(): string
    {
        return str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}