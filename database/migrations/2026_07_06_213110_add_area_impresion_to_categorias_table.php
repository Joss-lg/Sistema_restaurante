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
        Schema::table('categorias', function (Blueprint $table) {
            // Creamos la columna y le ponemos 'Cocina' por defecto para que no falle con las que ya existen
            $table->string('area_impresion')->default('Cocina')->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            // Si hacemos un rollback, eliminamos la columna de la tabla
            $table->dropColumn('area_impresion');
        });
    }
};