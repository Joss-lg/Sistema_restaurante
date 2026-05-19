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
            // Campos para división de cuentas
            $table->boolean('cuenta_dividida')->default(false)->after('metodo_pago');
            $table->integer('numero_cuenta_division')->nullable()->after('cuenta_dividida');
            $table->integer('total_cuentas_division')->nullable()->after('numero_cuenta_division');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn(['cuenta_dividida', 'numero_cuenta_division', 'total_cuentas_division']);
        });
    }
};
