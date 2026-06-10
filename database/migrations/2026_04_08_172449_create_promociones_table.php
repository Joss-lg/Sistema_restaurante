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
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            
            // Agregamos la columna descripción para añadir texto explicativo de la promo
            $table->text('descripcion')->nullable();
            
            // El tipo de promoción (formulario de texto libre donde escribes qué promoción deseas que exista)
            $table->string('tipo_promocion'); 
            
            // El valor puede ser el porcentaje (ej: 15.00) o el monto (ej: 50.00)
            $table->decimal('valor_descuento', 10, 2);
            
            // Vigencia de la promoción
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // Estructura JSON para almacenar los días seleccionados con el checklist (Ej: ["martes", "miercoles"])
            $table->json('dias_semana')->nullable();
            
            // Para activar/desactivar la promo sin borrarla
            $table->boolean('esta_activa')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};