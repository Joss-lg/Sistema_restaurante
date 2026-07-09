<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Mesa;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Modulo;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'codigo_empleado',
        'rol_id',
        'esta_activo',
        'puede_acceder_pos',
        'ultimo_acceso',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     * Vital para la seguridad (oculta contraseñas en respuestas JSON/API).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'password'          => 'hashed',
            'esta_activo'       => 'boolean',
            'puede_acceder_pos' => 'boolean',
        ];
    }

    /**
     * Obtiene la contraseña para la autenticación del usuario.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relación: Un usuario pertenece a un Rol.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Accesor para cargar el Rol de manera segura.
     */
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

    /**
     * Relación: Un usuario tiene muchos Permisos asignados directamente.
     */
    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'user_id');
    }

    /**
     * Relación: Un usuario (mesero) puede tener muchas Mesas asignadas.
     */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'mesero_id');
    }

    /**
     * Lógica adaptada para soportar tanto parámetros separados ($modulo_id, $accion)
     * como notación de puntos ('roles.agregar') utilizada en las vistas Blade.
     * Traduce automáticamente nombres de módulos (strings) a sus IDs en la base de datos.
     *
     * @param mixed $modulo Nombre del módulo (string) o ID (int)
     * @param string|null $accion Acción requerida (agregar, editar, mostrar, eliminar)
     * @return bool
     */
    public function tienePermiso($modulo, $accion = null): bool
    {
        // 1. El ID 1 es el Administrador supremo (bypass total)
        if ($this->id === 1) {
            return true;
        }

        // 2. Si se envía un string con punto (ej: 'roles.agregar' desde la vista)
        if (is_string($modulo) && str_contains($modulo, '.')) {
            [$modulo, $accion] = explode('.', $modulo);
        } else if (!$accion) {
            $accion = 'mostrar'; // Acción por defecto si no se especifica
        }

        // 3. Si $modulo es un string (nombre de módulo), traducirlo a su ID
        if (is_string($modulo) && !is_numeric($modulo)) {
            $moduloObj = Modulo::where('nombre', $modulo)->first();
            
            if ($moduloObj) {
                $modulo = $moduloObj->id;
            } else {
                // Si no encuentra el módulo en la base de datos, denegar acceso inmediatamente
                return false;
            }
        }

        // 4. Buscamos el permiso asociado a este usuario por modulo_id
        $permiso = $this->permisos->where('modulo_id', $modulo)->first();

        // 5. Si existe el registro, verificamos que la acción requerida esté habilitada (valor 1/true)
        if ($permiso) {
            return $permiso->$accion == 1;
        }

        return false;
    }
}