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
        if (Schema::hasTable('promociones') && Schema::hasColumn('promociones', 'tipo')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->string('tipo_promocion')->after('nombre');
            });

            DB::statement('UPDATE promociones SET tipo_promocion = tipo');

            Schema::table('promociones', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('promociones') && Schema::hasColumn('promociones', 'tipo_promocion')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->string('tipo')->after('nombre');
            });

            DB::statement('UPDATE promociones SET tipo = tipo_promocion');

            Schema::table('promociones', function (Blueprint $table) {
                $table->dropColumn('tipo_promocion');
            });
        }
    }
};
