<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existe = DB::table('modulos')->where('nombre', 'Delivery')->exists();
        if (!$existe) {
            DB::table('modulos')->insert([
                'nombre' => 'Delivery'
            ]);
        }
    }

    public function down(): void
    {
        DB::table('modulos')->where('nombre', 'Delivery')->delete();
    }
};