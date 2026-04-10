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
            // onDelete('cascade') asegura que si borras una promo, se limpie esta relación
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');

            // FK: Referencia a la tabla de productos
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_productos');
    }
};
