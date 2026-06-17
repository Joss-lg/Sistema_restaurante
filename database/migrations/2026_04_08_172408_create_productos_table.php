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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->string('nombre');
            $table->text('descripcion')->nullable(); // <-- ¡FALTABA ESTA LÍNEA!
            $table->decimal('precio', 10, 2);
            $table->integer('tiempo_preparacion')->default(15)->comment('en minutos'); // <-- Tip: Agrégale un default aquí también por seguridad
            $table->boolean('esta_disponible')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
