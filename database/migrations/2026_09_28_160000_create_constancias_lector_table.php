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
        // Constancias de lector frecuente: guardan una copia de los datos certificados
        // para que cualquiera pueda verificarlas con su codigo.
        Schema::create('constancias_lector', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nivel', 20);
            $table->json('datos');
            $table->timestamp('emitida_at');
            $table->timestamp('vence_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancias_lector');
    }
};
