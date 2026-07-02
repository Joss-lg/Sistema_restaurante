<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAMOS el rol base necesario para que el sistema no explote
        $rolAdmin = Rol::firstOrCreate(
            ['slug' => 'admin'],
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Rol con acceso total al sistema'
            ]
        );

        // 2. Creamos al usuario ID 1 (Tú)
        $admin = User::updateOrCreate(
            ['email' => 'admin@ollintem.com'],
            [
                'nombre' => 'Sebastian Admin',
                'password' => Hash::make('admin123'),
                'codigo_empleado' => '1010',
                'rol_id' => $rolAdmin->id,
                'esta_activo' => true,
                'puede_acceder_pos' => true, // El nuevo campo de la migración
            ]
        );

        $this->command->info('¡Rol Administrador y Usuario ID 1 creados con éxito!');
    }
}