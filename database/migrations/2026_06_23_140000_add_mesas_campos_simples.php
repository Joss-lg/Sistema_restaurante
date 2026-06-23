<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar campos faltantes a la tabla mesas
        if (Schema::hasTable('mesas')) {
            Schema::table('mesas', function (Blueprint $table) {
                // Agregar posicion_x si no existe
                if (!Schema::hasColumn('mesas', 'posicion_x')) {
                    $table->integer('posicion_x')->nullable()->default(50)->after('seccion');
                }
                
                // Agregar posicion_y si no existe
                if (!Schema::hasColumn('mesas', 'posicion_y')) {
                    $table->integer('posicion_y')->nullable()->default(50)->after('posicion_x');
                }
                
                // Agregar zona si no existe (como string simple, no enum)
                if (!Schema::hasColumn('mesas', 'zona')) {
                    $table->string('zona')->nullable()->default('Salón')->after('posicion_y');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mesas')) {
            Schema::table('mesas', function (Blueprint $table) {
                if (Schema::hasColumn('mesas', 'posicion_x')) {
                    $table->dropColumn('posicion_x');
                }
                if (Schema::hasColumn('mesas', 'posicion_y')) {
                    $table->dropColumn('posicion_y');
                }
                if (Schema::hasColumn('mesas', 'zona')) {
                    $table->dropColumn('zona');
                }
            });
        }
    }
};
