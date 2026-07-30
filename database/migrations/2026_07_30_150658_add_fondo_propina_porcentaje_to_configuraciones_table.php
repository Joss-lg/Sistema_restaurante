<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existe = DB::table('configuraciones')
            ->where('clave', 'fondo_propina_porcentaje')
            ->exists();

        if (!$existe) {
            DB::table('configuraciones')->insert([
                'clave' => 'fondo_propina_porcentaje',
                'valor' => '5.00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuraciones')
            ->where('clave', 'fondo_propina_porcentaje')
            ->delete();
    }
};