<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_promociones', function (Blueprint $table) {
            $table->foreignId('detalle_orden_id')
                  ->nullable()
                  ->after('promocion_id')
                  ->constrained('detalles_orden')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('orden_promociones', function (Blueprint $table) {
            $table->dropForeign(['detalle_orden_id']);
            $table->dropColumn('detalle_orden_id');
        });
    }
};