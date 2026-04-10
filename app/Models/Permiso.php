<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importamos el modelo User para la relación
use App\Models\User;

class Permiso extends Model
{
    use HasFactory;

    /**
     * Definimos la tabla si el nombre es distinto al plural (opcional pero seguro)
     */
    protected $table = 'permisos';

    /**
     * Campos que el Administrador podrá llenar desde el sistema.
     */
    protected $fillable = [
        'nombre',      // Ejemplo: "Gestionar Inventario"
        'slug',        // Ejemplo: "gestionar-inventario" (clave para el código)
        'descripcion'  // Ejemplo: "Permite al usuario editar stock y precios"
    ];

    /**
     * Relación inversa: Un permiso puede estar asignado a muchos usuarios.
     */
    public function usuarios()
    {
        // Especificamos la tabla pivote que ya creaste en tus migraciones
        return $this->belongsToMany(User::class, 'permiso_user');
    }
}