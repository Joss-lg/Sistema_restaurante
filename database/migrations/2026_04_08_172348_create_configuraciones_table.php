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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            
            // 'clave' es el nombre del ajuste (ej: 'nombre_comercial')
            $table->string('clave')->unique(); 
            
            // 'valor' es el dato guardado (ej: 'Tacos El Sebastian')
            // Usamos text porque el valor puede ser largo (como una dirección o un aviso de privacidad)
            $table->text('valor')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
