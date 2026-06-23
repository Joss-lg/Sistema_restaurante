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
        $permisoRequerido = strtolower($permisoRequerido);
        
        // 1. Obtener el Rol (usando lazy loading optimizado como ya lo tenías)
        $rol = $this->relationLoaded('rol')
            ? $this->getRelation('rol')
            : $this->rol()->first();

        if ($rol instanceof Rol) {
            $rolSlug = strtolower(trim((string) $rol->slug));

            // El Administrador supremo siempre tiene acceso a todo
            if (in_array($rolSlug, ['admin', 'administrador'], true)) {
                return true;
            }
        }

        // 2. REGLA DE ANULACIÓN: Si el usuario tiene personalizaciones directas,
        // sus permisos directos MANDAN sobre el rol.
        if ($this->permisos()->exists()) {
            return $this->permisos()->where('slug', $permisoRequerido)->exists();
        }

        // 3. FALLBACK: Si el usuario no tiene permisos personalizados, hereda del Rol
        if ($rol instanceof Rol) {
            if ($rol->relationLoaded('permisos')) {
                if ($rol->permisos->contains('slug', $permisoRequerido)) {
                    return true;
                }
            } else {
                if (DB::table('permiso_rol')
                    ->join('permisos', 'permiso_rol.permiso_id', '=', 'permisos.id')
                    ->where('permiso_rol.rol_id', $rol->id)
                    ->where('permisos.slug', $permisoRequerido)
                    ->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}