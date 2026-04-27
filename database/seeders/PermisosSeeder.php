<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permiso;
use App\Models\User;

class PermisosSeeder extends Seeder
{
    public function run()
    {
        // 1. DEFINIMOS TODOS LOS PERMISOS DEL RESTAURANTE
        $modulos = [
            'dashboard'   => ['ver'],
            'inventario'  => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'empleados'   => ['ver', 'agregar', 'editar', 'eliminar', 'reporte'],
            'productos'   => ['ver', 'agregar', 'editar', 'eliminar'], 
            'categorias'  => ['ver', 'agregar', 'editar', 'eliminar'],
            'mesas'       => ['ver', 'agregar', 'editar', 'eliminar'],
            'promociones' => ['ver', 'agregar', 'editar', 'eliminar'],
            'cocina'      => ['ver', 'gestionar'],
            
            // EL NUEVO MÓDULO: CAJA (Antes Turnos)
            'caja'        => ['ver', 'abrir', 'cerrar', 'retiros', 'reporte'], 
        ];

        // 2. CREAMOS LOS PERMISOS EN LA BASE DE DATOS
        $permisosCreados = [];
        foreach ($modulos as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $slug = "{$modulo}.{$accion}";
                $nombre = ucfirst($accion) . ' ' . ucfirst($modulo);

                $permiso = Permiso::updateOrCreate(
                    ['slug' => $slug],
                    ['nombre' => $nombre, 'descripcion' => "Permite {$accion} en el módulo de {$modulo}"]
                );
                
                $permisosCreados[$slug] = $permiso->id;
            }
        }

        // 3. MATRIZ DE ACCESOS POR ROL
        $matrizRoles = [
            // EL ADMIN: Dueño del restaurante (Todo el acceso)
            'admin' => Permiso::pluck('id')->toArray(), 
            
            // EL CAPITÁN: Gerente en turno
            'capitan' => [
                $permisosCreados['dashboard.ver'],
                $permisosCreados['mesas.ver'], $permisosCreados['mesas.editar'],
                $permisosCreados['empleados.ver'], $permisosCreados['empleados.reporte'],
                $permisosCreados['inventario.ver'], $permisosCreados['inventario.agregar'], $permisosCreados['inventario.reporte'],
                $permisosCreados['productos.ver'], 
                
                // Permisos de Caja para el Capitán
                $permisosCreados['caja.ver'], 
                $permisosCreados['caja.abrir'], 
                $permisosCreados['caja.cerrar'], 
                $permisosCreados['caja.retiros'], // Para sacar dinero a proveedores
                $permisosCreados['caja.reporte'], // Para imprimir el corte Z
            ],

            // CAJERO (Nuevo rol recomendado si tienes a alguien fijo en la caja)
            'cajero' => [
                $permisosCreados['mesas.ver'],
                $permisosCreados['productos.ver'],
                
                // Permisos de Caja limitados (No puede hacer retiros grandes o ver el reporte final sin el Capitán)
                $permisosCreados['caja.ver'], 
                $permisosCreados['caja.abrir'], 
                $permisosCreados['caja.cerrar'], 
            ],

            // EL MESERO: Atiende y cobra (si usa tablet), pero no toca la caja registradora principal
            'mesero' => [
                $permisosCreados['mesas.ver'],
                $permisosCreados['productos.ver'],
                $permisosCreados['categorias.ver'],
                $permisosCreados['promociones.ver'],
            ],

            // EL COCINERO
            'cocinero' => [
                $permisosCreados['cocina.ver'], $permisosCreados['cocina.gestionar'],
                $permisosCreados['inventario.ver'], $permisosCreados['inventario.agregar'],
                $permisosCreados['productos.ver'], 
            ]
        ];

        // 4. ASIGNAMOS A LOS USUARIOS EXISTENTES
        $usuarios = User::all();

        foreach ($usuarios as $usuario) {
            $rol = strtolower($usuario->rol); 
            if (isset($matrizRoles[$rol])) {
                $usuario->permisos()->sync($matrizRoles[$rol]);
            }
        }

        $this->command->info('¡Módulo de Caja agregado y permisos asignados!');
    }
}