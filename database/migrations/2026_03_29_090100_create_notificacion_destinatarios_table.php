<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notificacion_id')->constrained('notificaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('leido')->default(false);
            $table->timestamp('fecha_lectura')->nullable();
            $table->boolean('archivado')->default(false);
            $table->boolean('enviado_email')->default(false);
            $table->timestamp('fecha_envio_email')->nullable();
            $table->timestamps();

            $table->unique(['notificacion_id', 'user_id'], 'notificacion_destinatario_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_destinatarios');
    }
};
