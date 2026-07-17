<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_promociones', function (Blueprint $table) {
            $table->id();
            
            // FK: Si se llega a eliminar una orden de la base de datos, se limpia su historial de promociones
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            
            // FK: Vinculación con la promoción aplicada (Protege el historial protegiendo la promo de borrados accidentales)
            $table->foreignId('promocion_id')->constrained('promociones');
            
            // Histórico: Guardamos el monto exacto descontado en esta transacción (Ej: 45.50)
            $table->decimal('monto_descuento', 10, 2); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_promociones');
    }
};