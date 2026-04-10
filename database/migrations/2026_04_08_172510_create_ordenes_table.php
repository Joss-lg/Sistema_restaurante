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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden')->unique();
            $table->foreignId('mesa_id')->constrained('mesas');
            $table->foreignId('mesero_id')->constrained('users');
            $table->foreignId('capitan_id')->nullable()->constrained('users');
            $table->string('estado'); // Ej: pendiente, en proceso, servida, pagada
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('abierta_el')->nullable();
            $table->timestamp('cerrada_el')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
