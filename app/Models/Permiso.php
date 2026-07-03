<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permisos';

    // Estos son los campos que realmente existen en tu tabla de la imagen
    protected $fillable = [
        'user_id', 
        'modulo_id', 
        'mostrar', 
        'crear', 
        'editar', 
        'eliminar', 
        'gestionar'
    ];

    // Para que Laravel trate estos campos automáticamente como true/false
    protected $casts = [
        'mostrar'   => 'boolean',
        'crear'     => 'boolean',
        'editar'    => 'boolean',
        'eliminar'  => 'boolean',
        'gestionar' => 'boolean',
    ];

    // Relación con el Usuario (Empleado)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el Módulo
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }
}