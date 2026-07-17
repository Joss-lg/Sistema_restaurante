<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->cascadeOnDelete();
            $table->string('lote_envio')->nullable();
            $table->string('area');
            $table->text('contenido');
            $table->string('estado')->default('pendiente');
            $table->timestamp('impreso_en')->nullable();
            $table->timestamps();

            $table->index(['area', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};