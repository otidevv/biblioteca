<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sanciones', function (Blueprint $table) {
            $table->foreignId('biblioteca_id')->nullable()->after('reservacion_id')->constrained('bibliotecas')->nullOnDelete();
        });

        // La sancion pertenece a la biblioteca del ejemplar que la origino.
        DB::statement('
            UPDATE sanciones s
            INNER JOIN prestamos p ON p.id = s.prestamo_id
            INNER JOIN ejemplares e ON e.id = p.ejemplar_id
            SET s.biblioteca_id = e.biblioteca_id
            WHERE s.biblioteca_id IS NULL
        ');

        DB::statement('
            UPDATE sanciones s
            INNER JOIN reservaciones r ON r.id = s.reservacion_id
            INNER JOIN ejemplares e ON e.id = r.ejemplar_id
            SET s.biblioteca_id = e.biblioteca_id
            WHERE s.biblioteca_id IS NULL
        ');

        // Sanciones manuales: se asignan a la biblioteca del bibliotecario que las registro,
        // solo cuando tiene una unica biblioteca asignada.
        DB::statement('
            UPDATE sanciones s
            INNER JOIN (
                SELECT user_id, MIN(biblioteca_id) AS biblioteca_id
                FROM usuario_rol_bibliotecas
                WHERE biblioteca_id IS NOT NULL
                GROUP BY user_id
                HAVING COUNT(DISTINCT biblioteca_id) = 1
            ) urb ON urb.user_id = s.bibliotecario_id
            SET s.biblioteca_id = urb.biblioteca_id
            WHERE s.biblioteca_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('biblioteca_id');
        });
    }
};
