<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Usamos updateOrCreate para asegurar que siempre tenga el ID 1
        User::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Admin',
                'correo' => 'admin@ollintem.com',
                'contrasena' => Hash::make('tu_password_segura'), // Para entrada web
                'codigo_empleado' => '1010', // Tu PIN para el teclado físico
                'rol' => 'admin',
                'esta_activo' => true,
            ]
        );
    }
}