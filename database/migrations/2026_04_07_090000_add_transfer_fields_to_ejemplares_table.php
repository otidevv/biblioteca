<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->foreignId('traslado_origen_biblioteca_id')
                ->nullable()
                ->after('biblioteca_id')
                ->constrained('bibliotecas');

            $table->foreignId('traslado_destino_biblioteca_id')
                ->nullable()
                ->after('traslado_origen_biblioteca_id')
                ->constrained('bibliotecas');

            $table->tinyInteger('estado_traslado')
                ->default(0)
                ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->dropConstrainedForeignId('traslado_destino_biblioteca_id');
            $table->dropConstrainedForeignId('traslado_origen_biblioteca_id');
            $table->dropColumn('estado_traslado');
        });
    }
};
