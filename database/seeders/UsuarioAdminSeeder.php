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
        // 1. CREAMOS el rol base
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'Administrador']
            // Si la tabla roles también tiene 'puede_acceder_pos', puedes agregarlo aquí abajo:
            // ['puede_acceder_pos' => true]
        );

        // 2. Creamos al usuario ID 1
        $admin = User::updateOrCreate(
            ['email' => 'admin@ollintem.com'],
            [
                'nombre' => 'Sebastian Admin',
                'password' => Hash::make('admin123'),
                'codigo_empleado' => '1010',
                'rol_id' => $rolAdmin->id,
                'esta_activo' => true,
                'puede_acceder_pos' => true, // <-- ¡Agregado de vuelta! Ya vi que sí está en tu tabla users
            ]
        );

        $this->command->info('¡Rol Administrador y Usuario ID 1 creados con éxito!');
    }
}