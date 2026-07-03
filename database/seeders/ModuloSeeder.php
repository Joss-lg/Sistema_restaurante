<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloSeeder extends Seeder
{
    public function run()
    {
        $modulos = [
            ['nombre' => 'Dashboard'],
            ['nombre' => 'Inventario'],
            ['nombre' => 'Empleados'],
            ['nombre' => 'Productos'],
            ['nombre' => 'Categorias'],
            ['nombre' => 'Mesas'],
            ['nombre' => 'Promociones'],
            ['nombre' => 'Cocina'],
            ['nombre' => 'Caja'],
            ['nombre' => 'Finanzas'],
            ['nombre' => 'Roles'],
            ['nombre' => 'Historial de Cajas'],
        ];

        DB::table('modulos')->insert($modulos);
    }
}