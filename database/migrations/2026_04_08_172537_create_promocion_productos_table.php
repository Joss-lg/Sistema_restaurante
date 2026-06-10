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
        Schema::create('promocion_productos', function (Blueprint $table) {
            $table->id();

            // FK: Referencia a la tabla de promociones
            // Si se elimina la promoción, se borran automáticamente sus productos vinculados aquí
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');

            // FK: Referencia a la tabla de productos
            // Si se elimina un producto, se remueve automáticamente de la promoción
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('promocion_productos');
    }
};