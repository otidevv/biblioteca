<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->string('resumen', 220)->nullable()->after('titulo');
            $table->string('lugar', 150)->nullable()->after('referencia');
            $table->time('hora_inicio')->nullable()->after('fecha_fin');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->string('modalidad', 60)->nullable()->after('lugar');
            $table->boolean('destacado')->default(false)->after('modalidad');
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn(['resumen', 'lugar', 'hora_inicio', 'hora_fin', 'modalidad', 'destacado']);
        });
    }
};
