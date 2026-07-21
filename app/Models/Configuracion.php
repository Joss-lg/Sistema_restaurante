<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
    ];

    /**
     * Obtiene un valor de configuración por su clave.
     * Usa cache para no pegarle a la BD en cada cálculo de orden.
     *
     * @param string $clave
     * @param mixed  $default
     * @return mixed
     */
    public static function obtener(string $clave, $default = null)
    {
        return Cache::rememberForever("config_{$clave}", function () use ($clave, $default) {
            $registro = self::where('clave', $clave)->first();
            return $registro ? $registro->valor : $default;
        });
    }

    /**
     * Guarda (o actualiza) un valor de configuración y limpia su cache.
     *
     * @param string $clave
     * @param mixed  $valor
     * @return void
     */
    public static function establecer(string $clave, $valor): void
    {
        self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor]
        );

        Cache::forget("config_{$clave}");
    }

    /**
     * Helper específico: ¿el IVA está habilitado?
     */
    public static function ivaHabilitado(): bool
    {
        return (bool) self::obtener('iva_habilitado', true);
    }

    /**
     * Helper específico: porcentaje de IVA configurado (ej. 16 => 16%)
     */
    public static function ivaPorcentaje(): float
    {
        return (float) self::obtener('iva_porcentaje', 16);
    }
}