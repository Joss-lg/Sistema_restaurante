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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Quién hizo el movimiento
            $table->decimal('cantidad', 10, 3);
            $table->enum('tipo', ['entrada', 'salida', 'ajuste', 'venta']); 
            $table->string('motivo')->nullable(); // Ej: "Producto caducado" o "Venta Folio #123"
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
