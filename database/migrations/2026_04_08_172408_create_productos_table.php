<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Importante para usar DB::raw si fuera necesario

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
            $table->text('descripcion')->nullable(); 
            $table->decimal('precio', 10, 2);
            $table->boolean('esta_disponible')->default(true);
            $table->binary('imagen')->nullable()->comment('Los bytes binarios de la imagen');
            $table->string('imagen_mime_type', 100)->nullable()->comment('Ej. image/jpeg, image/png');
            // ---------------------------------------------------------

            $table->softDeletes();
            $table->timestamps();
        });

        // Aseguramos que la columna sea LONGBLOB específicamente en MySQL
        DB::statement('ALTER TABLE productos MODIFY imagen LONGBLOB NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};