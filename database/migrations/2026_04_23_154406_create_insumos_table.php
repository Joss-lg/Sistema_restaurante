<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up()
        {
            Schema::create('insumos', function (Blueprint $table) {
                $table->id();
                // Recomendación: Agregar un código único para facilitar búsquedas rápidas (ej. INS-001)
                $table->string('codigo')->unique()->nullable();
                
                $table->string('nombre'); 
                $table->string('unidad_medida'); // kg, gr, lt, pza, etc.
                
                // CONEXIÓN CON TU TABLA DE CATEGORÍAS YA EXISTENTE (Recomendado)
                $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
                
                $table->decimal('stock_actual', 10, 2)->default(0);
                $table->decimal('stock_minimo', 10, 2)->default(5); // Umbral para alertas
                $table->decimal('precio_compra', 10, 2)->nullable();
                
                // Estado para no borrar insumos y arruinar reportes viejos
                $table->boolean('esta_activo')->default(true); 
                
                $table->timestamps();
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
