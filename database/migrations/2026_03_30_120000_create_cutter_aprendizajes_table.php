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
        Schema::create('cutter_aprendizajes', function (Blueprint $table) {
            $table->id();
            $table->string('clave_autor', 120);
            $table->string('codigo_cutter', 20);
            $table->integer('peso')->default(1);
            $table->timestamps();

            $table->unique(['clave_autor', 'codigo_cutter']);
            $table->index('clave_autor');
            $table->index('codigo_cutter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutter_aprendizajes');
    }
};
