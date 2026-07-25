<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columna heredada de una primera versión de la división "por
     * consumo" (asignaba el renglón COMPLETO a una sola persona). Ya no
     * la usa el código actual —ahora se reparte por unidades en la tabla
     * detalle_orden_divisiones—, pero se deja creada por si algún dato
     * viejo la sigue referenciando. No estorba.
     */
    public function up(): void
    {
        if (Schema::hasColumn('detalles_orden', 'cuenta_division_numero')) {
            return; // ya existe (p. ej. se aplicó antes a mano vía SQL)
        }

        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->unsignedTinyInteger('cuenta_division_numero')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('detalles_orden', 'cuenta_division_numero')) {
            return;
        }

        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->dropColumn('cuenta_division_numero');
        });
    }
};
