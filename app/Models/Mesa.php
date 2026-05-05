<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'numero', 
        'capacidad', 
        'estado', 
        'seccion', 
        'posicion_x', 
        'posicion_y',
        'mesero_id',
    ];

    public function mesero()
    {
        return $this->belongsTo(
            
            \App\Models\User::class,
            'mesero_id'
        );
    }
}