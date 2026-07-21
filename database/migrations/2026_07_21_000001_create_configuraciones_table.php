<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('valor');
            $table->timestamps();
        });

        // Sembramos los valores por defecto: IVA habilitado al 16%
        DB::table('configuraciones')->insert([
            [
                'clave'      => 'iva_habilitado',
                'valor'      => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'      => 'iva_porcentaje',
                'valor'      => '16',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};