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
        Schema::table('detalles_orden', function (Blueprint $table) {
            // Añadimos el campo y la llave foránea
            $table->unsignedBigInteger('transaccion_id')->nullable()->after('estado');
            
            $table->foreign('transaccion_id')
                ->references('id')
                ->on('transacciones')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
            $table->dropColumn('transaccion_id');
        });
    }
};
