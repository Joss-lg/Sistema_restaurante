<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
   public function run()
    {
        $roles = ['Administrador', 'Capitán', 'Mesero', 'Cajero', 'Cocinero'];

        foreach ($roles as $nombre) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $nombre]
            );
        }
    }
}