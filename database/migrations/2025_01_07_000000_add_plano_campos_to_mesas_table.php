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
        if (!Schema::hasColumn('mesas', 'posicion_x')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->integer('posicion_x')->nullable()->default(50)->after('estado');
            });
        }

        if (!Schema::hasColumn('mesas', 'posicion_y')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->integer('posicion_y')->nullable()->default(50)->after('posicion_x');
            });
        }

        if (!Schema::hasColumn('mesas', 'zona')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->string('zona')->nullable()->default('Salón')->after('posicion_y');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mesas', 'posicion_x')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->dropColumn('posicion_x');
            });
        }

        if (Schema::hasColumn('mesas', 'posicion_y')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->dropColumn('posicion_y');
            });
        }

        if (Schema::hasColumn('mesas', 'zona')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->dropColumn('zona');
            });
        }
    }
};
