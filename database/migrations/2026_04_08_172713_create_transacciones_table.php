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
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id(); // PK

        // FK: Conecta con la orden que se está pagando
        $table->foreignId('orden_id')->constrained('ordenes');

        // FK: Conecta con el turno de caja actual
        $table->foreignId('turno_id')->constrained('turnos');

        // FK: Conecta con el usuario que tiene el rol de cajero
        $table->foreignId('cajero_id')->constrained('users');

        $table->string('metodo_pago'); // Ej: Efectivo, Tarjeta, Transferencia
        $table->decimal('monto', 10, 2); // El total pagado
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
