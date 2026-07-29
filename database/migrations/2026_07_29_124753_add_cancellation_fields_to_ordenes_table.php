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
        Schema::table('ordenes', function (Blueprint $table) {
            // Verificamos y agregamos 'cancelada_motivo' después de 'estado'
            if (!Schema::hasColumn('ordenes', 'cancelada_motivo')) {
                $table->string('cancelada_motivo', 255)->nullable()->after('estado');
            }

            // Verificamos y agregamos 'cancelada_por'
            if (!Schema::hasColumn('ordenes', 'cancelada_por')) {
                $table->foreignId('cancelada_por')
                      ->nullable()
                      ->after('cancelada_motivo')
                      ->constrained('users')
                      ->nullOnDelete();
            }

            // Verificamos y agregamos 'cancelada_en'
            if (!Schema::hasColumn('ordenes', 'cancelada_en')) {
                $table->timestamp('cancelada_en')->nullable()->default(null)->after('cancelada_por');
            }

            // Verificamos y agregamos 'monto_cancelado'
            if (!Schema::hasColumn('ordenes', 'monto_cancelado')) {
                $table->decimal('monto_cancelado', 10, 2)->nullable()->default(null)->after('cancelada_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            // Eliminamos la llave foránea primero si existe
            if (Schema::hasColumn('ordenes', 'cancelada_por')) {
                $table->dropForeign(['cancelada_por']);
            }

            // Eliminamos las columnas si existen
            $table->dropColumn(array_filter([
                Schema::hasColumn('ordenes', 'cancelada_motivo') ? 'cancelada_motivo' : null,
                Schema::hasColumn('ordenes', 'cancelada_por') ? 'cancelada_por' : null,
                Schema::hasColumn('ordenes', 'cancelada_en') ? 'cancelada_en' : null,
                Schema::hasColumn('ordenes', 'monto_cancelado') ? 'monto_cancelado' : null,
            ]));
        });
    }
};