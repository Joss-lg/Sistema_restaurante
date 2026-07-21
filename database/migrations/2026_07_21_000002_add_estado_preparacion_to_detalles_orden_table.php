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
        Schema::table('detalles_orden', function (Blueprint $table) {
            // Campo NUEVO e independiente del campo 'estado' que ya existe
            // (ese se sigue usando para lo que ya usabas, como 'en cocina').
            // Este campo es exclusivamente para el flujo de la pantalla de
            // Cocina/Barra: pendiente -> en proceso -> servida, y se
            // actualiza por lote + área, no por Orden completa.
            $table->string('estado_preparacion')->default('pendiente')->after('estado');
        });

        // Los detalles que ya existan en la base de datos (pedidos activos
        // en este momento) arrancan en 'pendiente' por defecto gracias al
        // ->default('pendiente') de arriba, así que no rompen nada.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->dropColumn('estado_preparacion');
        });
    }
};