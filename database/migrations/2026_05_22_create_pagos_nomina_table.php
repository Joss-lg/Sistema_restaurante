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
        Schema::create('pagos_nomina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Relación con empleado
            $table->string('periodo'); // Ej: "1-15 Mayo 2026"
            $table->decimal('sueldo_base', 10, 2); // Sueldo base del empleado
            $table->decimal('bonos', 10, 2)->default(0); // Bonificaciones
            $table->decimal('deducciones', 10, 2)->default(0); // Retenciones/Deducciones
            $table->decimal('monto_neto', 10, 2); // Monto final a pagar
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente'); // Estado del pago
            $table->datetime('fecha_pago')->nullable(); // Fecha en que se realizó el pago
            $table->string('metodo_pago')->default('Efectivo'); // Método de pago
            $table->text('observaciones')->nullable(); // Notas adicionales
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_nomina');
    }
};
