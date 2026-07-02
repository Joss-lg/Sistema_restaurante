<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisosSeeder extends Seeder
{
    public function run()
    {
        // 1. DEFINIMOS LOS MÓDULOS Y SUS ACCIONES
        $modulos = [
            'dashboard'   => ['ver'],
            'inventario'  => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'empleados'   => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'productos'   => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'], 
            'categorias'  => ['ver', 'agregar', 'editar', 'eliminar'],
            'mesas'       => ['ver', 'agregar', 'editar', 'eliminar'],
            'promociones' => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'cocina'      => ['ver', 'gestionar'],
            'caja'        => ['ver', 'abrir', 'cerrar', 'retiros', 'reporte'], 
            'finanzas'    => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'roles'       => ['ver', 'agregar', 'editar', 'eliminar'],
        ];

        // 2. INSERTAMOS LOS PERMISOS EN LA BASE DE DATOS
        foreach ($modulos as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $slug = "{$modulo}.{$accion}";
                $nombre = ucfirst($accion) . ' ' . ucfirst($modulo);

                Permiso::updateOrCreate(
                    ['slug' => $slug],
                    ['nombre' => $nombre, 'descripcion' => "Permite {$accion} en el módulo de {$modulo}"]
                );
            }
        }

        $this->command->info('¡Catálogo de permisos sembrado! La tabla de roles queda vacía para creación desde el módulo.');
    }
}