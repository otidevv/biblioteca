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
        Schema::create('autor_libros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->nullable()->constrained("autores");
            $table->foreignId('libro_id')->nullable()->constrained("libros");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autor_libros');
    }
};
