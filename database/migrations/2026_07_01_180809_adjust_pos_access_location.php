<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar la columna a la tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_acceder_pos')->default(false)->after('esta_activo');
        });

        // 2. Eliminar la columna de la tabla roles
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('puede_acceder_pos');
        });
    }

    public function down(): void
    {
        // Revertir en caso de error
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('puede_acceder_pos')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('puede_acceder_pos');
        });
    }
};