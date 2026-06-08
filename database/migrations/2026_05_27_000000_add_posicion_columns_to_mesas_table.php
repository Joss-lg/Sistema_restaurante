<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (!Schema::hasColumn('mesas', 'posicion_x')) {
                $table->integer('posicion_x')->nullable();
            }
            if (!Schema::hasColumn('mesas', 'posicion_y')) {
                $table->integer('posicion_y')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (Schema::hasColumn('mesas', 'posicion_x')) {
                $table->dropColumn('posicion_x');
            }
            if (Schema::hasColumn('mesas', 'posicion_y')) {
                $table->dropColumn('posicion_y');
            }
        });
    }
};
