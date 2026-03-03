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
        Schema::create('libro_materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->nullable()->constrained("libros");
            $table->foreignId('materia_id')->nullable()->constrained("materias");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libro_materias');
    }
};
