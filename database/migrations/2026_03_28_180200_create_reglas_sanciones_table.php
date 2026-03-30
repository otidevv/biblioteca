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
        Schema::create('reglas_sanciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_sancion_id')->constrained('tipo_sanciones')->onDelete('cascade');
            $table->string('evento'); // prestamo_tardio, devolucion_deterioro, reserva_vencida, manual
            $table->integer('dias_desde')->nullable();
            $table->integer('dias_hasta')->nullable();
            $table->integer('cantidad_minima')->nullable();
            $table->integer('cantidad_maxima')->nullable();
            $table->integer('duracion_dias')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->boolean('requiere_aprobacion')->default(false);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        $tipoPrestamoTardanza = DB::table('tipo_sanciones')->where('codigo', 'PRESTAMO_TARDANZA')->value('id');
        $tipoPrestamoDeterioro = DB::table('tipo_sanciones')->where('codigo', 'PRESTAMO_DETERIORO')->value('id');
        $tipoReservaNoRecogida = DB::table('tipo_sanciones')->where('codigo', 'RESERVA_NO_RECOGIDA')->value('id');

        DB::table('reglas_sanciones')->insert(array_filter([
            $tipoPrestamoTardanza ? [
                'tipo_sancion_id' => $tipoPrestamoTardanza,
                'evento' => 'prestamo_tardio',
                'dias_desde' => 1,
                'dias_hasta' => null,
                'cantidad_minima' => null,
                'cantidad_maxima' => null,
                'duracion_dias' => 3,
                'monto' => null,
                'requiere_aprobacion' => false,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ] : null,
            $tipoPrestamoDeterioro ? [
                'tipo_sancion_id' => $tipoPrestamoDeterioro,
                'evento' => 'devolucion_deterioro',
                'dias_desde' => null,
                'dias_hasta' => null,
                'cantidad_minima' => null,
                'cantidad_maxima' => null,
                'duracion_dias' => 7,
                'monto' => null,
                'requiere_aprobacion' => false,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ] : null,
            $tipoReservaNoRecogida ? [
                'tipo_sancion_id' => $tipoReservaNoRecogida,
                'evento' => 'reserva_no_recogida',
                'dias_desde' => 1,
                'dias_hasta' => null,
                'cantidad_minima' => null,
                'cantidad_maxima' => null,
                'duracion_dias' => 2,
                'monto' => null,
                'requiere_aprobacion' => false,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ] : null,
        ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_sanciones');
    }
};
