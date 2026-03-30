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
        Schema::create('tipo_sanciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('origen_evento')->nullable(); // prestamo, reservacion, manual
            $table->string('condicion')->nullable(); // tardanza, deterioro, perdida, no_recojo
            $table->integer('dias_duracion')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->boolean('requiere_pago')->default(false);
            $table->boolean('bloquea_prestamos')->default(true);
            $table->boolean('aplica_automaticamente')->default(false);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        DB::table('tipo_sanciones')->insert([
            [
                'codigo' => 'PRESTAMO_TARDANZA',
                'nombre' => 'Prestamo con tardanza',
                'descripcion' => 'Se aplica cuando un prestamo supera la fecha limite.',
                'origen_evento' => 'prestamo',
                'condicion' => 'tardanza',
                'dias_duracion' => 3,
                'requiere_pago' => false,
                'bloquea_prestamos' => true,
                'aplica_automaticamente' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'PRESTAMO_DETERIORO',
                'nombre' => 'Devolucion con deterioro',
                'descripcion' => 'Se aplica cuando el ejemplar se devuelve deteriorado.',
                'origen_evento' => 'prestamo',
                'condicion' => 'deterioro',
                'dias_duracion' => 7,
                'requiere_pago' => false,
                'bloquea_prestamos' => true,
                'aplica_automaticamente' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'RESERVA_NO_RECOGIDA',
                'nombre' => 'Reserva no recogida',
                'descripcion' => 'Se aplica cuando el lector no recoge la reserva dentro del plazo.',
                'origen_evento' => 'reservacion',
                'condicion' => 'no_recojo',
                'dias_duracion' => 2,
                'requiere_pago' => false,
                'bloquea_prestamos' => true,
                'aplica_automaticamente' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_sanciones');
    }
};
