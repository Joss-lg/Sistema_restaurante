<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Primero creamos todos los permisos
        $this->call(PermisosSeeder::class);

        // 2. Luego creamos al Admin (que ya podrá tomar los permisos creados arriba)
        $this->call(UsuarioAdminSeeder::class);
    }
}
