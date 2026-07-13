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
        Schema::table('productos', function (Blueprint $table) {
            // Marca si el producto se vende por peso (en vez de precio fijo por unidad)
            $table->boolean('se_vende_por_peso')->default(false)->after('precio');

            // Precio de referencia por cada 100g. Nullable porque solo aplica
            // cuando se_vende_por_peso = true. El precio final se calcula como
            // (precio_por_100g / 100) * gramos elegidos en el modal de Gramaje.
            $table->decimal('precio_por_100g', 10, 2)->nullable()->after('se_vende_por_peso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['se_vende_por_peso', 'precio_por_100g']);
        });
    }
};