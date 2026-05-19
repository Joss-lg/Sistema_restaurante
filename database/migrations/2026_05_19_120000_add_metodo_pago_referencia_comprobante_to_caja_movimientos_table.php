<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            if (! Schema::hasColumn('caja_movimientos', 'metodo_pago')) {
                $table->string('metodo_pago')->nullable()->after('tipo');
            }
            if (! Schema::hasColumn('caja_movimientos', 'referencia')) {
                $table->string('referencia')->nullable()->after('metodo_pago');
            }
            if (! Schema::hasColumn('caja_movimientos', 'comprobante')) {
                $table->string('comprobante')->nullable()->after('referencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('caja_movimientos', 'comprobante')) {
                $table->dropColumn('comprobante');
            }
            if (Schema::hasColumn('caja_movimientos', 'referencia')) {
                $table->dropColumn('referencia');
            }
            if (Schema::hasColumn('caja_movimientos', 'metodo_pago')) {
                $table->dropColumn('metodo_pago');
            }
        });
    }
};
