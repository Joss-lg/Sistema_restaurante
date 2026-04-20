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
            ['email' => 'admin@ollintem.com'],
            [
                'nombre' => 'Sebastian Admin',
                'password' => Hash::make('admin123'),
                'codigo_empleado' => '1010',
                'rol' => 'admin',
                'esta_activo' => true,
            ]
        );

        // 2. En lugar de una lista manual, tomamos TODOS los permisos de la base de datos
        // Esto incluye los de Inventario, Mesas, Cocina, etc., que creó el otro Seeder.
        $todosLosPermisos = Permiso::all();

        // 3. Se los asignamos todos de un golpe
        // Usamos sync() para que el Admin siempre esté actualizado con lo último
        $admin->permisos()->sync($todosLosPermisos->pluck('id'));
    }
}