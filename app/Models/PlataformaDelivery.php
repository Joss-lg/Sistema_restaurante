<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlataformaDelivery extends Model
{
    protected $table = 'plataformas_delivery';

    protected $fillable = [
        'nombre',
        'slug',
        'color',
        'comision_porcentaje',
        'iva_comision_porcentaje',
        'activo',
    ];

    protected $casts = [
        'comision_porcentaje'     => 'float',
        'iva_comision_porcentaje' => 'float',
        'activo'                  => 'boolean',
    ];

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'plataforma_delivery_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}