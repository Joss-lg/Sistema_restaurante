<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos el Usuario Administrador Maestro
        $admin = User::updateOrCreate(
            ['email' => 'admin@ollintem.com'], // Buscamos por email
            [
                'nombre' => 'Sebastian Admin',
                'password' => Hash::make('admin123'), // Contraseña para el Panel Web
                'codigo_empleado' => '1010',         // PIN para el Teclado Numérico
                'rol' => 'admin',
                'esta_activo' => true,
            ]
        );

        // 2. Definimos los permisos iniciales del sistema
        $permisosData = [
            ['nombre' => 'Acceso al POS', 'slug' => 'pos.access', 'descripcion' => 'Permite usar el teclado numérico'],
            ['nombre' => 'Abrir Caja', 'slug' => 'caja.abrir', 'descripcion' => 'Permite realizar apertura de turno'],
            ['nombre' => 'Cancelar Comanda', 'slug' => 'comanda.cancelar', 'descripcion' => 'Permite borrar platillos de una orden'],
            ['nombre' => 'Ver Reportes', 'slug' => 'reportes.ver', 'descripcion' => 'Acceso a las gráficas de ventas'],
        ];

        foreach ($permisosData as $p) {
            $permiso = Permiso::updateOrCreate(['slug' => $p['slug']], $p);
            
            // 3. Asignamos los permisos al Admin (Modo Dios)
            // Usamos syncWithoutDetaching para evitar duplicados al re-ejecutar el seeder
            $admin->permisos()->syncWithoutDetaching([$permiso->id]);
        }
    }
}