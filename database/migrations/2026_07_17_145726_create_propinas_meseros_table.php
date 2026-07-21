<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propinas_meseros', function (Blueprint $table) {
            $table->id();

            // Sesión de caja (turno) en la que se generó la propina.
            // Esto reemplaza "agrupar por fecha" — resuelve el caso de
            // varios turnos en un mismo día.
            $table->foreignId('caja_movimiento_id')->constrained('caja_movimientos');

            $table->foreignId('orden_id')->constrained('ordenes')->cascadeOnDelete();
            $table->foreignId('mesa_id')->constrained('mesas');
            $table->foreignId('mesero_id')->constrained('users');

            $table->enum('metodo_pago', ['tarjeta', 'transferencia']);
            $table->decimal('monto', 10, 2);

            // Se llena cuando se entrega el efectivo al mesero en el corte
            $table->boolean('pagada')->default(false);
            $table->timestamp('pagada_el')->nullable();

            // Referencia al egreso real en flujo_caja que salió del cajón
            // para pagarle esta propina (un egreso puede cubrir varias filas
            // de este mesero en el mismo corte, así que esto es N:1)
            $table->foreignId('flujo_caja_id')->nullable()->constrained('flujo_caja')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propinas_meseros');
    }
};