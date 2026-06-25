<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Mesa;
use App\Models\Orden;

echo "🧹 Limpiando base de datos...\n";
echo "═════════════════════════════════════════\n";

try {
    // Deshabilitar restricciones de clave extranjera
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    echo "✅ Restricciones de clave extranjera deshabilitadas\n";
    
    // Limpiar tablas
    $ordenesCount = Orden::count();
    if ($ordenesCount > 0) {
        Orden::truncate();
        echo "✅ Órdenes eliminadas ($ordenesCount registros)\n";
    }
    
    $mesasCount = Mesa::count();
    if ($mesasCount > 0) {
        Mesa::truncate();
        echo "✅ Mesas eliminadas ($mesasCount registros)\n";
    }
    
    // Reabilitar restricciones de clave extranjera
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "✅ Restricciones de clave extranjera rehabilitadas\n";
    
    echo "\n📊 Estado final:\n";
    echo "   - Mesas en BD: " . Mesa::count() . "\n";
    echo "   - Órdenes en BD: " . Orden::count() . "\n";
    echo "\n🎉 Base de datos limpiada correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}
