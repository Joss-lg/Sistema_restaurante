<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Rol;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar la columna rol_id como clave foránea (nullable al principio)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id')->nullable()->after('password');
        });

        // 2. Mapear los valores del enum 'rol' a los IDs de la tabla roles
        $rolMap = [
            'admin'    => Rol::where('slug', 'admin')->first()?->id,
            'capitan'  => Rol::where('slug', 'capitan')->first()?->id,
            'mesero'   => Rol::where('slug', 'mesero')->first()?->id,
            'cocinero' => Rol::where('slug', 'cocinero')->first()?->id,
            'cajero'   => Rol::where('slug', 'cajero')->first()?->id,
        ];

        // 3. Actualizar cada usuario con su rol_id correspondiente
        foreach ($rolMap as $rolName => $rolId) {
            if ($rolId) {
                DB::table('users')
                    ->where('rol', $rolName)
                    ->update(['rol_id' => $rolId]);
            }
        }

        // 4. Eliminar la columna enum 'rol' (ya no se necesita)
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });

        // 5. Hacer que rol_id sea NOT NULL (después de que todos tengan valor)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id')->nullable(false)->change();
            $table->foreign('rol_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropColumn('rol_id');
        });

        // Volver a agregar la columna enum
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'capitan', 'mesero', 'cocinero', 'cajero'])->after('password');
        });
    }
};
