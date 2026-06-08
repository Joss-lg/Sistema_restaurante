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
        // 1. Agregar la columna rol_id si no existe
        if (!Schema::hasColumn('users', 'rol_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('rol_id')->nullable()->after('password');
            });
        }

        // 2. Mapear y actualizar los valores del enum 'rol' a los IDs de la tabla roles
        if (Schema::hasColumn('users', 'rol')) {
            $rolMap = [
                'admin'    => Rol::where('slug', 'admin')->first()?->id,
                'capitan'  => Rol::where('slug', 'capitan')->first()?->id,
                'mesero'   => Rol::where('slug', 'mesero')->first()?->id,
                'cocinero' => Rol::where('slug', 'cocinero')->first()?->id,
                'cajero'   => Rol::where('slug', 'cajero')->first()?->id,
            ];

            foreach ($rolMap as $rolName => $rolId) {
                if ($rolId) {
                    DB::table('users')
                        ->where('rol', $rolName)
                        ->update(['rol_id' => $rolId]);
                }
            }

            // Asignar un rol por defecto a los usuarios sin rol
            $defaultRolId = Rol::where('slug', 'mesero')->first()?->id ?? Rol::first()?->id;
            if ($defaultRolId) {
                DB::table('users')
                    ->whereNull('rol_id')
                    ->update(['rol_id' => $defaultRolId]);
            }

            // 3. Eliminar la columna enum 'rol'
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('rol');
            });
        } else {
            // Si la columna 'rol' no existe, asignar un rol por defecto a los NULL
            $defaultRolId = Rol::where('slug', 'mesero')->first()?->id ?? Rol::first()?->id;
            if ($defaultRolId) {
                DB::table('users')
                    ->whereNull('rol_id')
                    ->update(['rol_id' => $defaultRolId]);
            }
        }

        if (DB::getDriverName() !== 'sqlite') {
            // 4. Eliminar la clave foránea existente si tiene SET NULL
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME='users' AND COLUMN_NAME='rol_id' AND REFERENCED_TABLE_NAME='roles'");
            if (!empty($foreignKeys)) {
                DB::statement("ALTER TABLE users DROP FOREIGN KEY " . $foreignKeys[0]->CONSTRAINT_NAME);
            }
        }

        // 5. Agregar la clave foránea con CASCADE
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
        });

        if (DB::getDriverName() !== 'sqlite') {
            // 6. Hacer que rol_id sea NOT NULL
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('rol_id')->nullable(false)->change();
            });
        }
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
