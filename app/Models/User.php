<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use App\Models\Mesa;
use App\Models\Permiso;
use App\Models\Rol;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'codigo_empleado',
        'rol_id',
        'esta_activo',
        'puede_acceder_pos', 
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean',
            'puede_acceder_pos' => 'boolean', 
        ];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function getRolAttribute($value)
    {
        if ($this->relationLoaded('rol')) {
            return $this->getRelation('rol');
        }

        if (!empty($this->attributes['rol_id'])) {
            return $this->rol()->first();
        }

        return $value;
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_user');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'mesero_id');
    }

    /**
     * Función ajustada para verificar permisos por el NOMBRE del módulo
     */
    public function tienePermiso($nombreModulo)
    {
        // 1. El ID 1 es el Administrador supremo (bypass total)
        if ($this->id === 1) return true;

        // 2. ¿Tiene el permiso asignado directamente? (Busca por campo 'nombre')
        if ($this->permisos()->where('nombre', $nombreModulo)->exists()) {
            return true;
        }

        // 3. ¿Su rol tiene el permiso? (Busca por campo 'nombre')
        if ($this->rol && $this->rol->permisos()->where('nombre', $nombreModulo)->exists()) {
            return true;
        }

        return false;
    }
}