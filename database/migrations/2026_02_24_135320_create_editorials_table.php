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
        Schema::create('editoriales', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento',20)->nullable();
            $table->string('nro_documento',20)->nullable();
            $table->string('nombre');
            $table->string('responsable')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('web')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('pais')->nullable()->constrained("paises");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editoriales');
    }
};
