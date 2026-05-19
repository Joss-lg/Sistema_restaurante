<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'codigo_empleado', // PIN de acceso
        'rol_id',          // Cambiamos 'rol' por 'rol_id' para la relación
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
            'esta_activo' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relación con el nuevo modelo Rol
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function getRolAttribute($value)
    {
        // Si la relación ya está cargada, devolverla directamente.
        if ($this->relationLoaded('rol')) {
            return $this->getRelation('rol');
        }

        // Si hay rol_id, forzar la relación desde el modelo Rol.
        if (!empty($this->attributes['rol_id'])) {
            return $this->rol()->first();
        }

        // Si todavía existe el campo legacy 'rol', devolver su valor original.
        return $value;
    }

    /**
     * Mantenemos permisos directos por si necesitas asignar 
     * algo específico a un usuario fuera de su rol.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_user');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'mesero_id');
    }

    protected function obtenerSlugRol(): ?string
    {
        $rol = $this->rol;

        if ($rol instanceof Rol) {
            return strtolower(trim($rol->slug));
        }

        return $rol ? strtolower(trim((string) $rol)) : null;
    }

    /**
     * MÉTODO CLAVE: Ahora verifica permisos por ROL y directos.
     */
    public function tienePermiso($permisoRequerido)
    {
        $rolSlug = $this->obtenerSlugRol();

        if (!$rolSlug) {
            return false;
        }

        if (in_array($rolSlug, ['admin', 'administrador'], true)) {
            return true;
        }

        $tienePermisoEnRol = false;
        if ($this->rol instanceof Rol) {
            $tienePermisoEnRol = $this->rol->permisos->contains('slug', strtolower($permisoRequerido));
        }

        if ($tienePermisoEnRol) {
            return true;
        }

        return $this->permisos->contains('slug', strtolower($permisoRequerido));
    }

    /**
     * Útil para filtrar empleados de nómina que no deben entrar al POS
     */
    public function puedeAccederAlSistema(): bool
    {
        if (! $this->rol || ! $this->rol instanceof Rol) {
            return false;
        }

        return (bool) $this->rol->puede_acceder_pos;
    }
}