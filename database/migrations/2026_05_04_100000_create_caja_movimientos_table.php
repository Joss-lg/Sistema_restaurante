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
        
        // Quién hace la acción (Apertura o Corte)
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
        
        // Tipo de acción
        $table->enum('accion', ['apertura', 'cierre'])->default('apertura');
        
        // Montos de control para el corte
        $table->decimal('monto_inicial', 12, 2)->default(0); // El fondo de caja al abrir (Ej: $1,000)
        $table->decimal('monto_final_esperado', 12, 2)->nullable(); // Lo que el sistema dice que debe haber
        $table->decimal('monto_final_real', 12, 2)->nullable(); // Lo que el cajero contó físicamente
        $table->decimal('diferencia', 12, 2)->default(0); // Faltante o Sobrante (monto_real - monto_esperado)
        
        $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
        $table->text('comentarios')->nullable(); // Ej: "Faltaron $50 porque no había cambio"
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};