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
            // Eliminamos las columnas slug y orden_visualizacion
            $table->dropColumn(['slug', 'orden_visualizacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            // Volvemos a crear las columnas en caso de hacer un rollback
            $table->string('slug')->nullable();
            $table->integer('orden_visualizacion')->default(0);
        });
    }
};