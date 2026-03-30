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
        $duplicates = DB::table('autor_libros')
            ->select('autor_id', 'libro_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('autor_id')
            ->whereNotNull('libro_id')
            ->groupBy('autor_id', 'libro_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('autor_libros')
                ->where('autor_id', $duplicate->autor_id)
                ->where('libro_id', $duplicate->libro_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('autor_libros', function (Blueprint $table) {
            $table->unique(['autor_id', 'libro_id'], 'autor_libros_autor_id_libro_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('autor_libros', function (Blueprint $table) {
            $table->dropUnique('autor_libros_autor_id_libro_id_unique');
        });
    }
};
