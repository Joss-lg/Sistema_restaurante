<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una fila por "la parte de una persona" cuando una mesa se cobra
     * dividida. Vive a nivel MESA (no orden), porque CajaService
     * trabaja agregando todas las órdenes activas de la mesa como una
     * sola unidad de cobro.
     *
     * Sin llaves foráneas a propósito: la integridad ya la maneja
     * CajaService desde PHP (borra las filas al liberar/cancelar la
     * mesa), y así evitamos depender de que mesas/caja_movimientos
     * tengan sus índices en un estado perfecto en cada entorno.
     */
    public function up(): void
    {
        if (Schema::hasTable('cuentas_division')) {
            return; // ya existe (p. ej. se aplicó antes a mano vía SQL)
        }

        Schema::create('cuentas_division', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('mesa_id');
            $table->index('mesa_id');

            // 'equitativa': el total se reparte entre N partes iguales.
            // 'por_producto': cada parte cobra solo lo que le asignaron.
            $table->enum('tipo', ['equitativa', 'por_producto']);

            $table->unsignedTinyInteger('numero_cuenta'); // 1, 2, 3...
            $table->unsignedTinyInteger('total_partes');  // N total de personas

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('propina', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('estado', ['pendiente', 'pagada'])->default('pendiente');
            $table->timestamp('pagada_el')->nullable();

            $table->unsignedBigInteger('caja_movimiento_id')->nullable();
            $table->index('caja_movimiento_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_division');
    }
};
