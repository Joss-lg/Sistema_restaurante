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
        Schema::table('transacciones', function (Blueprint $table) {
            // Definimos el tipo de pago para la lógica de división
            $table->enum('tipo_division', ['equitativa', 'por_producto', 'personalizado'])
                ->default('personalizado')
                ->after('monto');
        });
    }

    public function down()
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn('tipo_division');
        });
    }
};
