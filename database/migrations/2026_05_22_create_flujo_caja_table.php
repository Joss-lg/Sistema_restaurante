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
        Schema::create('flujo_caja', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'egreso']); // Tipo de movimiento
            $table->string('categoria'); // Categoría (Ej: "Venta", "Nómina", "Compra Insumos", etc.)
            $table->string('concepto'); // Descripción del concepto
            $table->decimal('monto', 10, 2); // Monto del movimiento
            $table->string('metodo_pago'); // Método de pago utilizado
            $table->datetime('fecha'); // Fecha del movimiento
            
            // Relación Polimórfica
            $table->unsignedBigInteger('flujoable_id')->nullable(); // ID del modelo relacionado
            $table->string('flujoable_type')->nullable(); // Tipo de modelo (Transaccion, Gasto, PagoNomina, etc.)
            
            $table->text('observaciones')->nullable(); // Notas adicionales
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar búsquedas
            $table->index(['tipo', 'fecha']);
            $table->index(['flujoable_type', 'flujoable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flujo_caja');
    }
};
