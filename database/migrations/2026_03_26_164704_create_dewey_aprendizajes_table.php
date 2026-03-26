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
        Schema::create('dewey_aprendizajes', function (Blueprint $table) {
             $table->id();

            $table->string('palabra', 100);
            $table->string('codigo_dewey', 10);
            $table->integer('peso')->default(1);
            $table->timestamps();
            // Evita duplicados (clave única compuesta)
            $table->unique(['palabra', 'codigo_dewey']);
            // Índices para rendimiento
            $table->index('codigo_dewey');
            $table->index('palabra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dewey_aprendizajes');
    }
};
