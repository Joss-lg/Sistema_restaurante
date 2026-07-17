<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden_id',
        'lote_envio',
        'area',
        'contenido',
        'estado',
        'impreso_en',
    ];

    protected $casts = [
        'impreso_en' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDeArea($query, string $area)
    {
        return $query->where('area', $area);
    }
}