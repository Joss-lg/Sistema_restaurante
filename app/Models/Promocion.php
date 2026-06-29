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

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'promocion_productos',
            'promocion_id',
            'producto_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivas($query)
    {
        return $query->where('esta_activa',true);
    }
}