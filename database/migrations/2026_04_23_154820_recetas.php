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
       Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            // Relación con lo que vendes
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            // Relación con tu almacén
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            
            // Cuánto se gasta de este insumo por cada plato vendido
            $table->decimal('cantidad_usada', 10, 3); // Ej: 0.150 (para 150 gramos)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
