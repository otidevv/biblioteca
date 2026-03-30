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
        Schema::table('sanciones', function (Blueprint $table) {
            $table->foreignId('prestamo_id')->nullable()->after('user_id')->constrained('prestamos')->nullOnDelete();
            $table->foreignId('reservacion_id')->nullable()->after('prestamo_id')->constrained('reservaciones')->nullOnDelete();
            $table->unsignedBigInteger('tipo_sancion_id')->nullable()->after('reservacion_id');
            $table->string('tipo')->nullable()->after('tipo_sancion_id');
            $table->string('codigo_pago')->nullable()->after('tipo');
            $table->integer('duracion')->nullable()->after('fecha_fin');
            $table->text('observaciones')->nullable()->after('duracion');
            $table->text('detalles_termino')->nullable()->after('observaciones');
            $table->foreignId('bibliotecario_id')->nullable()->after('detalles_termino')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bibliotecario_id');
            $table->dropColumn('detalles_termino');
            $table->dropColumn('observaciones');
            $table->dropColumn('duracion');
            $table->dropColumn('codigo_pago');
            $table->dropColumn('tipo');
            $table->dropColumn('tipo_sancion_id');
            $table->dropConstrainedForeignId('reservacion_id');
            $table->dropConstrainedForeignId('prestamo_id');
        });
    }
};
