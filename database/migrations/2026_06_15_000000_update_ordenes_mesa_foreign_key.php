<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            // Eliminar la restricción actual
            $table->dropForeign(['mesa_id']);
        });

        // Hacer mesa_id nullable
        Schema::table('ordenes', function (Blueprint $table) {
            $table->unsignedBigInteger('mesa_id')->nullable()->change();
        });

        // Recrear la restricción con onDelete('set null')
        Schema::table('ordenes', function (Blueprint $table) {
            $table->foreign('mesa_id')
                ->references('id')
                ->on('mesas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropForeign(['mesa_id']);
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->unsignedBigInteger('mesa_id')->nullable(false)->change();
            $table->foreign('mesa_id')->references('id')->on('mesas');
        });
    }
};
