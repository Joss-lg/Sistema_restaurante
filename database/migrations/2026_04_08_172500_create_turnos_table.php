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
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            
            // FK: El usuario (cajero o admin) responsable del turno
            // Referencia a la tabla 'usuarios' que creaste en el Bloque 1
            $table->foreignId('usuario_id')->constrained('users');
            
            // Dinero con el que se inicia la caja (el "fondo")
            $table->decimal('saldo_apertura', 10, 2);
            
            // Dinero reportado al cerrar la caja (se llena al final)
            $table->decimal('saldo_cierre', 10, 2)->nullable();
            
            // Diferencia entre lo que el sistema dice que hay y lo que el cajero contó
            $table->decimal('diferencia', 10, 2)->default(0);
            
            // Fechas de inicio y fin del turno
            $table->timestamp('abierto_el')->useCurrent();
            $table->timestamp('cerrado_el')->nullable();
            
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
