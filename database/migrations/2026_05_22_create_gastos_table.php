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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->string('concepto'); // Descripción del gasto
            $table->decimal('monto', 10, 2); // Monto del gasto
            $table->enum('categoria', ['Compra Insumos', 'Servicios', 'Renta', 'Mantenimiento', 'Otro']); // Categoría del gasto
            $table->string('metodo_pago'); // Efectivo, Tarjeta, Transferencia
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente'); // Estado del gasto
            $table->datetime('fecha'); // Fecha del gasto
            $table->text('descripcion')->nullable(); // Descripción adicional
            $table->string('documento')->nullable(); // Número de documento/factura
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
