<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plataformas_delivery', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#7c3aed');
            $table->decimal('comision_porcentaje', 5, 2)->default(0.00);
            $table->decimal('iva_comision_porcentaje', 5, 2)->default(16.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $plataformas = [
            ['nombre' => 'Rappi', 'slug' => 'rappi', 'color' => '#FF441F', 'comision_porcentaje' => 25.00, 'iva_comision_porcentaje' => 16.00],
            ['nombre' => 'Uber Eats', 'slug' => 'uber', 'color' => '#06C167', 'comision_porcentaje' => 27.00, 'iva_comision_porcentaje' => 16.00],
            ['nombre' => 'DiDi Food', 'slug' => 'didi', 'color' => '#FF7300', 'comision_porcentaje' => 20.00, 'iva_comision_porcentaje' => 16.00],
        ];

        foreach ($plataformas as $plataforma) {
            DB::table('plataformas_delivery')->updateOrInsert(
                ['slug' => $plataforma['slug']],
                array_merge($plataforma, [
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plataformas_delivery');
    }
};