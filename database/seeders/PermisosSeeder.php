<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;

class PermisosSeeder extends Seeder
{
    public function run()
    {
        // 1. CREAMOS LOS ROLES BÁSICOS SI NO EXISTEN
        $roles = [
            ['slug' => 'admin', 'nombre' => 'Administrador', 'descripcion' => 'Acceso completo', 'puede_acceder_pos' => true],
            ['slug' => 'capitan', 'nombre' => 'Capitán', 'descripcion' => 'Gerente en turno', 'puede_acceder_pos' => true],
            ['slug' => 'mesero', 'nombre' => 'Mesero', 'descripcion' => 'Atiende mesas y crea órdenes', 'puede_acceder_pos' => false],
            ['slug' => 'cocinero', 'nombre' => 'Cocinero', 'descripcion' => 'Cocina y gestiona pedidos', 'puede_acceder_pos' => false],
            ['slug' => 'cajero', 'nombre' => 'Cajero', 'descripcion' => 'Opera caja y cierres', 'puede_acceder_pos' => true],
        ];

        foreach ($roles as $rolData) 
            Rol::updateOrCreate(
                ['slug' => $rolData['slug']],
                $rolData
            );
        
        // 2. DEFINIMOS TODOS LOS PERMISOS DEL RESTAURANTE  
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

        // 3. MATRIZ DE ACCESOS POR ROL (usando slugs)
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

        // 4. ASIGNAMOS PERMISOS A LOS ROLES DESDE LA BASE DE DATOS
        // Esto es mucho más flexible: si creas un nuevo rol, sus permisos se asignan automáticamente
        foreach ($matrizRoles as $rolSlug => $permisoIds) {
            $rol = Rol::where('slug', $rolSlug)->first();
            
            if ($rol) {
                $rol->permisos()->sync($permisoIds);
            }
        }

        // 5. SINCRONIZAR PERMISOS DE USUARIOS (Por si algunos usuarios tienen permisos específicos)
        $usuarios = User::with('rol')->get();

        foreach ($usuarios as $usuario) {
            if ($usuario->rol) {
                // Sincronizar permisos basados en el rol
                $rolSlug = strtolower($usuario->rol->slug);
                
                if (isset($matrizRoles[$rolSlug])) {
                    $usuario->permisos()->sync($matrizRoles[$rolSlug]);
                }
            }
        }

        $this->command->info('¡Permisos sincronizados con roles dinámicos!');
    }
}
