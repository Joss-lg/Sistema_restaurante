<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (!Schema::hasColumn('mesas', 'tipo')) {
                $table->string('tipo', 20)->default('local')->after('numero');
            }
            if (!Schema::hasColumn('mesas', 'plataforma_delivery_id')) {
                $table->foreignId('plataforma_delivery_id')
                      ->nullable()
                      ->after('tipo')
                      ->constrained('plataformas_delivery')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('mesas', 'comision_porcentaje')) {
                $table->decimal('comision_porcentaje', 5, 2)->nullable()->after('plataforma_delivery_id');
            }
            if (!Schema::hasColumn('mesas', 'comision_iva_porcentaje')) {
                $table->decimal('comision_iva_porcentaje', 5, 2)->nullable()->after('comision_porcentaje');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (Schema::hasColumn('mesas', 'plataforma_delivery_id')) {
                $table->dropForeign(['plataforma_delivery_id']);
                $table->dropColumn('plataforma_delivery_id');
            }
            $columnsToDrop = array_filter(
                ['tipo', 'comision_porcentaje', 'comision_iva_porcentaje'],
                fn($col) => Schema::hasColumn('mesas', $col)
            );
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};