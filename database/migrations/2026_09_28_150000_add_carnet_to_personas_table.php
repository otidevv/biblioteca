<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Carnet de biblioteca: ORIGINAL o DUPLICADO (el original se perdio y solo vale el duplicado).
        Schema::table('personas', function (Blueprint $table) {
            $table->string('tipo_carnet', 20)->nullable()->after('estado_academico');
            $table->date('fecha_carnet_duplicado')->nullable()->after('tipo_carnet');
        });

        // Hoy todos los lectores registrados tienen su carnet original.
        DB::table('personas')
            ->whereIn('id', DB::table('users')
                ->join('usuario_rol_bibliotecas', 'usuario_rol_bibliotecas.user_id', '=', 'users.id')
                ->where('usuario_rol_bibliotecas.rol_id', 5)
                ->whereNotNull('users.persona_id')
                ->select('users.persona_id'))
            ->update(['tipo_carnet' => 'ORIGINAL']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn(['tipo_carnet', 'fecha_carnet_duplicado']);
        });
    }
};
