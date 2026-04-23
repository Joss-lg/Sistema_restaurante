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


    protected $fillable = [
        'nombre',
        'email',
        'password',
        'codigo_empleado', // PIN de acceso
        'rol',
        'esta_activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean', // Para manejarlo como true/false fácilmente
        ];
    }


    public function getAuthPassword()
    {
        return $this->password;
    }

   
    public function permisos() 
    {
        // Especificamos explícitamente la tabla pivote 'permiso_user'
        return $this->belongsToMany(Permiso::class, 'permiso_user');
    }

    public function tienePermiso($permisoRequerido) 
    {
        // Si es admin, tiene permiso a todo
        if (strtolower($this->rol) === 'admin' || strtolower($this->rol) === 'administrador') {
            return true;
        }

        // Buscamos si dentro de los permisos del usuario existe el slug que le pasamos
        return $this->permisos->contains(function ($permiso) use ($permisoRequerido) {
            return strtolower($permiso->slug) === strtolower($permisoRequerido);
        });
    }

    
}
