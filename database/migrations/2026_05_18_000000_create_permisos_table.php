<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            
            // Lógica de permisos CRUD (usamos booleanos para facilitar el uso)
            $table->boolean('mostrar')->default(0);
            $table->boolean('crear')->default(0);
            $table->boolean('editar')->default(0);
            $table->boolean('eliminar')->default(0);
            $table->boolean('gestionar')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
