<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Agregado
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Mesa;
use App\Models\Permiso;
use App\Models\Rol;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // <-- Agregado SoftDeletes

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'codigo_empleado',
        'rol_id',
        'esta_activo',
        'puede_acceder_pos',
        'ultimo_acceso', // <-- Agregado para que coincida con tu migración
    ];

    // <-- Agregado: Vital para la seguridad (oculta contraseñas en respuestas JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime', // <-- Agregado
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

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'user_id');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'mesero_id');
    }

    /**
     * Lógica adaptada para soportar tanto parámetros separados ($modulo_id, $accion)
     * como notación de puntos ('roles.agregar') como usas en tus vistas.
     */
    public function tienePermiso($modulo, $accion = null)
    {
        // 1. El ID 1 es el Administrador supremo (bypass total)
        if ($this->id === 1) return true;

        // 2. Si mandas un string con punto (ej: 'roles.agregar' desde la vista)
        if (is_string($modulo) && str_contains($modulo, '.')) {
            [$modulo, $accion] = explode('.', $modulo);
        } else if (!$accion) {
            $accion = 'mostrar'; // Acción por defecto
        }

        // 3. Buscamos el permiso. 
        // Nota: Si 'modulo' en la base de datos es un ID numérico (ej. 1, 2), 
        // pero pasas 'roles', deberás asegurarte de que tu BD entienda 'roles'.
        $permiso = $this->permisos->where('modulo_id', $modulo)->first();

        // 4. Si existe el registro, verificamos que la acción requerida esté en 1 (true)
        if ($permiso) {
            return $permiso->$accion == 1;
        }

        return false;
    }
}