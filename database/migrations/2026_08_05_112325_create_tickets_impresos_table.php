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
        Schema::create('tickets_impresos', function (Blueprint $table) {
            $table->id(); // Equivale a BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            
            $table->unsignedBigInteger('orden_referencia_id')->unique('tickets_impresos_orden_referencia_id_unique');
            $table->string('mesa_numero')->nullable();
            
            // Relaciones con la tabla 'users'
            $table->foreignId('mesero_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('cajero_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('impreso_en');
            $table->timestamps(); // Genera created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_impresos');
    }
};