<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            // Agregar columnas para el plano espacial si no existen
            if (!Schema::hasColumn('mesas', 'forma')) {
                $table->enum('forma', ['redonda', 'cuadrada'])->default('redonda')->after('seccion');
            }
            if (!Schema::hasColumn('mesas', 'ancho')) {
                $table->integer('ancho')->default(60)->after('posicion_y');
            }
            if (!Schema::hasColumn('mesas', 'alto')) {
                $table->integer('alto')->default(60)->after('ancho');
            }
            if (!Schema::hasColumn('mesas', 'zona')) {
                $table->enum('zona', ['salon', 'terraza', 'vip'])->default('salon')->after('seccion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (Schema::hasColumn('mesas', 'forma')) {
                $table->dropColumn('forma');
            }
            if (Schema::hasColumn('mesas', 'ancho')) {
                $table->dropColumn('ancho');
            }
            if (Schema::hasColumn('mesas', 'alto')) {
                $table->dropColumn('alto');
            }
            if (Schema::hasColumn('mesas', 'zona')) {
                $table->dropColumn('zona');
            }
        });
    }
};
