<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Importamos el modelo de Permiso
use App\Models\Permiso;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'codigo_empleado', // PIN de acceso
        'rol',
        'esta_activo',
    ];

    /**
     * Atributos ocultos para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de tipos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean', // Para manejarlo como true/false fácilmente
        ];
    }

    /**
     * Indica a Laravel que use 'password' para el password.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    // --- MÓDULO DE PERMISOS ---

    /**
     * Relación: Un usuario puede tener muchos permisos (Muchos a Muchos).
     */
    public function permisos() 
    {
        // Especificamos explícitamente la tabla pivote 'permiso_user'
        return $this->belongsToMany(Permiso::class, 'permiso_user');
    }

    /**
     * Verifica si el usuario tiene permiso para una acción.
     * El Administrador siempre tiene acceso total.
     */
    public function tienePermiso($identificadorPermiso) 
    {
        // 1. Prioridad: Modo Dios para el Administrador
        if ($this->rol === 'admin') {
            return true;
        }

        // 2. Buscamos por slug o por nombre en la colección de permisos cargados
        return $this->permisos->contains(function ($permiso) use ($identificadorPermiso) {
            return $permiso->slug === $identificadorPermiso || $permiso->nombre === $identificadorPermiso;
        });
    }

    /**
     * Método de ayuda para saber si es Admin rápidamente.
     */
    public function isAdmin()
    {
        return $this->rol === 'admin';
    }
}
