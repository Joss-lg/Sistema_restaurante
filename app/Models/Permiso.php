<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permisos';

    protected $fillable = [
        'nombre',      // Ejemplo: "Gestionar Inventario"
        'slug',        // Ejemplo: "gestionar-inventario"
        'descripcion'  // Ejemplo: "Permite editar stock y precios"
    ];

    /**
     * Relación con los Roles: Un permiso puede estar en muchos roles.
     * Usamos la tabla 'permiso_rol' que migramos recientemente.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'permiso_rol');
    }

    /**
     * Mantenemos la relación con usuarios por si decides asignar 
     * permisos individuales sin pasar por un rol (opcional).
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permiso_user');
    }
}