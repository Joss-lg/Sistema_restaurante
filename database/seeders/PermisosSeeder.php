<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            'dashboard'   => ['ver'],
            'empleados'   => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'inventario'  => ['ver', 'agregar', 'editar', 'eliminar'],
            'productos'   => ['ver', 'agregar', 'editar', 'eliminar'],
            'categorias'  => ['ver', 'agregar', 'editar', 'eliminar'],
            'mesas'       => ['ver', 'agregar', 'editar', 'eliminar'],
            'promociones' => ['ver', 'agregar', 'editar', 'eliminar'],
            'cocina'      => ['ver', 'editar'], 
            'turnos'      => ['ver', 'abrir', 'cerrar'],
        ];

        foreach ($modulos as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                Permiso::updateOrCreate(
                    ['slug' => "$modulo.$accion"],
                    ['nombre' => ucfirst($accion) . " " . ucfirst($modulo)]
                );
            }
        }
    }
}