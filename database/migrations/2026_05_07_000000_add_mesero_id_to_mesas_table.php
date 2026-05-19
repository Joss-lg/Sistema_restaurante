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
        // Verificamos si la columna NO existe antes de intentar crearla
        if (!Schema::hasColumn('mesas', 'mesero_id')) {
            Schema::table('mesas', function (Blueprint $table) {
                $table->unsignedBigInteger('mesero_id')->nullable()->after('estado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn('mesero_id');
        });
    }
};