<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mesa;

$mesa = Mesa::where('numero', 'MESAPRUEBA2')->first();

if ($mesa) {
    echo "✅ Mesa encontrada\n";
    echo "ID: " . $mesa->id . "\n";
    echo "Número: " . $mesa->numero . "\n";
    echo "Estado actual: " . $mesa->estado . "\n";
    
    if ($mesa->estado !== 'disponible') {
        $mesa->update(['estado' => 'disponible']);
        echo "✅ Estado actualizado a: disponible\n";
    } else {
        echo "⚠️ Ya estaba en estado disponible\n";
    }
} else {
    echo "❌ Mesa MESAPRUEBA2 no encontrada\n";
}
