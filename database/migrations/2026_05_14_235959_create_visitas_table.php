<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100);
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('fecha');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'fecha']);
            $table->index('fecha');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
