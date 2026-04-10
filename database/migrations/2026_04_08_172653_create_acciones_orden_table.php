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
        Schema::create('acciones_orden', function (Blueprint $table) {
            $table->id();
            // FK: A qué orden pertenece la acción
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            // FK: Qué usuario realizó la acción
            $table->foreignId('usuario_id')->constrained('users');
            
            $table->string('tipo_accion'); // Ej: 'Cancelación', 'Reimpresión', 'Descuento'
            $table->text('motivo')->nullable(); // Por qué se hizo la acción
            
            $table->timestamp('creado_el')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acciones_orden');
    }
};
