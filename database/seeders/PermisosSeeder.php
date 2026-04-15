public function run()
{
    $permisos = [
        // CATEGORÍA: VENTAS/MESAS
        ['nombre' => 'Ver Tablero de Mesas', 'slug' => 'ver_mesas'],
        ['nombre' => 'Tomar Comandas', 'slug' => 'tomar_comandas'],
        
        // CATEGORÍA: COCINA
        ['nombre' => 'Ver Monitor de Cocina', 'slug' => 'ver_cocina'],
        
        // CATEGORÍA: ADMIN
        ['nombre' => 'Gestionar Inventario', 'slug' => 'ver_inventario'],
        ['nombre' => 'Administrar Empleados', 'slug' => 'admin_empleados'],
    ];

    foreach ($permisos as $permiso) {
        // updateOrCreate evita que se dupliquen si vuelves a correr el seeder
        \App\Models\Permiso::updateOrCreate(
            ['slug' => $permiso['slug']], 
            ['nombre' => $permiso['nombre']]
        );
    }
}