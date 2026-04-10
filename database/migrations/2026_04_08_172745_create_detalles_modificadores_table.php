<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalles_modificadores', function (Blueprint $table) {
            $table->id();
            // Conecta con la línea específica del producto en la orden
            $table->foreignId('detalle_orden_id')->constrained('detalles_orden')->onDelete('cascade');
            // Conecta con el modificador (Extra, Sin, etc.)
            $table->foreignId('modificador_id')->constrained('modificadores');
            
            // Es importante guardar el precio aquí por si el modificador cambia de precio a futuro
            $table->decimal('precio', 10, 2); 
            $table->timestamps();
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_modificadores');
    }
};
