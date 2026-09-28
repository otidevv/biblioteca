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
        // Guarda la hora real del prestamo y de la devolucion.
        // Los registros anteriores quedan con 00:00:00 porque esa hora nunca se registro.
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dateTime('fecha_prestamo')->change();
            $table->dateTime('fecha_devolucion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->date('fecha_prestamo')->change();
            $table->date('fecha_devolucion')->nullable()->change();
        });
    }
};
