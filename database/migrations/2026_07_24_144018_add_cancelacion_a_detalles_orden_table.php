<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->text('cancelado_motivo')->nullable()->after('estado');
            $table->unsignedBigInteger('cancelado_por')->nullable()->after('cancelado_motivo');
            $table->timestamp('cancelado_en')->nullable()->after('cancelado_por');

            $table->foreign('cancelado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->dropForeign(['cancelado_por']);
            $table->dropColumn(['cancelado_motivo', 'cancelado_por', 'cancelado_en']);
        });
    }
};