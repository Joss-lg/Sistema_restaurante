<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            
            // Quién abrió y opera esta sesión de caja
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            
            // Turno asignado (Para pintar tus etiquetas: Matutino, Vespertino)
            $table->string('turno')->nullable(); // Ej: 'Matutino', 'Vespertino'
            
            // Estado de la sesión
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');

            // Montos de control para el arqueo/corte
            $table->decimal('monto_inicial', 12, 2)->default(0); // Fondo inicial (Ej: $1000.00)
            $table->decimal('monto_final_esperado', 12, 2)->default(0); // Cálculo matemático del sistema
            $table->decimal('monto_final_real', 12, 2)->default(0); // Lo que el cajero contó en físico
            $table->decimal('diferencia', 12, 2)->default(0); // descuadre (Real - Esperado)
            
            $table->text('comentarios')->nullable(); // Anotaciones del corte
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};