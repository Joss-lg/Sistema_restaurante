<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cuántas UNIDADES de un producto (detalle_orden) le tocan a cada
     * persona cuando la mesa se divide "por consumo". Permite partir la
     * cantidad de un mismo renglón entre varias personas (ej: de 3
     * pizzas, 2 para la Persona 1 y 1 para la Persona 2).
     *
     * Sin llaves foráneas, mismo criterio que cuentas_division.
     */
    public function up(): void
    {
        if (Schema::hasTable('detalle_orden_divisiones')) {
            return; // ya existe (p. ej. se aplicó antes a mano vía SQL)
        }

        Schema::create('detalle_orden_divisiones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('detalle_orden_id');
            $table->index('detalle_orden_id');

            $table->unsignedTinyInteger('numero_cuenta'); // a qué persona
            $table->unsignedInteger('cantidad');           // cuántas unidades

            $table->timestamps();

            $table->unique(['detalle_orden_id', 'numero_cuenta'], 'detalle_persona_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_orden_divisiones');
    }
};
