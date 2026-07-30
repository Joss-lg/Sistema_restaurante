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
        Schema::create('aportes_fondo_propina', function (Blueprint $table) {
            $table->id();
            
            // Llaves foráneas
            $table->foreignId('caja_movimiento_id')
                  ->constrained('caja_movimientos')
                  ->cascadeOnDelete();

            $table->foreignId('mesero_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // Datos del cálculo
            $table->date('fecha');
            $table->decimal('venta_base', 10, 2)->default(0.00);
            $table->decimal('porcentaje', 5, 2)->default(0.00);
            $table->decimal('monto', 10, 2)->default(0.00);
            
            // Usuario que registró el aporte
            $table->foreignId('registrado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Restricción Única e Índices
            $table->unique(['caja_movimiento_id', 'mesero_id'], 'aporte_por_mesero_y_turno');
            $table->index('fecha');
            $table->index('mesero_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aportes_fondo_propina');
    }
};