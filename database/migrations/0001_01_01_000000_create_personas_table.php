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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('dni', 15)->nullable()->unique();
            $table->string('codigo_institucional')->nullable()->unique(); // código alumno/docente

            // Nombres
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();

            // Datos personales
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F', 'O'])->nullable();

            // Contacto
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email_personal')->nullable();

            // Académico / institucional
            $table->string('tipo_persona')->nullable();// ['ESTUDIANTE', 'DOCENTE', 'ADMINISTRATIVO', 'EXTERNO'];
            $table->foreignId('carrera_id')
                ->nullable()
                ->constrained('carreras')
                ->nullOnDelete();

            $table->text('estado_academico')->nullable();// ['ESTUDIANTE','EGRESADO','SUSPENDIDO','RETIRADO']         ])

            // Control
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
