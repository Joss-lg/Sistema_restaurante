<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener el rol de administrador desde la tabla roles
        $rolAdmin = Rol::where('slug', 'admin')->first();
        
        if (!$rolAdmin) {
            $this->command->warn('El rol "admin" no existe en la tabla roles. Crea los roles primero.');
            return;
        }

        // 2. Creamos el Usuario Administrador Maestro con rol_id en lugar de rol string
        $admin = User::updateOrCreate(
            ['email' => 'admin@ollintem.com'],
            [
                'nombre' => 'Sebastian Admin',
                'password' => Hash::make('admin123'),
                'codigo_empleado' => '1010',
                'rol_id' => $rolAdmin->id,
                'esta_activo' => true,
            ]
        );

        // 3. En lugar de una lista manual, tomamos TODOS los permisos de la base de datos
        // Esto incluye los de Inventario, Mesas, Cocina, etc., que creó el otro Seeder.
        $todosLosPermisos = Permiso::all();

        // 4. Se los asignamos todos de un golpe
        // Usamos sync() para que el Admin siempre esté actualizado con lo último
        $admin->permisos()->sync($todosLosPermisos->pluck('id'));
    }
}
