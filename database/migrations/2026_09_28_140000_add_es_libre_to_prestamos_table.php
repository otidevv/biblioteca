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
        // Prestamo libre: estudiante identificado por DNI (API institucional), sin registro previo como lector.
        Schema::table('prestamos', function (Blueprint $table) {
            $table->boolean('es_libre')->default(false)->after('prestamo_lugar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('es_libre');
        });
    }
};
