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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lector_id')->constrained('users')->onDelete('cascade');
            $table->string('prestamo_lugar')->nullable();
            $table->integer('duracion');
            $table->date('fecha_prestamo');
            $table->date('fecha_limite');
            $table->date('fecha_devolucion')->nullable();
            $table->text('observaciones_prestamo')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->tinyInteger('estado_prestamo')->default(0);//0 PRESTADO,1 DEVUELTO,2 TARDANZA, 3 DETERIORO
            $table->tinyInteger('estado')->default(1);//1 INCIIADO, 2 FINALIZADO
            $table->foreignId('ejemplar_id')->constrained('ejemplares')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // bibliotecario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
