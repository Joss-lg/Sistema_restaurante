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
            
            // Relación con el turno/sesión de caja operativa (opcional, por si es un movimiento externo como banco)
            $table->foreignId('caja_movimiento_id')
                  ->nullable()
                  ->constrained('caja_movimientos')
                  ->nullOnDelete(); 

            $table->enum('tipo', ['ingreso', 'egreso']); // Tipo de movimiento financiero
            $table->string('categoria'); // "Venta", "Nómina", "Insumos", "Caja Chica"
            $table->string('concepto'); // Descripción clara del movimiento
            $table->decimal('monto', 12, 2); // Capacidad para montos grandes
            $table->string('metodo_pago'); // "Efectivo", "Tarjeta", "Transferencia"
            $table->string('referencia')->nullable(); // Clave de rastreo o número de operación bancaria
            $table->datetime('fecha'); // Cuándo ocurrió realmente el movimiento
            
            // Relación Polimórfica (Para enlazar directamente con un Pedido, Gasto, Nómina, etc.)
            $table->unsignedBigInteger('flujoable_id')->nullable(); 
            $table->string('flujoable_type')->nullable(); 
            
            $table->text('observaciones')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
            
            // Índices de velocidad para reportes rápidos
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