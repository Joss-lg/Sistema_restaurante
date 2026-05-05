<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (! Schema::hasColumn('mesas', 'mesero_id')) {
                $table->foreignId('mesero_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (Schema::hasColumn('mesas', 'mesero_id')) {
                $table->dropForeign(['mesero_id']);
                $table->dropColumn('mesero_id');
            }
        });
    }
};
